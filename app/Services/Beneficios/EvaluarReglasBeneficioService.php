<?php

namespace App\Services\Beneficios;

use App\Models\Academico\ReglaBeneficio;
use App\Models\Estudiante\Alumnos;
use App\Models\Academico\BeneficioEstudiante;

class EvaluarReglasBeneficioService
{
    /**
     * Evalúa de forma administrativa si un beneficio está vigente.
     * NO calcula CUM, UV ni reprobaciones (módulos no desarrollados aún).
     *
     * @return array{
     *     cumple: bool,
     *     motivos: array<string>,
     *     regla: ReglaBeneficio|null,
     * }
     */
    public function execute(Alumnos $alumno, ?BeneficioEstudiante $beneficio = null): array
    {
        $beneficio = $beneficio ?? $alumno->beneficioCicloActual;

        if (!$beneficio) {
            return [
                'cumple'  => true,
                'motivos' => ['El alumno no tiene beneficio activo.'],
                'regla'   => null,
            ];
        }

        $regla = ReglaBeneficio::query()
            ->where('tipo_beneficio', $beneficio->tipo_beneficio)
            ->where('activo', true)
            ->where('vigente_desde', '<=', now())
            ->orderByDesc('vigente_desde')
            ->first();

        if (!$regla) {
            return [
                'cumple'  => true,
                'motivos' => ['No hay regla configurada para este tipo de beneficio.'],
                'regla'   => null,
            ];
        }

        $motivos = [];

        // Solo evaluación administrativa: solvencia financiera simple.
        // Los filtros de CUM/UV/reprobaciones quedan pendientes hasta que exista el módulo de calificaciones.
        if ($regla->requiere_solvencia_financiera && !$this->estaSolvente($alumno)) {
            $motivos[] = 'No cuenta con solvencia financiera.';
        }

        return [
            'cumple'  => count($motivos) === 0,
            'motivos' => $motivos,
            'regla'   => $regla,
        ];
    }

    private function estaSolvente(Alumnos $alumno): bool
    {
        // Simplificado: sin cargos vencidos pendientes.
        return !\DB::table('cargo_estudiante')
            ->where('id_alumno', $alumno->alumno_id)
            ->whereIn('estado_cargo', ['PENDIENTE', 'VENCIDO'])
            ->where('fecha_vencimiento', '<', now())
            ->exists();
    }
}