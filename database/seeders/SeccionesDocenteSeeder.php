<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SeccionesDocenteSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Creando secciones del docente 2...');

        // Datos de las secciones del docente 2 (del dump SQL original)
        $secciones = [
            [
                'seccion_id' => 10,
                'id_materia' => 68,  // Matematica 1
                'id_ciclo_lectivo' => 2,
                'id_docente' => 2,
                'nombre_grupo' => 'D-INFORMATICA-CICLO1',
                'cupo_maximo' => 20,
                'cupo_extra_maximo' => 0,
                'permite_sobrecupo' => false,
                'estado' => 'ACTIVA',
                'id_grupo' => 7,
            ],
            [
                'seccion_id' => 11,
                'id_materia' => 151,  // Algoritmos
                'id_ciclo_lectivo' => 2,
                'id_docente' => 2,
                'nombre_grupo' => 'E-INFORMATICA-CICLO1',
                'cupo_maximo' => 20,
                'cupo_extra_maximo' => 0,
                'permite_sobrecupo' => false,
                'estado' => 'ACTIVA',
                'id_grupo' => 8,
            ],
            [
                'seccion_id' => 12,
                'id_materia' => 75,  // Informatica 1
                'id_ciclo_lectivo' => 2,
                'id_docente' => 2,
                'nombre_grupo' => 'I-INFORMATICA-CICLO1',
                'cupo_maximo' => 20,
                'cupo_extra_maximo' => 0,
                'permite_sobrecupo' => false,
                'estado' => 'ACTIVA',
                'id_grupo' => 13,
            ],
            [
                'seccion_id' => 13,
                'id_materia' => 152,  // Seminario taller de competencias
                'id_ciclo_lectivo' => 2,
                'id_docente' => 2,
                'nombre_grupo' => 'J-INFORMATICA-CICLO1',
                'cupo_maximo' => 20,
                'cupo_extra_maximo' => 0,
                'permite_sobrecupo' => false,
                'estado' => 'ACTIVA',
                'id_grupo' => 14,
            ],
            [
                'seccion_id' => 14,
                'id_materia' => 153,  // Seminario de gramatica y ortografia
                'id_ciclo_lectivo' => 2,
                'id_docente' => 2,
                'nombre_grupo' => 'Ñ-INFORMATICA-CICLO1',
                'cupo_maximo' => 20,
                'cupo_extra_maximo' => 0,
                'permite_sobrecupo' => false,
                'estado' => 'ACTIVA',
                'id_grupo' => 18,
            ],
        ];

        foreach ($secciones as $seccion) {
            $existe = DB::table('seccion')
                ->where('id_materia', $seccion['id_materia'])
                ->where('id_ciclo_lectivo', $seccion['id_ciclo_lectivo'])
                ->where('nombre_grupo', $seccion['nombre_grupo'])
                ->exists();

            if (!$existe) {
                // Usar OVERRIDING SYSTEM VALUE para permitir insertar IDs específicos
                DB::statement("
                    INSERT INTO seccion (
                        seccion_id, id_materia, id_ciclo_lectivo, id_docente, 
                        nombre_grupo, cupo_maximo, cupo_extra_maximo, 
                        permite_sobrecupo, estado, id_grupo, created_at, updated_at
                    ) OVERRIDING SYSTEM VALUE VALUES (
                        ?, ?, ?, ?, ?, ?, ?, ?, ?::estado_seccion, ?, NOW(), NOW()
                    )
                ", [
                    $seccion['seccion_id'],
                    $seccion['id_materia'],
                    $seccion['id_ciclo_lectivo'],
                    $seccion['id_docente'],
                    $seccion['nombre_grupo'],
                    $seccion['cupo_maximo'],
                    $seccion['cupo_extra_maximo'],
                    $seccion['permite_sobrecupo'],
                    $seccion['estado'],
                    $seccion['id_grupo'],
                ]);

                $this->command->info("  ✓ Sección creada: {$seccion['nombre_grupo']} (ID: {$seccion['seccion_id']})");
            } else {
                $this->command->info("  → Sección ya existe: {$seccion['nombre_grupo']}");
            }
        }

        // Crear seccion_horario (relación sección-horario-aula)
        $this->command->info('Creando horarios de las secciones...');

        $seccionHorarios = [
            ['seccion_horario_id' => 15, 'id_seccion' => 10, 'id_horario' => 7, 'id_aula' => 5],
            ['seccion_horario_id' => 16, 'id_seccion' => 10, 'id_horario' => 10, 'id_aula' => 5],
            ['seccion_horario_id' => 17, 'id_seccion' => 11, 'id_horario' => 8, 'id_aula' => 5],
            ['seccion_horario_id' => 18, 'id_seccion' => 11, 'id_horario' => 9, 'id_aula' => 5],
            ['seccion_horario_id' => 19, 'id_seccion' => 12, 'id_horario' => 18, 'id_aula' => 4],
            ['seccion_horario_id' => 20, 'id_seccion' => 12, 'id_horario' => 19, 'id_aula' => 4],
            ['seccion_horario_id' => 21, 'id_seccion' => 13, 'id_horario' => 20, 'id_aula' => 4],
            ['seccion_horario_id' => 22, 'id_seccion' => 13, 'id_horario' => 21, 'id_aula' => 4],
            ['seccion_horario_id' => 23, 'id_seccion' => 14, 'id_horario' => 29, 'id_aula' => 2],
            ['seccion_horario_id' => 24, 'id_seccion' => 14, 'id_horario' => 28, 'id_aula' => 2],
        ];

        foreach ($seccionHorarios as $sh) {
            $existe = DB::table('seccion_horario')
                ->where('id_seccion', $sh['id_seccion'])
                ->where('id_horario', $sh['id_horario'])
                ->exists();

            if (!$existe) {
                // Usar OVERRIDING SYSTEM VALUE para seccion_horario también
                DB::statement("
                    INSERT INTO seccion_horario (
                        seccion_horario_id, id_seccion, id_horario, id_aula, created_at
                    ) OVERRIDING SYSTEM VALUE VALUES (?, ?, ?, ?, NOW())
                ", [
                    $sh['seccion_horario_id'],
                    $sh['id_seccion'],
                    $sh['id_horario'],
                    $sh['id_aula'],
                ]);
            }
        }

        $this->command->info('✅ Secciones y horarios creados correctamente');
    }
}
