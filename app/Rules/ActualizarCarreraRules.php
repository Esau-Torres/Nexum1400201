<?php

namespace App\Rules;

use App\Models\Estudiante\Carreras;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Support\Facades\Validator;

class ActualizarCarreraRules
{
    public static function validate(Carreras $carrera, array $data): ValidatorContract
    {
        $validator = Validator::make($data, [
            'id_facultad'         => ['required', 'integer', 'exists:facultades,facultad_id'],
            'id_nivel_academico'  => ['required', 'integer', 'exists:niveles_academicos,nivel_academico_id'],
            'nombre'              => ['required', 'string', 'min:3', 'max:150'],
            'duracion_ciclos'     => ['required', 'integer', 'min:1', 'max:20'],
        ]);

        $validator->after(function ($v) use ($carrera, $data) {
            // Unicidad por facultad (excluyendo la actual)
            $existe = Carreras::where('carrera_id', '!=', $carrera->carrera_id)
                ->where('id_facultad', $data['id_facultad'] ?? null)
                ->whereRaw('UPPER(nombre) = ?', [mb_strtoupper($data['nombre'] ?? '')])
                ->exists();

            if ($existe) {
                $v->errors()->add('nombre', 'Ya existe otra carrera con ese nombre en esta facultad.');
            }

            // Regla clave: si la carrera tiene pensums vinculados, NO se puede cambiar duracion_ciclos
            if ((int) ($data['duracion_ciclos'] ?? 0) !== (int) $carrera->duracion_ciclos) {
                if ($carrera->tienePensum()) {
                    $v->errors()->add(
                        'duracion_ciclos',
                        'No se puede cambiar la duración de ciclos: la carrera ya tiene pensums vinculados.'
                    );
                }
            }

            // Regla: si la carrera tiene alumnos, no se puede cambiar de facultad
            if ((int) ($data['id_facultad'] ?? 0) !== (int) $carrera->id_facultad) {
                if ($carrera->alumno()->exists()) {
                    $v->errors()->add(
                        'id_facultad',
                        'No se puede cambiar de facultad: la carrera ya tiene estudiantes asignados.'
                    );
                }
            }
        });

        return $validator;
    }
}