<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ClasesSesionSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('📅 Generando sesiones de clase basadas en horarios...');

        // Feriados nacionales 2026 a excluir (puedes ajustar las fechas)
        $feriados = [
            '2026-06-17', // Día del Padre
            '2026-08-06', // Fiestas de San Salvador
            '2026-09-15', // Independencia
            '2026-11-01', // Día de los Difuntos
            '2026-11-02', // Día de los Difuntos
            '2026-11-07', // Fiestas de San Salvador
            '2026-11-08', // Fiestas de San Salvador
            '2026-11-09', // Fiestas de San Salvador
        ];

        // Obtener todas las secciones activas
        $secciones = DB::table('seccion')
            ->where('estado', 'ACTIVA')
            ->get();

        $totalCreadas = 0;

        foreach ($secciones as $seccion) {
            // Obtener los horarios de esta sección con sus días
            $horariosSeccion = DB::table('seccion_horario')
                ->join('horarios', 'seccion_horario.id_horario', '=', 'horarios.horario_id')
                ->where('seccion_horario.id_seccion', $seccion->seccion_id)
                ->select(
                    'seccion_horario.seccion_horario_id',
                    'horarios.horario_id',
                    'horarios.dia',
                    'horarios.hora_inicio',
                    'horarios.hora_fin',
                    'seccion_horario.id_aula'
                )
                ->get();

            if ($horariosSeccion->isEmpty()) {
                continue; // Si no tiene horario, no generamos clases
            }

            // Rango de fechas del ciclo (6 de julio a 18 de diciembre de 2026)
            $fechaInicio = Carbon::create(2026, 7, 6);
            $fechaFin = Carbon::create(2026, 12, 18);

            // Mapeo de días de la base de datos a formato de Carbon (inglés)
            $diasSemanaMap = [
                'LUNES' => 'Monday',
                'MARTES' => 'Tuesday',
                'MIERCOLES' => 'Wednesday',
                'JUEVES' => 'Thursday',
                'VIERNES' => 'Friday',
                'SABADO' => 'Saturday',
            ];

            foreach ($horariosSeccion as $horario) {
                $diaSemanaIngles = $diasSemanaMap[$horario->dia] ?? null;
                if (!$diaSemanaIngles) continue;

                $fechaActual = clone $fechaInicio;

                while ($fechaActual <= $fechaFin) {
                    // Si el día de la semana coincide con el horario de la sección
                    if ($fechaActual->format('l') === $diaSemanaIngles) {
                        $fechaStr = $fechaActual->format('Y-m-d');

                        // Verificar que no sea feriado
                        if (!in_array($fechaStr, $feriados)) {

                            // Verificar si ya existe la sesión para evitar duplicados (idempotente)
                            $existe = DB::table('clase_sesion')
                                ->where('id_seccion', $seccion->seccion_id)
                                ->where('fecha', $fechaStr)
                                ->where('id_seccion_horario', $horario->seccion_horario_id)
                                ->exists();

                            if (!$existe) {
                                DB::table('clase_sesion')->insert([
                                    'id_seccion' => $seccion->seccion_id,
                                    'fecha' => $fechaStr,
                                    'tema' => 'Clase programada',
                                    'id_seccion_horario' => $horario->seccion_horario_id,
                                    'id_aula' => $horario->id_aula,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]);
                                $totalCreadas++;
                            }
                        }
                    }
                    $fechaActual->addDay();
                }
            }
        }

        $this->command->info("✅ Se generaron {$totalCreadas} sesiones de clase exitosamente.");
    }
}
