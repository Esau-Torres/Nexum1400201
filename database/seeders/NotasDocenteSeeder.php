<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NotasDocenteSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener el docente activo (ID 2 según el dump)
        $docente = DB::table('docentes')
            ->where('estado', 'ACTIVO')
            ->first();

        if (!$docente) {
            $this->command->error('No se encontró un docente activo');
            return;
        }

        $this->command->info("Usando docente: {$docente->docente_id} (Usuario: {$docente->id_usuario})");

        // Obtener las secciones reales de este docente
        $secciones = DB::table('seccion')
            ->where('id_docente', $docente->docente_id)
            ->where('estado', 'ACTIVA')
            ->pluck('seccion_id')
            ->toArray();

        if (empty($secciones)) {
            $this->command->error('El docente no tiene secciones activas');
            return;
        }

        $this->command->info("Secciones encontradas: " . implode(', ', $secciones));

        // Obtener alumnos reales
        $alumnos = DB::table('alumnos')
            ->where('estado_carrera', 'ACTIVO')
            ->pluck('alumno_id')
            ->toArray();

        if (empty($alumnos)) {
            $this->command->error('No se encontraron alumnos activos');
            return;
        }

        $this->command->info("Alumnos encontrados: " . implode(', ', $alumnos));

        // Obtener el ciclo lectivo activo (o el más reciente)
        $ciclo = DB::table('ciclo_lectivo')
            ->where('estado', '!=', 'CERRADO')
            ->orderBy('ciclo_lectivo_id', 'desc')
            ->first();

        if (!$ciclo) {
            $this->command->error('No se encontró un ciclo lectivo activo');
            return;
        }

        $this->command->info("Usando ciclo: {$ciclo->ciclo_lectivo_id} ({$ciclo->nombre_ciclo})");

        // 1. PERIODOS DE EVALUACION
        $this->command->info('Creando períodos de evaluación...');
        $periodos = [
            ['id_ciclo_lectivo' => $ciclo->ciclo_lectivo_id, 'nombre' => 'Periodo 1', 'numero_periodo' => 1, 'porcentaje_periodo' => 30.00, 'fecha_inicio' => '2026-07-06', 'fecha_fin' => '2026-08-28', 'estado' => 'ACTIVO'],
            ['id_ciclo_lectivo' => $ciclo->ciclo_lectivo_id, 'nombre' => 'Periodo 2', 'numero_periodo' => 2, 'porcentaje_periodo' => 30.00, 'fecha_inicio' => '2026-09-01', 'fecha_fin' => '2026-10-23', 'estado' => 'ACTIVO'],
            ['id_ciclo_lectivo' => $ciclo->ciclo_lectivo_id, 'nombre' => 'Periodo 3', 'numero_periodo' => 3, 'porcentaje_periodo' => 40.00, 'fecha_inicio' => '2026-10-26', 'fecha_fin' => '2026-12-18', 'estado' => 'ACTIVO'],
        ];

        $periodoIds = [];
        foreach ($periodos as $periodo) {
            $registro = DB::table('periodo_evaluacion')
                ->where('id_ciclo_lectivo', $periodo['id_ciclo_lectivo'])
                ->where('numero_periodo', $periodo['numero_periodo'])
                ->first();

            if (!$registro) {
                $periodoIds[$periodo['numero_periodo']] = DB::table('periodo_evaluacion')
                    ->insertGetId($periodo, 'periodo_evaluacion_id');
                $this->command->info("  ✓ Período {$periodo['numero_periodo']} creado");
            } else {
                $periodoIds[$periodo['numero_periodo']] = $registro->periodo_evaluacion_id;
                $this->command->info("  → Período {$periodo['numero_periodo']} ya existe");
            }
        }

        // 2. CATEGORIAS
        $this->command->info('Creando categorías de evaluación...');
        $categorias = [];
        foreach ($periodoIds as $numeroPeriodo => $periodoId) {
            foreach ($secciones as $seccionId) {
                foreach (['LABORATORIO' => 40.00, 'PARCIAL' => 60.00] as $tipo => $porcentaje) {
                    $registro = DB::table('categoria_evaluacion')
                        ->where('id_periodo_evaluacion', $periodoId)
                        ->where('id_seccion', $seccionId)
                        ->where('tipo_categoria', $tipo)
                        ->first();

                    if (!$registro) {
                        $categoriaId = DB::table('categoria_evaluacion')->insertGetId([
                            'id_periodo_evaluacion' => $periodoId,
                            'id_seccion' => $seccionId,
                            'tipo_categoria' => $tipo,
                            'porcentaje_categoria' => $porcentaje,
                        ], 'categoria_id');
                        $this->command->info("  ✓ Categoría {$tipo} creada para sección {$seccionId}, período {$numeroPeriodo}");
                    } else {
                        $categoriaId = $registro->categoria_id;
                    }
                    $categorias[$numeroPeriodo][$seccionId][$tipo] = $categoriaId;
                }
            }
        }

        // 3. EVALUACIONES
        $this->command->info('Creando evaluaciones...');
        foreach ($categorias as $numeroPeriodo => $seccionesCategorias) {
            foreach ($seccionesCategorias as $seccionId => $tipos) {
                if (!DB::table('evaluaciones')->where('id_categoria', $tipos['LABORATORIO'])->where('nombre_evaluacion', 'Laboratorio ' . $numeroPeriodo)->exists()) {
                    DB::table('evaluaciones')->insert([
                        'id_categoria' => $tipos['LABORATORIO'],
                        'nombre_evaluacion' => 'Laboratorio ' . $numeroPeriodo,
                        'tipo_instrumento' => 'LABORATORIO',
                        'porcentaje_actividad' => 100.00,
                        'fecha_aplicacion' => null,
                        'fecha_limite_calificacion' => null,
                        'fecha_limite_revision' => null,
                        'estado' => 'PROGRAMADA',
                    ]);
                    $this->command->info("  ✓ Laboratorio {$numeroPeriodo} creado para sección {$seccionId}");
                }

                if (!DB::table('evaluaciones')->where('id_categoria', $tipos['PARCIAL'])->where('nombre_evaluacion', 'Parcial ' . $numeroPeriodo)->exists()) {
                    DB::table('evaluaciones')->insert([
                        'id_categoria' => $tipos['PARCIAL'],
                        'nombre_evaluacion' => 'Parcial ' . $numeroPeriodo,
                        'tipo_instrumento' => 'EXAMEN',
                        'porcentaje_actividad' => 100.00,
                        'fecha_aplicacion' => null,
                        'fecha_limite_calificacion' => null,
                        'fecha_limite_revision' => null,
                        'estado' => 'PROGRAMADA',
                    ]);
                    $this->command->info("  ✓ Parcial {$numeroPeriodo} creado para sección {$seccionId}");
                }
            }
        }

        // 4. INSCRIPCIONES
        $this->command->info('Creando inscripciones...');
        $inscripcionIds = [];
        foreach ($alumnos as $alumnoId) {
            $registro = DB::table('inscripciones')
                ->where('id_alumno', $alumnoId)
                ->where('id_ciclo_lectivo', $ciclo->ciclo_lectivo_id)
                ->first();

            if (!$registro) {
                $inscripcionIds[$alumnoId] = DB::table('inscripciones')->insertGetId([
                    'id_alumno' => $alumnoId,
                    'id_ciclo_lectivo' => $ciclo->ciclo_lectivo_id,
                    'fecha_inscripcion' => '2026-07-15',
                    'estado_inscripcion' => 'ACTIVA',
                ], 'inscripcion_id');
                $this->command->info("  ✓ Inscripción creada para alumno {$alumnoId}");
            } else {
                $inscripcionIds[$alumnoId] = $registro->inscripcion_id;
                $this->command->info("  → Inscripción del alumno {$alumnoId} ya existe");
            }
        }

        // 5. DETALLE_INSCRIPCION
        $this->command->info('Creando detalles de inscripción...');
        $seccionesParaInscribir = array_slice($secciones, 0, 3); // Solo las primeras 3 secciones
        foreach ($alumnos as $alumnoId) {
            $inscripcionId = $inscripcionIds[$alumnoId];
            foreach ($seccionesParaInscribir as $seccionId) {
                if (!DB::table('detalle_inscripcion')->where('id_inscripcion', $inscripcionId)->where('id_seccion', $seccionId)->exists()) {
                    DB::table('detalle_inscripcion')->insert([
                        'id_inscripcion' => $inscripcionId,
                        'id_seccion' => $seccionId,
                        'numero_matricula' => 1,
                        'promedio_final' => null,
                        'estado_materia' => 'CURSANDO',
                        'es_sobrecupo' => false,
                        'autorizado_por' => null,
                        'fecha_retiro' => null,
                    ]);
                    $this->command->info("  ✓ Alumno {$alumnoId} inscrito en sección {$seccionId}");
                }
            }
        }

        $this->command->info('✅ Seeder ejecutado correctamente');
    }
}
