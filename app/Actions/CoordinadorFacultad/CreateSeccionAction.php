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
            $bloques = $this->resolverBloques($data);

            if (empty($bloques)) {
                throw new RuntimeException('Debés asignar al menos un bloque horario a la sección.');
            }

            // FIX: filter_var protege contra 'Undefined array key' de los checkboxes
            $seccion = Seccion::create([
                'id_materia'        => $data['id_materia'],
                'id_ciclo_lectivo'  => $data['id_ciclo_lectivo'],
                'id_docente'        => $data['id_docente'],
                'id_grupo'          => $data['id_grupo'] ?? null,
                'nombre_grupo'      => strtoupper($data['nombre_grupo']),
                'cupo_maximo'       => (int) $data['cupo_maximo'],
                'cupo_extra_maximo' => (int) ($data['cupo_extra_maximo'] ?? 0),
                'permite_sobrecupo' => filter_var($data['permite_sobrecupo'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'estado'            => $data['estado'],
            ]);

            foreach ($bloques as $b) {
                $claseCompartida = $this->obtenerOCrearClaseCompartida(
                    (int) $data['id_docente'],
                    (int) $data['id_ciclo_lectivo'],
                    (int) $b['id_aula'],
                    (int) $b['id_horario'],
                    (int) $data['cupo_maximo']
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

    private function obtenerOCrearClaseCompartida(int $idDocente, int $idCiclo, int $idAula, int $idHorario, int $cupoNuevo): ClaseCompartida 
    {
        $existente = ClaseCompartida::query()
            ->where('id_docente', $idDocente)
            ->where('id_ciclo_lectivo', $idCiclo)
            ->where('id_horario', $idHorario)
            ->lockForUpdate()
            ->first();

        if ($existente) {
            $aula = $existente->aula;
            $cupoTotal = $existente->cupo_total + $cupoNuevo;

            if ($aula && $cupoTotal > $aula->capacidad) {
                throw new RuntimeException("Capacidad excedida para el aula '{$aula->nombre_aula}'.");
            }
            return $existente;
        }

        return ClaseCompartida::create([
            'id_docente'       => $idDocente,
            'id_ciclo_lectivo' => $idCiclo,
            'id_aula'          => $idAula,
            'id_horario'       => $idHorario,
        ]);
    }

    private function resolverBloques(array $data): array
    {
        $idGrupo = $data['id_grupo'] ?? null;

        if ($idGrupo) {
            $grupo = Grupo::with('horarios')->find($idGrupo);
            $aulasPorHorario = $data['aulas_por_horario'] ?? [];
            $bloques = [];

            foreach ($grupo->horarios as $horario) {
                $idAula = $aulasPorHorario[$horario->horario_id] ?? null;
                if (!$idAula) {
                    $diaText = is_object($horario->dia) && method_exists($horario->dia, 'label') ? $horario->dia->label() : (is_object($horario->dia) ? $horario->dia->value : $horario->dia);
                    throw new RuntimeException("Falta asignar un aula para el bloque del {$diaText}.");
                }
                $bloques[] = ['id_horario' => $horario->horario_id, 'id_aula' => (int) $idAula];
            }
            return $bloques;
        }

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