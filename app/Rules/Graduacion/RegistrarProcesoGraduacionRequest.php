<?php

namespace App\Rules\Graduacion;

use App\Enums\ModalidadGraduacion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class RegistrarProcesoGraduacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'modalidad' => ['required', Rule::enum(ModalidadGraduacion::class)],
            'id_grupo'  => ['nullable', 'integer', 'exists:grupos_graduacion,grupo_id'],
            'titulo_tema' => [
                Rule::requiredIf(fn () =>
                    $this->modalidad === ModalidadGraduacion::PRE_ESPECIALIZACION->value
                    || ($this->modalidad === ModalidadGraduacion::TRABAJO_GRADUACION->value && !$this->filled('id_grupo'))
                ),
                'nullable', 'string', 'min:10', 'max:255',
            ],
            'id_docente_asesor' => ['nullable', 'integer', 'exists:docentes,docente_id'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $modalidad = $this->modalidad;

            // PRE_ESPECIALIZACION no lleva grupo ni título propio
            if ($modalidad === ModalidadGraduacion::PRE_ESPECIALIZACION->value) {
                if ($this->filled('id_grupo')) {
                    $v->errors()->add('id_grupo', 'La pre-especialización no permite grupos.');
                }
            }

            // TRABAJO_GRADUACION requiere grupo o título nuevo
            if ($modalidad === ModalidadGraduacion::TRABAJO_GRADUACION->value) {
                if (!$this->filled('id_grupo') && !$this->filled('titulo_tema')) {
                    $v->errors()->add('titulo_tema', 'Debes indicar el título del tema o unirte a un grupo existente.');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'modalidad.required' => 'Debes seleccionar una modalidad.',
            'titulo_tema.required' => 'El título del tema es obligatorio para trabajo de graduación.',
            'titulo_tema.min' => 'El título debe tener al menos 10 caracteres.',
        ];
    }
}