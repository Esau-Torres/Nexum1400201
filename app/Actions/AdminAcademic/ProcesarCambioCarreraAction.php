<?php

namespace App\Actions\AdminAcademic;

use App\Enums\EstadoCiclo;
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
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ProcesarCambioCarreraAction
{
    /**
     * @param Alumnos $alumno
     * @param int     $nuevaCarreraId
     * @param string  $motivo 'VOLUNTARIO' | 'FORZOSO_REPROBACION'
     * @param int|null $materiaReprobadaId Requerido si motivo = FORZOSO_REPROBACION
     * @param bool    $esGraduado
     * @return array{success: bool, message: string}
     */
    public function execute(
        Alumnos $alumno,
        int $nuevaCarreraId,
        string $motivo,
        ?int $materiaReprobadaId = null,
        bool $esGraduado = false
    ): array {
        // 0. Validar estado del ciclo lectivo
        $this->validarCicloLectivo();

        // 1. Determinar si es egresado/graduado por proceso_graduacion
        //    (sobrescribe el flag que venga del controlador para seguridad)
        $esGraduado = $this->esGraduado($alumno);

        // 2. Validar solvencia financiera
        $this->validarSolvencia($alumno, $esGraduado);

        // 3. Validar arancel de cambio de carrera (solo si NO es graduado)
        if (!$esGraduado) {
            $this->validarArancelCambioCarrera($alumno);
        }

        // 4. Validar que no tenga materias en curso (solo si NO es graduado)
        if (!$esGraduado) {
            $this->validarSinInscripcionesActivas($alumno);
        }

        // 5. Validar restricción de materia reprobada (solo si es FORZOSO)
        if ($motivo === 'FORZOSO_REPROBACION') {
            if (!$materiaReprobadaId) {
                throw new RuntimeException('Debe indicar la materia reprobada en 3ra matrícula.');
            }
            $this->validarMateriaNoEnNuevaCarrera($nuevaCarreraId, $materiaReprobadaId);
        }

        // 6. Validar que la nueva carrera sea distinta
        if ((int) $alumno->id_carrera === $nuevaCarreraId) {
            throw new RuntimeException('El estudiante ya pertenece a esa carrera.');
        }

        // 7. Ejecutar el cambio
        return DB::transaction(function () use ($alumno, $nuevaCarreraId, $motivo, $materiaReprobadaId, $esGraduado) {
            $carreraAnterior = $alumno->id_carrera;

            $alumno->update([
                'id_carrera'     => $nuevaCarreraId,
                'estado_carrera' => 'ACTIVO', // vuelve a quedar activo en la nueva carrera
            ]);

            // Registrar historial (opcional, si tienes la tabla)
            // DB::table('historial_cambios_carrera')->insert([
            //     'id_alumno'            => $alumno->alumno_id,
            //     'id_carrera_anterior'  => $carreraAnterior,
            //     'id_carrera_nueva'     => $nuevaCarreraId,
            //     'motivo'               => $motivo,
            //     'id_materia_reprobada' => $materiaReprobadaId,
            //     'es_graduado'          => $esGraduado,
            //     'fecha_cambio'         => now(),
            // ]);

            return [
                'success' => true,
                'message' => $esGraduado
                    ? 'Cambio de carrera procesado correctamente (egresado, exento de arancel).'
                    : 'Cambio de carrera procesado correctamente.',
            ];
        });
    }

    /**
     * Determina si el alumno es egresado/graduado según proceso_graduacion.
     */
    public function esGraduado(Alumnos $alumno): bool
    {
        return ProcesoGraduacion::where('id_alumno', $alumno->alumno_id)
            ->where(function ($q) {
                $q->where('estado_proceso', EstadoProcesoGraduacion::GRADUADO->value)
                  ->orWhere('estado_proceso', 'GRADUADO');
            })
            ->exists();
    }

    /**
     * Bloquea solo en CONFIGURACION. Permite MATRICULA_ABIERTA, ACTIVO y CERRADO.
     */
    private function validarCicloLectivo(): void
    {
        $cicloConfig = CicloLectivo::where('estado', EstadoCiclo::CONFIGURACION)->exists();
        if ($cicloConfig) {
            throw new RuntimeException(
                'No se puede procesar un cambio de carrera mientras un ciclo está en CONFIGURACION.'
            );
        }
    }

    /**
     * Solvencia: la deuda del ciclo (o total si es graduado) debe estar 100% pagada.
     */
    private function validarSolvencia(Alumnos $alumno, bool $esGraduado): void
    {
        $cicloActual = CicloLectivo::whereIn('estado', [
            EstadoCiclo::MATRICULA_ABIERTA,
            EstadoCiclo::ACTIVO,
            EstadoCiclo::CERRADO,
        ])->orderByDesc('fecha_inicio')->first();

        if (!$cicloActual) {
            throw new RuntimeException('No hay un ciclo lectivo vigente para validar solvencia.');
        }

        $queryCargos = CargoEstudiante::where('id_alumno', $alumno->alumno_id);

        if (!$esGraduado) {
            $queryCargos->where('id_ciclo_lectivo', $cicloActual->ciclo_lectivo_id);
        }

        $cargos = $queryCargos->get();

        if ($cargos->isEmpty()) {
            return; // Sin cargos = solvente
        }

        $totalDeuda = $cargos->sum('monto_neto_estudiante');

        $totalPagado = DetallePago::whereIn('id_cargo', $cargos->pluck('cargo_id'))
            ->sum('subtotal');

        // Saldo acotado a 0 (nunca negativo)
        $saldo = max(0, round($totalDeuda - $totalPagado, 2));

        if ($saldo > 0.009) {
            throw new RuntimeException(
                "El estudiante no está solvente. Saldo pendiente: $" . number_format($saldo, 2)
            );
        }
    }

    /**
     * Valida que el arancel TRA-005 esté pagado.
     */
    private function validarArancelCambioCarrera(Alumnos $alumno): void
    {
        $concepto = ConceptoPago::where('codigo_concepto', 'TRA-005')->first();

        if (!$concepto) {
            throw new RuntimeException('No existe el concepto de pago TRA-005 (Cambio de Carrera).');
        }

        $cicloActual = CicloLectivo::whereIn('estado', [
            EstadoCiclo::MATRICULA_ABIERTA,
            EstadoCiclo::ACTIVO,
        ])->orderByDesc('fecha_inicio')->first();

        // ---- Vía A: buscar cargo generado del arancel ----
        $queryCargo = CargoEstudiante::where('id_alumno', $alumno->alumno_id)
            ->where('id_concepto', $concepto->concepto_pago_id);

        if ($cicloActual) {
            $queryCargo->where('id_ciclo_lectivo', $cicloActual->ciclo_lectivo_id);
        }

        $cargoArancel = $queryCargo->first();

        $montoEsperado = $cargoArancel
            ? (float) $cargoArancel->monto_neto_estudiante
            : (float) $concepto->monto_base; // si no hay cargo, se usa el monto base del concepto

        $pagado = 0.0;

        // Vía A: pago aplicado al cargo
        if ($cargoArancel) {
            $pagado += (float) DetallePago::where('id_cargo', $cargoArancel->cargo_id)
                ->sum('subtotal');
        }

        // ---- Vía B: pago directo por concepto (id_cargo NULL) ----
        // Sumamos los detalles de pago del alumno con este concepto, ya sea con o sin cargo.
        $pagosDirectos = DetallePago::query()
            ->where('id_concepto', $concepto->concepto_pago_id)
            ->whereHas('pago', function ($q) use ($alumno) {
                $q->where('id_alumno', $alumno->alumno_id);
            })
            ->whereNull('id_cargo') // solo los que no están atados a un cargo
            ->sum('subtotal');

        $pagado += (float) $pagosDirectos;

        // Comparación con margen decimal
        if (round($pagado, 2) < round($montoEsperado, 2)) {
            throw new RuntimeException(
                'El arancel de Cambio de Carrera (TRA-005) no está cancelado en su totalidad. '
                . 'Pagado: $' . number_format($pagado, 2)
                . ' · Requerido: $' . number_format($montoEsperado, 2)
            );
        }
    }

    /**
     * La nueva carrera no debe tener la materia reprobada en su pensum activo.
     */
    private function validarMateriaNoEnNuevaCarrera(int $carreraId, int $materiaId): void
    {
        $pensumActivo = Pensum::where('id_carrera', $carreraId)
            ->where('estado', EstadoPensum::ACTIVO)
            ->first();

        if (!$pensumActivo) {
            throw new RuntimeException('La nueva carrera no tiene un pensum activo.');
        }

        $existe = $pensumActivo->pensumMaterias()
            ->where('id_materia', $materiaId)
            ->exists();

        if ($existe) {
            throw new RuntimeException(
                'La nueva carrera contiene la materia reprobada. No es posible el cambio (límite institucional de 3 intentos).'
            );
        }
    }

    /**
     * Bloquea el cambio si el alumno tiene materias en curso en el ciclo ACTIVO.
     * (Solo aplica a estudiantes no graduados.)
     */
    private function validarSinInscripcionesActivas(Alumnos $alumno): void
    {
        // 1. Buscar ciclos vigentes (ACTIVO o MATRICULA_ABIERTA)
        $ciclosVigentes = CicloLectivo::whereIn('estado', [
            EstadoCiclo::ACTIVO,
            EstadoCiclo::MATRICULA_ABIERTA,
        ])->pluck('ciclo_lectivo_id');

        if ($ciclosVigentes->isEmpty()) {
            return; // Sin ciclo vigente → no hay nada que validar
        }

        // 2. Estados que consideramos "materia en curso"
        $estadosEnCurso = ['CURSANDO', 'APROBADA', 'RETIRADA', 'REPROBADA'];

        // 3. Buscar detalles de inscripción del alumno en esos ciclos
        $materiasEnCurso = DetalleInscripcion::query()
            ->whereHas('inscripcion', function ($q) use ($alumno, $ciclosVigentes) {
                $q->where('id_alumno', $alumno->alumno_id)
                  ->whereIn('id_ciclo_lectivo', $ciclosVigentes);
            })
            ->whereIn('estado_materia', $estadosEnCurso)
            ->with('seccion.materia')
            ->get();

        if ($materiasEnCurso->isEmpty()) {
            return; // Sin materias en curso → puede cambiar
        }

        // 4. Armar mensaje con las materias que impiden el cambio
        $nombres = $materiasEnCurso
            ->map(fn ($d) => $d->seccion?->materia?->nombre
                ? ($d->seccion->materia->codigo_materia ?? '') . ' — ' . $d->seccion->materia->nombre
                : 'Materia #' . $d->id_seccion)
            ->filter()
            ->unique()
            ->values();

        $detalle = $nombres->take(5)->implode(', ');
        $extra   = $nombres->count() > 5 ? ' y ' . ($nombres->count() - 5) . ' más' : '';

        throw new RuntimeException(
            'El estudiante tiene materias en curso en el ciclo vigente. '
            . 'Debe darse de baja antes de cambiar de carrera. Materias: '
            . $detalle . $extra
        );
    }

    /**
     * Helper público: devuelve carreras válidas para un alumno según motivo.
     */
    public static function carrerasPermitidas(Alumnos $alumno, string $motivo, ?int $materiaReprobadaId = null)
    {
        $query = Carreras::query()->where('carrera_id', '!=', $alumno->id_carrera);

        if ($motivo === 'FORZOSO_REPROBACION' && $materiaReprobadaId) {
            $query->whereHas('pensums', function ($q) use ($materiaReprobadaId) {
                $q->where('estado', EstadoPensum::ACTIVO)
                  ->whereDoesntHave('pensumMaterias', function ($sub) use ($materiaReprobadaId) {
                      $sub->where('id_materia', $materiaReprobadaId);
                  });
            });
        } else {
            $query->whereHas('pensums', fn ($q) => $q->where('estado', EstadoPensum::ACTIVO));
        }

        return $query->orderBy('nombre')->get();
    }
}
