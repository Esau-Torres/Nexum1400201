<?php

namespace App\Console\Commands;

use App\Enums\EstadoCiclo;
use App\Models\Academico\CicloLectivo;
use App\Services\SuperAdmin\CalculadoraEstadoMateriaService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CerrarCicloLectivoCommand extends Command
{
    protected $signature = 'ciclo:cerrar
                            {ciclo_lectivo_id : ID del ciclo a cerrar}
                            {--dry-run : No persiste cambios, solo muestra el resultado}';

    protected $description = 'Cierra un ciclo lectivo y calcula el estado final (APROBADA/REPROBADA) de cada materia inscrita.';

    public function __construct(
        private CalculadoraEstadoMateriaService $calculadora,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $cicloId = (int) $this->argument('ciclo_lectivo_id');
        $dryRun = (bool) $this->option('dry-run');

        $ciclo = CicloLectivo::find($cicloId);
        if (!$ciclo) {
            $this->error("Ciclo {$cicloId} no encontrado.");
            return self::FAILURE;
        }

        if ($ciclo->estado !== EstadoCiclo::ACTIVO && $ciclo->estado !== EstadoCiclo::MATRICULA_ABIERTA) {
            $this->error("El ciclo {$ciclo->nombre_ciclo} no está en un estado válido para cierre (actual: {$ciclo->estado->label()}).");
            return self::FAILURE;
        }

        // Alumnos con inscripción ACTIVA en el ciclo
        $alumnosInscritos = DB::table('inscripciones as i')
            ->join('alumnos as a', 'i.id_alumno', '=', 'a.alumno_id')
            ->where('i.id_ciclo_lectivo', $cicloId)
            ->where('i.estado_inscripcion', 'ACTIVA')
            ->select('a.alumno_id', 'a.id_carrera')
            ->get();

        $this->info("Procesando {$alumnosInscritos->count()} alumnos en {$ciclo->nombre_ciclo}...");

        $procesados = 0;
        $aprobadas = 0;
        $reprobadas = 0;
        $sinNota = 0;
        $bloqueados = 0;

        foreach ($alumnosInscritos as $alumno) {
            // Detalle de cada materia inscrita en el ciclo
            $detalles = DB::table('detalle_inscripcion as di')
                ->join('inscripciones as i', 'di.id_inscripcion', '=', 'i.inscripcion_id')
                ->join('seccion as s', 'di.id_seccion', '=', 's.seccion_id')
                ->where('i.id_alumno', $alumno->alumno_id)
                ->where('i.id_ciclo_lectivo', $cicloId)
                ->where('i.estado_inscripcion', 'ACTIVA')
                ->select(
                    'di.detalle_inscripcion_id',
                    'di.promedio_final',
                    'di.estado_materia',
                    's.id_materia'
                )
                ->get();

            foreach ($detalles as $detalle) {
                // Solo procesar los que están CURSANDO (ya procesados no se tocan)
                if (strtoupper($detalle->estado_materia) !== 'CURSANDO') {
                    continue;
                }

                if ($detalle->promedio_final === null) {
                    $sinNota++;
                    $this->warn("  · Alumno {$alumno->alumno_id} · Detalle {$detalle->detalle_inscripcion_id}: sin promedio final.");
                    continue;
                }

                $promedio = (float) $detalle->promedio_final;
                $aprobo = $this->calculadora->aprobo($promedio, $alumno->id_carrera);
                $nuevoEstado = $aprobo ? 'APROBADA' : 'REPROBADA';

                // Verificar si debe bloquear por reprobaciones (si es REPROBADA)
                if (!$aprobo) {
                    $reprobaciones = $this->contarReprobacionesPrevias(
                        $alumno->alumno_id,
                        $detalle->id_materia,
                        $detalle->detalle_inscripcion_id
                    );
                    if ($this->calculadora->agotoOportunidades($reprobaciones + 1, $alumno->id_carrera)) {
                        $this->warn("  · Alumno {$alumno->alumno_id} · Materia {$detalle->id_materia}: agotó oportunidades. Requiere cambio de carrera.");
                        $bloqueados++;
                    }
                }

                if (!$dryRun) {
                    DB::table('detalle_inscripcion')
                        ->where('detalle_inscripcion_id', $detalle->detalle_inscripcion_id)
                        ->update(['estado_materia' => $nuevoEstado]);

                    Log::info('Cierre de ciclo · Materia actualizada', [
                        'detalle_inscripcion_id' => $detalle->detalle_inscripcion_id,
                        'alumno_id' => $alumno->alumno_id,
                        'materia_id' => $detalle->id_materia,
                        'promedio' => $promedio,
                        'estado_anterior' => 'CURSANDO',
                        'estado_nuevo' => $nuevoEstado,
                        'ciclo_lectivo_id' => $cicloId,
                    ]);
                }

                $procesados++;
                $aprobo ? $aprobadas++ : $reprobadas++;
            }
        }

        // Marcar inscripciones como COMPLETADA (opcional)
        if (!$dryRun) {
            DB::table('inscripciones')
                ->where('id_ciclo_lectivo', $cicloId)
                ->where('estado_inscripcion', 'ACTIVA')
                ->update(['estado_inscripcion' => 'COMPLETADA']);

            // Cambiar estado del ciclo a CERRADO
            $ciclo->estado = EstadoCiclo::CERRADO;
            $ciclo->save();
        }

        $this->newLine();
        $this->info('══════════════════════════════════════════');
        $this->info('  RESUMEN DE CIERRE DE CICLO');
        $this->info('══════════════════════════════════════════');
        $this->line("  Ciclo:            {$ciclo->nombre_ciclo}");
        $this->line("  Alumnos:          {$alumnosInscritos->count()}");
        $this->line("  Materias procesadas: {$procesados}");
        $this->line("  Aprobadas:        {$aprobadas}");
        $this->line("  Reprobadas:       {$reprobadas}");
        $this->line("  Sin promedio:     {$sinNota}");
        $this->line("  Alumnos bloqueados (3 reprobaciones): {$bloqueados}");
        $this->line("  Modo:             " . ($dryRun ? 'DRY-RUN' : 'PERSISTIDO'));
        $this->info('══════════════════════════════════════════');

        if ($bloqueados > 0) {
            $this->warn("⚠ Hay {$bloqueados} materia(s) que requieren cambio de carrera.");
            $this->warn("  Revisa el log o la tabla para más detalle.");
        }

        return self::SUCCESS;
    }

    /**
     * Cuenta reprobaciones previas (antes del cierre actual).
     */
    private function contarReprobacionesPrevias(int $alumnoId, int $materiaId, int $excluirDetalleId): int
    {
        return DB::table('detalle_inscripcion as di')
            ->join('inscripciones as i', 'di.id_inscripcion', '=', 'i.inscripcion_id')
            ->join('seccion as s', 'di.id_seccion', '=', 's.seccion_id')
            ->where('i.id_alumno', $alumnoId)
            ->where('s.id_materia', $materiaId)
            ->where('di.estado_materia', 'REPROBADA')
            ->where('di.detalle_inscripcion_id', '!=', $excluirDetalleId)
            ->count();
    }
}