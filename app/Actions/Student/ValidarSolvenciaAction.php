<?php

namespace App\Actions\Student;

use App\Models\Academico\CicloLectivo;
use App\Models\Estudiante\Alumnos;
use App\Models\Estudiante\Inscripcion;
use Illuminate\Support\Facades\DB;

class ValidarSolvenciaAction
{
    /**
     * Códigos de concepto (según tabla concepto_pago).
     * Centralizados para evitar typos y facilitar cambios futuros.
     */
    private const CONCEPTO_MATRICULA       = 'MAT-001'; // Matrícula de pregrado
    private const CONCEPTO_CUOTA_MENSUAL   = 'MAT-002'; // Cuota mensual pregrado
    private const CONCEPTO_CUOTA_BECARIA   = 'MAT-003'; // Cuota mensual franja becaria

    /**
     * Estados de cargo considerados como "pendiente" (deuda activa).
     */
    private const ESTADOS_PENDIENTES = ['PENDIENTE', 'VENCIDO'];

    private const MESES_NOMBRE = [
        1  => 'ENERO',
        2  => 'FEBRERO',
        3  => 'MARZO',
        4  => 'ABRIL',
        5  => 'MAYO',
        6  => 'JUNIO',
        7  => 'JULIO',
        8  => 'AGOSTO',
        9  => 'SEPTIEMBRE',
        10 => 'OCTUBRE',
        11 => 'NOVIEMBRE',
        12 => 'DICIEMBRE',
    ];

    public function execute(Alumnos $alumno, int $cicloLectivoId): array
    {
        $motivos = [];

        $esNuevoIngreso = $this->esNuevoIngreso($alumno, $cicloLectivoId);

        $cicloActual = CicloLectivo::find($cicloLectivoId);
        if (!$cicloActual) {
            return [
                'solvente' => false,
                'motivos'  => ['No se encontró el ciclo lectivo actual.'],
                'es_nuevo_ingreso' => $esNuevoIngreso,
            ];
        }

        // 1. Matrícula del ciclo actual
        if (!$this->matriculaPagada($alumno, $cicloLectivoId)) {
            $motivos[] = 'No has pagado la matrícula del ciclo actual.';
        }

        // 2. Primera cuota del ciclo actual (mes de inicio, NO "mes 1" calendario)
        $mesInicio = $this->mesInicioDelCiclo($cicloActual);
        if (!$this->cuotaMesPagada($alumno, $cicloLectivoId, $mesInicio)) {
            $motivos[] = "No has cancelado la primera cuota del ciclo actual (mes de {$this->nombreMes($mesInicio)}).";
        }

        // 3. Si es viejo ingreso: validar cuotas pendientes del ciclo anterior
        if (!$esNuevoIngreso) {
            $cicloAnterior = $this->obtenerCicloAnterior($cicloActual);
            if ($cicloAnterior) {
                $pendientes = $this->cuotasPendientes($alumno, $cicloAnterior->ciclo_lectivo_id);
                if ($pendientes > 0) {
                    $motivos[] = "Tienes {$pendientes} cuota(s) pendiente(s) del ciclo anterior ({$cicloAnterior->nombre_ciclo}).";
                }
            }
        }

        return [
            'solvente'         => empty($motivos),
            'motivos'          => $motivos,
            'es_nuevo_ingreso' => $esNuevoIngreso,
        ];
    }


    /* ==========================================================
     * HELPERS
     * ========================================================== */

    /**
     * Determina si el alumno es de nuevo ingreso respecto al ciclo evaluado.
     * Considera "nuevo ingreso" si no tiene ninguna inscripción registrada en ciclos anteriores.
     */
    protected function esNuevoIngreso(Alumnos $alumno, int $cicloLectivoId): bool
    {
        return !Inscripcion::where('id_alumno', $alumno->alumno_id)
            ->where('id_ciclo_lectivo', '!=', $cicloLectivoId)
            ->exists();
    }

