<?php

namespace App\Actions\CoordinadorFacultad;

use App\Models\CoordinadorFacultad\ClaseCompartida;
use App\Models\CoordinadorFacultad\Grupo;
use App\Models\CoordinadorFacultad\Seccion;
use App\Models\CoordinadorFacultad\SeccionHorario;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CreateSeccionAction
{
    public function execute(array $data): Seccion
    {
        return DB::transaction(function () use ($data) {

            // 1. Resolver los bloques horarios definitivos
            $bloques = $this->resolverBloques($data);

            if (empty($bloques)) {
                throw new RuntimeException('Debés asignar al menos un bloque horario a la sección.');
            }

            // 2. Crear la sección
            $seccion = Seccion::create([
                'id_materia'        => $data['id_materia'],
                'id_ciclo_lectivo'  => $data['id_ciclo_lectivo'],
                'id_docente'        => $data['id_docente'],
                'id_grupo'          => $data['id_grupo'] ?? null,
                'nombre_grupo'      => strtoupper($data['nombre_grupo']),
                'cupo_maximo'       => (int) $data['cupo_maximo'],
                'cupo_extra_maximo' => (int) $data['cupo_extra_maximo'] ?? 0,
                'permite_sobrecupo' => (bool) $data['permite_sobrecupo'],
                'estado'            => $data['estado'],
            ]);

            // 3. Insertar los bloques
            foreach ($bloques as $b) {
                $claseCompartida = $this->obtenerOCrearClaseCompartida(
                    idDocente: (int) $data['id_docente'],
                    idCiclo:   (int) $data['id_ciclo_lectivo'],
                    idAula:    (int) $b['id_aula'],
                    idHorario: (int) $b['id_horario'],
                    cupoNuevo: (int) $data['cupo_maximo'],
                );

                SeccionHorario::create([
                    'id_seccion'          => $seccion->seccion_id,
                    'id_horario'          => (int) $b['id_horario'],
                    'id_aula'             => (int) $b['id_aula'],
                    'id_clase_compartida' => $claseCompartida->clase_compartida_id,
                ]);
            }

            return $seccion->fresh([
                'materia', 'cicloLectivo', 'docente', 'grupo',
                'horarios.horario', 'horarios.aula', 'horarios.claseCompartida',
            ]);
        });
    }

    /**
     * Busca una clase compartida existente o crea una nueva.
     * Valida que la capacidad del aula alcance para sumar el nuevo cupo.
     */
    private function obtenerOCrearClaseCompartida(
        int $idDocente,
        int $idCiclo,
        int $idAula,
        int $idHorario,
        int $cupoNuevo
    ): ClaseCompartida {
        $existente = ClaseCompartida::query()
            ->where('id_docente', $idDocente)
            ->where('id_ciclo_lectivo', $idCiclo)
            ->where('id_horario', $idHorario)
            ->lockForUpdate()
            ->first();

        if ($existente) {
            // Verificar que la capacidad del aula alcance para sumar el nuevo cupo
            $aula = $existente->aula;
            $cupoActual = $existente->cupo_total;
            $cupoTotal = $cupoActual + $cupoNuevo;

            if ($aula && $cupoTotal > $aula->capacidad) {
                throw new RuntimeException(
                    "No se puede compartir la clase: el aula '{$aula->nombre_aula}' tiene capacidad {$aula->capacidad} y la suma de cupos sería {$cupoTotal}."
                );
            }

            return $existente;
        }

        // Crear nueva clase compartida
        return ClaseCompartida::create([
            'id_docente'       => $idDocente,
            'id_ciclo_lectivo' => $idCiclo,
            'id_aula'          => $idAula,
            'id_horario'       => $idHorario,
        ]);
    }

    /**
     * Resuelve los bloques definitivos de la sección.
     *
     * - Si hay grupo: usa los bloques del grupo, y toma las aulas del input `aulas_por_horario`.
     * - Si NO hay grupo: usa los bloques del input `horarios[]`.
     *
     * @return array<int, array{id_horario:int, id_aula:int}>
     */
    private function resolverBloques(array $data): array
    {
        $idGrupo = $data['id_grupo'] ?? null;

        if ($idGrupo) {
            $grupo = Grupo::with('horarios')->find($idGrupo);
            if (!$grupo) {
                throw new RuntimeException('El grupo seleccionado no existe.');
            }

            // Aulas vienen como: aulas_por_horario[<horario_id>] = <aula_id>
            $aulasPorHorario = $data['aulas_por_horario'] ?? [];
            $bloques = [];

            foreach ($grupo->horarios as $horario) {
                $idAula = $aulasPorHorario[$horario->horario_id] ?? null;
                if (!$idAula) {
                    throw new RuntimeException(
                        "Falta asignar un aula para el bloque del {$horario->dia->label()} " .
                        "{$horario->hora_inicio}–{$horario->hora_fin}."
                    );
                }
                $bloques[] = [
                    'id_horario' => $horario->horario_id,
                    'id_aula'    => (int) $idAula,
                ];
            }

            return $bloques;
        }

        // Sin grupo: los bloques vienen del input directo
        $bloques = [];
        foreach ($data['horarios'] ?? [] as $h) {
            if (empty($h['id_horario']) || empty($h['id_aula'])) continue;
            $bloques[] = [
                'id_horario' => (int) $h['id_horario'],
                'id_aula'    => (int) $h['id_aula'],
            ];
        }

        return $bloques;
    }
}