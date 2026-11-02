<?php

namespace App\Rules\Student;

use App\Models\Users\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEvaluacionDocenteRules extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(Role::ADMIN_ACADEMICO) ?? false;
    }

    public function rules(): array
    {
        $evaluacion = $this->route('evaluacion');
        $esUpdate   = $evaluacion !== null;

        if ($esUpdate) {
            // En update solo se permite cambiar la URL
            return [
                'url' => [
                    'required', 'string', 'max:2000',
                    'url', 'regex:/^https?:\/\//i',
                ],
            ];
        }

        // En store se valida todo
        $idCiclo = is_numeric($this->input('id_ciclo_lectivo'))
            ? (int) $this->input('id_ciclo_lectivo')
            : null;

        return [
            'id_seccion' => [
                'required', 'integer', 'exists:seccion,seccion_id',
                Rule::unique('evaluacion_docente', 'id_seccion')
                    ->where('id_ciclo_lectivo', $idCiclo),
            ],
            'id_ciclo_lectivo' => ['required', 'integer', 'exists:ciclo_lectivo,ciclo_lectivo_id'],
            'url' => [
                'required', 'string', 'max:2000',
                'url', 'regex:/^https?:\/\//i',
            ],
        ];
    }

    public function withValidator($validator): void
    {
        if ($this->route('evaluacion')) {
            return; // En update no hay más validaciones
        }

        $validator->after(function ($v) {
            $seccionId = $this->input('id_seccion');
            $cicloId   = $this->input('id_ciclo_lectivo');

            if (!$seccionId || !$cicloId) return;

            $coincide = \DB::table('seccion')
                ->where('seccion_id', $seccionId)
                ->where('id_ciclo_lectivo', $cicloId)
                ->exists();

            if (!$coincide) {
                $v->errors()->add(
                    'id_seccion',
                    'La sección seleccionada no pertenece al ciclo lectivo indicado.'
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'id_seccion.required'       => 'Seleccioná una sección.',
            'id_seccion.exists'         => 'La sección no existe.',
            'id_seccion.unique'         => 'Ya existe una encuesta para esta sección en este ciclo.',
            'id_ciclo_lectivo.required' => 'Seleccioná un ciclo lectivo.',
            'id_ciclo_lectivo.exists'   => 'El ciclo lectivo no existe.',
            'url.required'              => 'La URL de la encuesta es obligatoria.',
            'url.url'                   => 'La URL no es válida.',
            'url.regex'                 => 'La URL debe comenzar con http:// o https://',
        ];
    }
}