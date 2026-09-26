<?php

namespace App\Actions\AdminAcademic;

use App\Enums\TipoBeneficio;
use App\Models\Estudiante\Alumnos;
use App\Models\Academico\BeneficioEstudiante;
use App\Models\Academico\CicloLectivo;
use App\Models\Users\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class AssignStudentBenefitAction
{
    /**
     * Crea un beneficio/arancel para un alumno en un ciclo lectivo.
     *
     * @param  array{
     *     id_ciclo_lectivo: int,
     *     tipo_beneficio: string,
     *     nombre_convenio: string,
     *     porcentaje_estudiante?: float|null,
     *     porcentaje_universidad?: float|null,
     *     monto_fijo_cuota?: float|null,
     *     resolucion_academica?: string|null,
     * } $data
     */
    public function execute(Alumnos $alumno, array $data, User $asignadoPor): BeneficioEstudiante
    {
        $tipo = TipoBeneficio::from($data['tipo_beneficio']);

        // 1. Reglas de negocio previas a la BD
        [$pctEstudiante, $pctUniversidad, $montoFijo] = $this->resolverPorcentajes($tipo, $data);

        $this->validarSumaCobertura($pctEstudiante, $pctUniversidad);

        // 2. Verificar ciclo lectivo existente
        $ciclo = CicloLectivo::find($data['id_ciclo_lectivo']);
        if (!$ciclo) {
            throw new RuntimeException('El ciclo lectivo seleccionado no existe.');
        }

        // 3. Verificar que no exista otro beneficio activo para el mismo alumno + ciclo
        //    (la BD también lo bloquea con uq_alumno_ciclo_beneficio, pero así el error es claro)
        $existe = BeneficioEstudiante::where('id_alumno', $alumno->alumno_id)
            ->where('id_ciclo_lectivo', $data['id_ciclo_lectivo'])
            ->exists();

        if ($existe) {
            throw new RuntimeException(
                'El estudiante ya tiene un beneficio registrado para este ciclo lectivo.'
            );
        }

        // 4. Crear
        return DB::transaction(function () use (
            $alumno, $data, $asignadoPor, $tipo, $pctEstudiante, $pctUniversidad, $montoFijo
        ) {
            return BeneficioEstudiante::create([
                'id_alumno'              => $alumno->alumno_id,
                'id_ciclo_lectivo'       => $data['id_ciclo_lectivo'],
                'tipo_beneficio'         => $tipo->value,
                'nombre_convenio'        => $data['nombre_convenio'],
                'porcentaje_estudiante'  => $pctEstudiante,
                'porcentaje_universidad' => $pctUniversidad,
                'monto_fijo_cuota'       => $montoFijo,
                'resolucion_academica'   => $data['resolucion_academica'] ?? null,
                'activo'                 => true,
                'asignado_por'           => $asignadoPor->id,
            ]);
        });
    }

    /**
     * @return array{0: float, 1: float, 2: float|null}
     */
    private function resolverPorcentajes(TipoBeneficio $tipo, array $data): array
    {
        // BECA_COMPLETA fuerza 0 / 100
        if ($tipo === TipoBeneficio::BECA_COMPLETA) {
            return [0.00, 100.00, null];
        }

        // BECA_PARCIAL y CUOTA_ESPECIAL requieren porcentajes explícitos
        if ($tipo === TipoBeneficio::BECA_PARCIAL || $tipo === TipoBeneficio::CUOTA_ESPECIAL) {
            $pctEst  = (float) ($data['porcentaje_estudiante']  ?? $tipo->porcentajeEstudianteDefault());
            $pctUniv = (float) ($data['porcentaje_universidad'] ?? (100.00 - $pctEst));

            return [$pctEst, $pctUniv, null];
        }

        // FRANJA_BECARIA: monto fijo institucional, el % universidad se calcula después
        if ($tipo === TipoBeneficio::FRANJA_BECARIA) {
            $monto = $data['monto_fijo_cuota'] ?? null;
            if ($monto === null || $monto < 0) {
                throw new RuntimeException(
                    'La Franja Becaria requiere un monto fijo de cuota válido.'
                );
            }

            // El estudiante paga el 100% del arancel diferenciado, la UMA absorbe 0% sobre el monto base.
            return [100.00, 0.00, (float) $monto];
        }

        throw new RuntimeException('Tipo de beneficio no soportado.');
    }

    private function validarSumaCobertura(float $pctEst, float $pctUniv): void
    {
        $suma = round($pctEst + $pctUniv, 2);

        if (abs($suma - 100.00) > 0.01) {
            throw new RuntimeException(
                "La suma de coberturas debe ser exactamente 100.00%. Recibido: {$suma}%."
            );
        }
    }
}