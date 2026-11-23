<?php

namespace App\Actions\AdminAcademic;

use App\Models\Academico\CicloLectivo;
use App\Models\Estudiante\Alumnos;
use App\Models\Estudiante\RequisitosEgreso;
use App\Enums\ConceptosPagoPorCarrera;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MarcarSolvenciaFinancieraAction
{
    private const CONCEPTO_MATRICULA     = 'MAT-001';
    private const CONCEPTO_CUOTA_MENSUAL = 'MAT-002';
    private const CONCEPTO_CUOTA_BECARIA = 'MAT-003';

    public function execute(Alumnos $alumno): array
    {
        // Conceptos correctos según el nivel académico
        $conceptos = ConceptosPagoPorCarrera::para($alumno->id_carrera);

        $ciclos = DB::table('inscripciones as i')
            ->join('ciclo_lectivo as cl', 'i.id_ciclo_lectivo', '=', 'cl.ciclo_lectivo_id')
            ->where('i.id_alumno', $alumno->alumno_id)
            ->select(
                'cl.ciclo_lectivo_id',
                'cl.nombre_ciclo',
                'cl.fecha_inicio',
                'cl.fecha_fin',
                'cl.estado'
            )
            ->orderBy('cl.fecha_inicio')
            ->get();

        if ($ciclos->isEmpty()) {
            throw ValidationException::withMessages([
                'solvencia' => 'El alumno no tiene ciclos lectivos registrados.',
            ]);
        }

        $hoy     = now();
        $adeudos = [];
        $detalles = [];

        foreach ($ciclos as $ciclo) {
            $fechaInicio  = Carbon::parse($ciclo->fecha_inicio)->startOfDay();
            $fechaFin     = Carbon::parse($ciclo->fecha_fin)->endOfDay();
            $cicloCerrado = $ciclo->estado === 'CERRADO';
            $fechaLimite  = $cicloCerrado ? $fechaFin : $hoy->copy()->min($fechaFin);

            if ($fechaInicio->gt($hoy)) continue;

            if (!$cicloCerrado) {
                $tieneMateriasEnCurso = DB::table('detalle_inscripcion as di')
                    ->join('inscripciones as i', 'di.id_inscripcion', '=', 'i.inscripcion_id')
                    ->where('i.id_alumno', $alumno->alumno_id)
                    ->where('i.id_ciclo_lectivo', $ciclo->ciclo_lectivo_id)
                    ->where('i.estado_inscripcion', 'ACTIVA')
                    ->where('di.estado_materia', 'CURSANDO')
                    ->exists();

                if (!$tieneMateriasEnCurso) {
                    // Alumno ya terminó sus materias: no se exige matrícula ni cuotas
                    // Solo verificamos que lo que sí tiene esté pagado
                    $detalles[] = [
                        'ciclo'              => $ciclo->nombre_ciclo,
                        'estado_ciclo'       => $ciclo->estado,
                        'meses_esperados'    => 0,
                        'cuotas_pagadas'     => 0,
                        'matricula_cubierta' => true,
                        'adeudo'             => false,
                        'motivos'            => [],
                        'nota'               => 'El alumno no tiene materias en curso en este ciclo.',
                    ];
                    continue;
                }
            }

            $mesesEsperados = $this->mesesTranscurridos($fechaInicio, $fechaLimite);

            // 1. Matrícula (concepto según carrera)
            $matricula = DB::table('cargo_estudiante as ce')
                ->join('concepto_pago as cp', 'ce.id_concepto', '=', 'cp.concepto_pago_id')
                ->where('ce.id_alumno', $alumno->alumno_id)
                ->where('ce.id_ciclo_lectivo', $ciclo->ciclo_lectivo_id)
                ->where('cp.codigo_concepto', $conceptos['codigo_matricula'])
                ->select('ce.estado_cargo', 'ce.monto_neto_estudiante')
                ->first();

            $matriculaCubierta = $this->cargoCubierto($matricula);

            // 2. Cuotas mensuales (conceptos según carrera)
            $cuotas = DB::table('cargo_estudiante as ce')
                ->join('concepto_pago as cp', 'ce.id_concepto', '=', 'cp.concepto_pago_id')
                ->where('ce.id_alumno', $alumno->alumno_id)
                ->where('ce.id_ciclo_lectivo', $ciclo->ciclo_lectivo_id)
                ->whereIn('cp.codigo_concepto', $conceptos['codigos_cuota'])
                ->select('ce.estado_cargo', 'ce.monto_neto_estudiante', 'ce.mes_arancel')
                ->get();

            $cuotasPagadas = $cuotas->filter(fn ($c) => $this->cargoCubierto($c))->count();

            $detalle = [
                'ciclo'              => $ciclo->nombre_ciclo,
                'estado_ciclo'       => $ciclo->estado,
                'meses_esperados'    => $mesesEsperados,
                'cuotas_pagadas'     => $cuotasPagadas,
                'matricula_cubierta' => $matriculaCubierta,
                'adeudo'             => false,
                'motivos'            => [],
            ];

            if (!$matriculaCubierta) {
                $detalle['adeudo']    = true;
                $detalle['motivos'][] = "Matrícula pendiente en {$ciclo->nombre_ciclo}.";
            }

            if ($cuotasPagadas < $mesesEsperados) {
                $faltantes = $mesesEsperados - $cuotasPagadas;
                $detalle['adeudo']    = true;
                $detalle['motivos'][] = "Faltan {$faltantes} cuota(s) en {$ciclo->nombre_ciclo} ({$cuotasPagadas}/{$mesesEsperados}).";
            }

            if ($detalle['adeudo']) $adeudos[] = $detalle;

            $detalles[] = $detalle;
        }

        if (!empty($adeudos)) {
            $mensajes = [];
            foreach ($adeudos as $a) {
                $mensajes[] = "Ciclo {$a['ciclo']}: " . implode(' ', $a['motivos']);
            }
            throw ValidationException::withMessages([
                'solvencia' => 'El alumno no está solvente: ' . implode(' ', $mensajes),
            ]);
        }

        $req = RequisitosEgreso::paraAlumno($alumno->alumno_id);
        $req->solvencia_financiera = true;
        $req->save();

        return [
            'req'      => $req->fresh(),
            'detalles' => $detalles,
        ];
    }
    /* ────────────────────────────────
     * HELPERS
     * ──────────────────────────────── */

    private function mesesTranscurridos(Carbon $inicio, Carbon $limite): int
    {
        $diff = $inicio->diffInMonths($limite->copy()->startOfMonth());
        return max(1, $diff + 1);
    }

    private function tienePagoConcepto(int $alumnoId, int $cicloLectivoId, array $codigos): bool
    {
        return DB::table('pago as p')
            ->join('detalle_pago as dp', 'p.pago_id', '=', 'dp.id_pago')
            ->join('concepto_pago as cp', 'dp.id_concepto', '=', 'cp.concepto_pago_id')
            ->join('cargo_estudiante as ce', 'dp.id_cargo', '=', 'ce.cargo_id')
            ->where('p.id_alumno', $alumnoId)
            ->where('ce.id_ciclo_lectivo', $cicloLectivoId)
            ->whereIn('cp.codigo_concepto', $codigos)
            ->where('ce.estado_cargo', 'PAGADO')
            ->exists();
    }

    private function contarPagosCuotas(int $alumnoId, int $cicloLectivoId): int
    {
        return DB::table('cargo_estudiante as ce')
            ->join('concepto_pago as cp', 'ce.id_concepto', '=', 'cp.concepto_pago_id')
            ->where('ce.id_alumno', $alumnoId)
            ->where('ce.id_ciclo_lectivo', $cicloLectivoId)
            ->whereIn('cp.codigo_concepto', [
                self::CONCEPTO_CUOTA_MENSUAL,
                self::CONCEPTO_CUOTA_BECARIA,
            ])
            ->where('ce.estado_cargo', 'PAGADO')
            ->count();
    }

    private function cargoCubierto(?object $cargo): bool
    {
        if (!$cargo) return false;

        if ((float) $cargo->monto_neto_estudiante <= 0.00) return true;

        $estado = strtoupper((string) $cargo->estado_cargo);
        return in_array($estado, ['PAGADO', 'EXONERADO'], true);
    }
}