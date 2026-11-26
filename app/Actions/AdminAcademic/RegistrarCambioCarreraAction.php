<?php

namespace App\Actions\AdminAcademic;

use App\Enums\EstadoCiclo;
use App\Enums\EstadoEstudiante;
use App\Enums\EstadoPensum;
use App\Enums\EstadoProcesoGraduacion;
use App\Models\Academico\CargoEstudiante;
use App\Models\Academico\CicloLectivo;
use App\Models\Academico\Pensum;
use App\Models\Academico\ProcesoGraduacion;
use App\Models\Adfinanciero\ConceptoPago;
use App\Models\Adfinanciero\DetallePago;
use App\Models\Estudiante\Alumnos;
use App\Models\Estudiante\Carreras;
use App\Models\Estudiante\DetalleInscripcion;
use App\Rules\CodigoEstudiante;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class RegistrarCambioCarreraAction
{
    /**
     * Registra un cambio de carrera creando un NUEVO expediente en `alumnos`.
     * El expediente anterior queda con estado CAMBIO_VOLUNTARIO o CAMBIO_REPROBATORIO.
     *
     * @return array{success: bool, message: string, nuevo_alumno: Alumnos}
     */
    public function execute(
        Alumnos $alumno,
        int $nuevaCarreraId,
        string $motivo, // 'VOLUNTARIO' | 'FORZOSO_REPROBACION'
        ?int $materiaReprobadaId = null,
        bool $esGraduado = false
    ): array {
        // 1. Validar estado del ciclo
        $this->validarCicloLectivo();

        // 2. Determinar si es graduado real
        $esGraduado = $this->esGraduado($alumno);

        // 3. Validar solvencia
        $this->validarSolvencia($alumno, $esGraduado);

        // 4. Validar arancel (solo si NO es graduado)
        if (!$esGraduado) {
            $this->validarArancelCambioCarrera($alumno);
        }

        // 5. Validar que no tenga materias en curso (solo si NO es graduado)
        if (!$esGraduado) {
            $this->validarSinInscripcionesActivas($alumno);
        }

        // 6. Validar materia reprobada si es FORZOSO
        if ($motivo === 'FORZOSO_REPROBACION') {
            if (!$materiaReprobadaId) {
                throw new RuntimeException('Debe indicar la materia reprobada en 3ra matrícula.');
            }
            $this->validarMateriaNoEnNuevaCarrera($nuevaCarreraId, $materiaReprobadaId);
        }

        // 7. Validar que la nueva carrera sea distinta
        if ((int) $alumno->id_carrera === $nuevaCarreraId) {
            throw new RuntimeException('El estudiante ya pertenece a esa carrera.');
        }

        // 8. Validar que el expediente origen NO esté ya en cambio
        if (in_array($alumno->estado_carrera, [
            EstadoEstudiante::CAMBIO_VOLUNTARIO->value,
            EstadoEstudiante::CAMBIO_REPROBATORIO->value,
            EstadoEstudiante::INACTIVO->value,
        ], true)) {
            throw new RuntimeException('Este expediente ya no puede generar nuevos cambios de carrera.');
        }

        // 8.5. Validar que el estudiante NO haya cursado antes la nueva carrera
        //      (evita que "vuelva" a una carrera que ya tiene expediente)
        $this->validarCarreraNoCursadaPreviamente($alumno, $nuevaCarreraId);

        // 9. Ejecutar
        return DB::transaction(function () use ($alumno, $nuevaCarreraId, $motivo, $esGraduado) {
            $user = $alumno->usuario;

            // 9.1. Cambiar el estado del expediente ORIGEN
            if (!$esGraduado) {
                $nuevoEstadoOrigen = $motivo === 'FORZOSO_REPROBACION'
                    ? EstadoEstudiante::CAMBIO_REPROBATORIO
                    : EstadoEstudiante::CAMBIO_VOLUNTARIO;

                $alumno->update(['estado_carrera' => $nuevoEstadoOrigen->value]);
            }

            // 9.2. Generar código y correo para el NUEVO expediente
            $cantidadExpedientes = Alumnos::where('id_usuario', $user->id)->count();
            $intento = $cantidadExpedientes; // primer sufijo: 1, 2, 3...
            $codigo = null;

            for ($i = $intento; $i < $intento + 10; $i++) {
                $codigoCandidato = CodigoEstudiante::generar($user->name, $user->id, $i);
                if (!Alumnos::where('codigo_estudiante', $codigoCandidato)->exists()) {
                    $codigo = $codigoCandidato;
                    break;
                }
            }

            if (!$codigo) {
                throw new RuntimeException('No se pudo generar un código único para el nuevo expediente.');
            }

            $correoInstitucional = strtolower($codigo) . '@uma.edu.sv';

            // 9.3. Crear el NUEVO expediente
            $nuevoAlumno = Alumnos::create([
                'id_usuario'           => $user->id,
                'id_alumno_origen'     => $alumno->alumno_id,
                'id_carrera'           => $nuevaCarreraId,
                'codigo_estudiante'    => $codigo,
                'correo_institucional' => $correoInstitucional,
                'estado_carrera'       => EstadoEstudiante::ACTIVO->value,
            ]);

            // 9.4. NO se crea fila en alumno_documentos:
            //      el nuevo expediente hereda los del origen vía `documentos_heredados`.

            return [
                'success'      => true,
                'message'      => $esGraduado
                    ? 'Cambio de carrera registrado (egresado exento de arancel). Nuevo expediente creado.'
                    : 'Cambio de carrera registrado. Nuevo expediente creado.',
                'nuevo_alumno' => $nuevoAlumno,
            ];
        });
    }


    public function esGraduado(Alumnos $alumno): bool
    {
        return ProcesoGraduacion::where('id_alumno', $alumno->alumno_id)
            ->where('estado_proceso', EstadoProcesoGraduacion::GRADUADO->value)
            ->exists();
    }

    private function validarCicloLectivo(): void
    {
        if (CicloLectivo::where('estado', EstadoCiclo::CONFIGURACION)->exists()) {
            throw new RuntimeException('No se puede procesar un cambio mientras un ciclo está en CONFIGURACION.');
        }
    }

    private function validarSolvencia(Alumnos $alumno, bool $esGraduado): void
    {
        $cicloActual = CicloLectivo::whereIn('estado', [
            EstadoCiclo::MATRICULA_ABIERTA,
            EstadoCiclo::ACTIVO,
            EstadoCiclo::CERRADO,
        ])->orderByDesc('fecha_inicio')->first();

        if (!$cicloActual) {
            throw new RuntimeException('No hay ciclo vigente para validar solvencia.');
        }

        $queryCargos = CargoEstudiante::where('id_alumno', $alumno->alumno_id);
        if (!$esGraduado) {
            $queryCargos->where('id_ciclo_lectivo', $cicloActual->ciclo_lectivo_id);
        }

        $cargos = $queryCargos->get();
        if ($cargos->isEmpty()) return;

        $totalDeuda  = $cargos->sum('monto_neto_estudiante');
        $totalPagado = DetallePago::whereIn('id_cargo', $cargos->pluck('cargo_id'))->sum('subtotal');
        $saldo       = max(0, round($totalDeuda - $totalPagado, 2));

        if ($saldo > 0.009) {
            throw new RuntimeException("El estudiante no está solvente. Saldo: $" . number_format($saldo, 2));
        }
    }

    private function validarArancelCambioCarrera(Alumnos $alumno): void
    {
        $concepto = ConceptoPago::where('codigo_concepto', 'TRA-005')->first();
        if (!$concepto) throw new RuntimeException('No existe el concepto TRA-005.');

        $cicloActual = CicloLectivo::whereIn('estado', [EstadoCiclo::MATRICULA_ABIERTA, EstadoCiclo::ACTIVO])
            ->orderByDesc('fecha_inicio')->first();

        $queryCargo = CargoEstudiante::where('id_alumno', $alumno->alumno_id)
            ->where('id_concepto', $concepto->concepto_pago_id);
        if ($cicloActual) $queryCargo->where('id_ciclo_lectivo', $cicloActual->ciclo_lectivo_id);

        $cargoArancel = $queryCargo->first();

        $montoEsperado = $cargoArancel
            ? (float) $cargoArancel->monto_neto_estudiante
            : (float) $concepto->monto_base;

        $pagado = 0.0;
        if ($cargoArancel) {
            $pagado += (float) DetallePago::where('id_cargo', $cargoArancel->cargo_id)->sum('subtotal');
        }
        $pagado += (float) DetallePago::where('id_concepto', $concepto->concepto_pago_id)
            ->whereNull('id_cargo')
            ->whereHas('pago', fn ($q) => $q->where('id_alumno', $alumno->alumno_id))
            ->sum('subtotal');

        if (round($pagado, 2) < round($montoEsperado, 2)) {
            throw new RuntimeException(
                'El arancel TRA-005 no está cancelado. Pagado: $' . number_format($pagado, 2)
                . ' · Requerido: $' . number_format($montoEsperado, 2)
            );
        }
    }

    private function validarSinInscripcionesActivas(Alumnos $alumno): void
    {
        $ciclosVigentes = CicloLectivo::whereIn('estado', [EstadoCiclo::ACTIVO, EstadoCiclo::MATRICULA_ABIERTA])
            ->pluck('ciclo_lectivo_id');

        if ($ciclosVigentes->isEmpty()) return;

        $materiasEnCurso = DetalleInscripcion::query()
            ->whereHas('inscripcion', function ($q) use ($alumno, $ciclosVigentes) {
                $q->where('id_alumno', $alumno->alumno_id)
                  ->whereIn('id_ciclo_lectivo', $ciclosVigentes);
            })
            ->whereIn('estado_materia', ['CURSANDO'])
            ->with('seccion.materia')
            ->get();

        if ($materiasEnCurso->isEmpty()) return;

        $nombres = $materiasEnCurso->map(fn ($d) => $d->seccion?->materia?->nombre)->filter()->unique()->values();
        throw new RuntimeException(
            'El estudiante tiene materias en curso. Debe darse de baja antes de cambiar. Materias: '
            . $nombres->take(5)->implode(', ')
        );
    }

    private function validarMateriaNoEnNuevaCarrera(int $carreraId, int $materiaId): void
    {
        $pensum = Pensum::where('id_carrera', $carreraId)
            ->where('estado', EstadoPensum::ACTIVO)
            ->first();

        if (!$pensum) throw new RuntimeException('La nueva carrera no tiene pensum activo.');

        if ($pensum->pensumMaterias()->where('id_materia', $materiaId)->exists()) {
            throw new RuntimeException(
                'La nueva carrera contiene la materia reprobada. No es posible el cambio.'
            );
        }
    }

    public static function carrerasPermitidas(Alumnos $alumno, string $motivo, ?int $materiaReprobadaId = null)
    {
        // Carreras que el estudiante ya tiene como expediente (cualquier estado)
        $carrerasYaCursadas = Alumnos::where('id_usuario', $alumno->id_usuario)
            ->where('alumno_id', '!=', $alumno->alumno_id)
            ->pluck('id_carrera')
            ->unique()
            ->toArray();

        $query = Carreras::query()
            ->where('carrera_id', '!=', $alumno->id_carrera)
            ->whereNotIn('carrera_id', $carrerasYaCursadas);

        if ($motivo === 'FORZOSO_REPROBACION' && $materiaReprobadaId) {
            $query->whereHas('pensums', function ($q) use ($materiaReprobadaId) {
                $q->where('estado', EstadoPensum::ACTIVO)
                ->whereDoesntHave('pensumMaterias', fn ($sub) => $sub->where('id_materia', $materiaReprobadaId));
            });
        } else {
            $query->whereHas('pensums', fn ($q) => $q->where('estado', EstadoPensum::ACTIVO));
        }

        return $query->orderBy('nombre')->get();
    }

    /**
     * Valida que el estudiante no tenga un expediente previo en la nueva carrera.
     * Si ya cursó (o está cursando) esa carrera, no se permite el cambio.
     */
    private function validarCarreraNoCursadaPreviamente(Alumnos $alumno, int $nuevaCarreraId): void
    {
        $yaCursada = Alumnos::where('id_usuario', $alumno->id_usuario)
            ->where('id_carrera', $nuevaCarreraId)
            ->where('alumno_id', '!=', $alumno->alumno_id) // excluir el actual
            ->exists();

        if ($yaCursada) {
            throw new RuntimeException(
                'El estudiante ya tiene un expediente en la carrera seleccionada. '
                . 'No es posible volver a cursarla. Seleccione una carrera diferente.'
            );
        }
    }
}