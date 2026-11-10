<?php

namespace App\Actions\Student;

use App\Models\Academico\CicloLectivo;
use App\Models\Estudiante\Alumnos;
use App\Models\Estudiante\DetalleInscripcion;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ValidarPuedeRetirarMateriaAction
{
    private const CONCEPTO_RETIRO = 'TRA-011';

    public function execute(Alumnos $alumno, DetalleInscripcion $detalle): array
    {
        $bloqueadores = [];

        // ─────────────────────────────────────────────
        // 1. Estado actual
        // ─────────────────────────────────────────────
        if (strtoupper($detalle->estado_materia) !== 'CURSANDO') {
            $bloqueadores[] = 'Solo puedes retirar materias en estado CURSANDO.';
        }

        // ─────────────────────────────────────────────
        // 2. Ciclo lectivo
        // ─────────────────────────────────────────────
        $inscripcion = $detalle->inscripcion;
        if (!$inscripcion) {
            return [
                'puede'        => false,
                'bloqueadores' => ['No se encontró la inscripción asociada.'],
            ];
        }

        $ciclo = CicloLectivo::find($inscripcion->id_ciclo_lectivo);
        if (!$ciclo) {
            return [
                'puede'        => false,
                'bloqueadores' => ['No se encontró el ciclo lectivo.'],
            ];
        }

        // ─────────────────────────────────────────────
        // 3. Ventana de retiro: 2 meses desde inicio
        // ─────────────────────────────────────────────
        $fechaInicio   = Carbon::parse($ciclo->fecha_inicio)->startOfDay();
        $fechaLimite   = $fechaInicio->copy()->addMonths(2);
        $hoy           = now();

        if ($hoy->lt($fechaInicio)) {
            $bloqueadores[] = 'El ciclo lectivo aún no ha iniciado.';
        } elseif ($hoy->gt($fechaLimite)) {
            $bloqueadores[] = "El plazo para retirar materias expiró el {$fechaLimite->format('d/m/Y')}.";
        }

        // ─────────────────────────────────────────────
        // 4. No puede retirar la misma materia 2 veces
        // ─────────────────────────────────────────────
        $yaRetirada = DB::table('detalle_inscripcion as di')
            ->join('inscripciones as i', 'di.id_inscripcion', '=', 'i.inscripcion_id')
            ->join('seccion as s', 'di.id_seccion', '=', 's.seccion_id')
            ->where('i.id_alumno', $alumno->alumno_id)
            ->where('i.id_ciclo_lectivo', $inscripcion->id_ciclo_lectivo)
            ->where('s.id_materia', $detalle->seccion->id_materia)
            ->where('di.estado_materia', 'RETIRADA')
            ->exists();

        if ($yaRetirada) {
            $bloqueadores[] = 'Ya retiraste esta materia en el ciclo actual.';
        }

        // ─────────────────────────────────────────────
        // 5. Solvencia financiera hasta el mes actual
        // ─────────────────────────────────────────────
        $mesActual = (int) $hoy->format('n');
        $mesesCiclo = $this->mesesTranscurridos($fechaInicio, $hoy);
        $cuotasPagadas = $this->cuotasPagadasHasta($alumno, $ciclo->ciclo_lectivo_id);

        if ($cuotasPagadas < $mesesCiclo) {
            $bloqueadores[] = "Debes estar solvente financieramente. Has pagado {$cuotasPagadas} de {$mesesCiclo} cuota(s) transcurridas.";
        }

        // ─────────────────────────────────────────────
        // 6. Arancel TRA-011 pagado (1 por retiro)
        // ─────────────────────────────────────────────
        $retirosPagados   = $this->contarPagosRetiro($alumno, $ciclo);
        $retirosRealizados = $this->contarRetirosRealizados($alumno, $ciclo->ciclo_lectivo_id);
        $retirosDisponibles = max(0, $retirosPagados - $retirosRealizados);

        if ($retirosDisponibles <= 0) {
            $bloqueadores[] = 'No tienes un pago vigente del arancel RETIRO DE MATERIA (TRA-011) para este ciclo lectivo.';
        }

        return [
            'puede'                => empty($bloqueadores),
            'bloqueadores'         => $bloqueadores,
            'ciclo'                => $ciclo,
            'fecha_limite'         => $fechaLimite,
            'retiros_pagados'      => $retirosPagados,
            'retiros_realizados'   => $retirosRealizados,
            'retiros_disponibles'  => $retirosDisponibles,
        ];
    }

    /* ────────────────────────────────
     *  HELPERS
     * ──────────────────────────────── */

    /**
     * Cuenta los meses completos transcurridos desde el inicio del ciclo hasta hoy.
     * Ej: inicio 02/07, hoy 15/09 → 2 meses transcurridos (julio y agosto) + fracción de septiembre.
     * Tomamos la diferencia en meses, redondeando hacia arriba si ya empezó el mes.
     */
    private function mesesTranscurridos(Carbon $inicio, Carbon $hoy): int
    {
        return $inicio->diffInMonths($hoy->copy()->startOfMonth()) + 1;
    }

    /**
     * Cuenta las cuotas mensuales (MAT-002, MAT-003) pagadas del ciclo.
     */
    private function cuotasPagadasHasta(Alumnos $alumno, int $cicloLectivoId): int
    {
        return DB::table('cargo_estudiante as ce')
            ->join('concepto_pago as cp', 'ce.id_concepto', '=', 'cp.concepto_pago_id')
            ->where('ce.id_alumno', $alumno->alumno_id)
            ->where('ce.id_ciclo_lectivo', $cicloLectivoId)
            ->whereIn('cp.codigo_concepto', ['MAT-002', 'MAT-003'])
            ->where('ce.estado_cargo', 'PAGADO')
            ->count();
    }

    /**
     * Cuenta los pagos del arancel TRA-011 cuya fecha de pago caiga dentro del ciclo.
     */
    private function contarPagosRetiro(Alumnos $alumno, CicloLectivo $ciclo): int
    {
        return DB::table('pago as p')
            ->join('detalle_pago as dp', 'p.pago_id', '=', 'dp.id_pago')
            ->join('concepto_pago as cp', 'dp.id_concepto', '=', 'cp.concepto_pago_id')
            ->where('p.id_alumno', $alumno->alumno_id)
            ->where('cp.codigo_concepto', self::CONCEPTO_RETIRO)
            ->whereBetween('p.fecha_pago', [
                Carbon::parse($ciclo->fecha_inicio)->startOfDay(),
                Carbon::parse($ciclo->fecha_fin)->endOfDay(),
            ])
            ->count();
    }

    /**
     * Cuenta los retiros ya realizados en el ciclo.
     */
    private function contarRetirosRealizados(Alumnos $alumno, int $cicloLectivoId): int
    {
        return DB::table('detalle_inscripcion as di')
            ->join('inscripciones as i', 'di.id_inscripcion', '=', 'i.inscripcion_id')
            ->where('i.id_alumno', $alumno->alumno_id)
            ->where('i.id_ciclo_lectivo', $cicloLectivoId)
            ->where('di.estado_materia', 'RETIRADA')
            ->count();
    }
}