    /**
     * Verifica si la matrícula del ciclo actual está pagada.
     */
    protected function matriculaPagada(Alumnos $alumno, int $cicloLectivoId): bool
    {
        return DB::table('cargo_estudiante as ce')
            ->join('concepto_pago as cp', 'ce.id_concepto', '=', 'cp.concepto_pago_id')
            ->where('ce.id_alumno', $alumno->alumno_id)
            ->where('ce.id_ciclo_lectivo', $cicloLectivoId)
            ->where('cp.codigo_concepto', self::CONCEPTO_MATRICULA)
            ->whereIn('ce.estado_cargo', ['PAGADO', 'EXONERADO'])
            ->exists();
    }

    /**
     * Verifica si una cuota mensual específica del ciclo actual está pagada.
     * Acepta tanto cuota normal (MAT-002) como becaria (MAT-003).
     */
    protected function cuotaMesPagada(Alumnos $alumno, int $cicloLectivoId, int $mes): bool
    {
        $variantes = $this->variantesMes($mes);

        $query = DB::table('cargo_estudiante as ce')
            ->join('concepto_pago as cp', 'ce.id_concepto', '=', 'cp.concepto_pago_id')
            ->where('ce.id_alumno', $alumno->alumno_id)
            ->where('ce.id_ciclo_lectivo', $cicloLectivoId)
            ->whereIn('cp.codigo_concepto', [
                self::CONCEPTO_CUOTA_MENSUAL,
                self::CONCEPTO_CUOTA_BECARIA,
            ])
            ->whereIn('ce.estado_cargo', ['PAGADO', 'EXONERADO']);

        // Si NO hay mes_arancel registrado (NULL), aceptamos el cargo como pagado
        // (útil si en tu BD algunas cuotas no tienen mes por un tema histórico).
        $query->where(function ($q) use ($variantes) {
            $q->whereNull('ce.mes_arancel')
              ->orWhere(function ($q2) use ($variantes) {
                  foreach ($variantes as $v) {
                      $q2->orWhereRaw('UPPER(TRIM(ce.mes_arancel)) = ?', [$v]);
                  }
              });
        });

        return $query->exists();
    }

    /**
     * Devuelve las variantes en mayúsculas que puede tener el mes en `mes_arancel`.
     */
    private function variantesMes(int $mes): array
    {
        $nombre = self::MESES_NOMBRE[$mes] ?? '';

        return array_unique(array_filter([
            (string) $mes,
            str_pad((string) $mes, 2, '0', STR_PAD_LEFT),
            $nombre,
            // Por si guardan "ENERO 2026" o "ENERO-2026"
        ]));
    }

    protected function nombreMes(int $mes): string
    {
        return self::MESES_NOMBRE[$mes] ?? (string) $mes;
    }

    /**
     * Determina el número de mes en el que inicia el ciclo lectivo.
     * Ej: si el ciclo empieza en 2026-07-02, devuelve 7 (julio).
     */
    protected function mesInicioDelCiclo(CicloLectivo $ciclo): int
    {
        return (int) $ciclo->fecha_inicio->format('n');
    }

    protected function obtenerCicloAnterior(CicloLectivo $cicloActual): ?CicloLectivo
    {
        return CicloLectivo::where('fecha_fin', '<', $cicloActual->fecha_inicio)
            ->orderByDesc('fecha_fin')
            ->first();
    }

    protected function cuotasPendientes(Alumnos $alumno, int $cicloLectivoId): int
    {
        return DB::table('cargo_estudiante as ce')
            ->join('concepto_pago as cp', 'ce.id_concepto', '=', 'cp.concepto_pago_id')
            ->where('ce.id_alumno', $alumno->alumno_id)
            ->where('ce.id_ciclo_lectivo', $cicloLectivoId)
            ->whereIn('cp.codigo_concepto', [
                self::CONCEPTO_CUOTA_MENSUAL,
                self::CONCEPTO_CUOTA_BECARIA,
            ])
            ->whereIn('ce.estado_cargo', self::ESTADOS_PENDIENTES)
            ->count();
    }
}