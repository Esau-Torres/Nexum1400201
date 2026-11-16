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

            if ($seccion->horarios()->exists() && (int) ($data['id_grupo'] ?? 0) !== (int) $seccion->id_grupo) {
                throw new RuntimeException('No se puede cambiar el grupo: la sección ya tiene bloques asignados. Eliminá y recreá la sección.');
            }
            
            $inscritos = $seccion->detallesInscripcion()
                ->whereIn('estado_materia', ['CURSANDO', 'APROBADA'])
                ->count();

            if ((int) $data['cupo_maximo'] < $inscritos) {
                throw new RuntimeException("No se puede reducir el cupo a {$data['cupo_maximo']}: hay {$inscritos} inscrito(s).");
            }

            $nuevoCupo = (int) $data['cupo_maximo'];

            foreach ($seccion->horarios as $sh) {
                $clase = $sh->claseCompartida;
                if (!$clase) continue;

                $aula = $clase->aula;
                if (!$aula) continue;

                $cupoOtrasSecciones = $clase->seccionesHorario()
                    ->join('seccion', 'seccion.seccion_id', '=', 'seccion_horario.id_seccion')
                    ->where('seccion.seccion_id', '!=', $seccion->seccion_id)
                    ->sum('seccion.cupo_maximo');

                $cupoTotal = $cupoOtrasSecciones + $nuevoCupo;

                if ($cupoTotal > $aula->capacidad) {
                    throw new RuntimeException("No se puede aumentar el cupo a {$nuevoCupo}. Excede capacidad de clase compartida.");
                }
            }

            $seccion->update([
                'id_materia'        => $data['id_materia'],
                'id_ciclo_lectivo'  => $data['id_ciclo_lectivo'],
                'id_docente'        => $data['id_docente'],
                'id_grupo'          => $data['id_grupo'] ?? null,
                'nombre_grupo'      => strtoupper($data['nombre_grupo']),
                'cupo_maximo'       => $nuevoCupo,
                'cupo_extra_maximo' => (int) ($data['cupo_extra_maximo'] ?? 0),
                'permite_sobrecupo' => filter_var($data['permite_sobrecupo'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'estado'            => $data['estado'],
            ]);

            return $seccion->fresh(['materia', 'cicloLectivo', 'docente', 'grupo', 'horarios.horario', 'horarios.aula']);
        });
    }
}