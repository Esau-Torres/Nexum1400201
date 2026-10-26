<?php

namespace App\Actions\CoordinadorFacultad;

use App\Models\CoordinadorFacultad\Seccion;
use App\Models\CoordinadorFacultad\SeccionHorario;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class UpdateSeccionAction
{
    public function execute(Seccion $seccion, array $data): Seccion
    {
        return DB::transaction(function () use ($seccion, $data) {

            if ($seccion->detallesInscripcion()->exists()) {
                if ((int) $data['id_materia'] !== (int) $seccion->id_materia) {
                    throw new RuntimeException('No se puede cambiar la materia: la sección tiene alumnos inscritos.');
                }
                if ((int) $data['id_ciclo_lectivo'] !== (int) $seccion->id_ciclo_lectivo) {
                    throw new RuntimeException('No se puede cambiar el ciclo: la sección tiene alumnos inscritos.');
                }
            }

            if ($seccion->horarios()->exists() && (int) $data['id_grupo'] !== (int) $seccion->id_grupo) {
                throw new RuntimeException(
                    'No se puede cambiar el grupo: la sección ya tiene bloques horarios asignados. Eliminá y recreá la sección.'
                );
            }
            
            $inscritos = $seccion->detallesInscripcion()
                ->whereIn('estado_materia', ['CURSANDO', 'APROBADA'])
                ->count();

            if ((int) $data['cupo_maximo'] < $inscritos) {
                throw new RuntimeException(
                    "No se puede reducir el cupo a {$data['cupo_maximo']}: hay {$inscritos} inscrito(s)."
                );
            }

            // === Validar capacidad del aula contra el nuevo cupo ===
            // Se hace porque en update no se valida en la Rule (no se envían bloques)
            $nuevoCupo = (int) $data['cupo_maximo'];

            foreach ($seccion->horarios as $sh) {
                $clase = $sh->claseCompartida;
                if (!$clase) continue;

                $aula = $clase->aula;
                if (!$aula) continue;

                // Suma de cupos de TODAS las secciones de la clase compartida,
                // restando el cupo actual de esta sección y sumando el nuevo
                $cupoOtrasSecciones = $clase->seccionesHorario()
                    ->join('seccion', 'seccion.seccion_id', '=', 'seccion_horario.id_seccion')
                    ->where('seccion.seccion_id', '!=', $seccion->seccion_id)
                    ->sum('seccion.cupo_maximo');

                $cupoTotal = $cupoOtrasSecciones + $nuevoCupo;

                if ($cupoTotal > $aula->capacidad) {
                    throw new RuntimeException(
                        "No se puede aumentar el cupo a {$nuevoCupo}: el aula '{$aula->nombre_aula}' tiene capacidad {$aula->capacidad} y la suma con las otras secciones compartidas sería {$cupoTotal}."
                    );
                }
            }

            $seccion->update([
                'id_materia'        => $data['id_materia'],
                'id_ciclo_lectivo'  => $data['id_ciclo_lectivo'],
                'id_docente'        => $data['id_docente'],
                'id_grupo'          => $data['id_grupo'] ?? null,
                'nombre_grupo'      => strtoupper($data['nombre_grupo']),
                'cupo_maximo'       => (int) $data['cupo_maximo'],
                'cupo_extra_maximo' => (int) ($data['cupo_extra_maximo'] ?? 0),
                'permite_sobrecupo' => (bool) ($data['permite_sobrecupo'] ?? false),
                'estado'            => $data['estado'],
            ]);

            return $seccion->fresh(['materia', 'cicloLectivo', 'docente', 'grupo', 'horarios.horario', 'horarios.aula']);
        });
    }
}