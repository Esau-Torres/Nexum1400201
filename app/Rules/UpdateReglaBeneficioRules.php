<?php

namespace App\Rules;

use App\Enums\TipoBeneficio;
use App\Models\Users\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateReglaBeneficioRules extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(Role::ADMIN_ACADEMICO) ?? false;
    }

    public function rules(): array
    {
        $reglaId = $this->route('regla')?->regla_beneficio_id;

        return [
            'tipo_beneficio' => [
                'required',
                new Enum(TipoBeneficio::class),
                // El tipo no se puede duplicar (aunque ya existe, por si acaso)
                Rule::unique('reglas_beneficio', 'tipo_beneficio')
                    ->where('vigente_desde', $this->input('vigente_desde'))
                    ->ignore($reglaId, 'regla_beneficio_id'),
            ],
            'cum_minimo'                    => ['required', 'numeric', 'min:0', 'max:10'],
            'nota_minima_materia'           => ['required', 'numeric', 'min:0', 'max:10'],
            'max_reprobaciones_permitidas'  => ['required', 'integer', 'min:0', 'max:20'],
            'max_materias_reprobadas_ciclo' => ['required', 'integer', 'min:0', 'max:20'],
            'uv_minimas_cursadas'           => ['required', 'integer', 'min:0', 'max:500'],
            'requiere_solvencia_financiera' => ['boolean'],
            'requiere_actividades'          => ['boolean'],
            'observaciones'                 => ['nullable', 'string', 'max:1000'],
            'activo'                        => ['boolean'],
            'vigente_desde'                 => ['required', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'tipo_beneficio.required'                => 'Seleccioná el tipo de beneficio.',
            'tipo_beneficio.unique'                  => 'Ya existe una regla con este tipo para esa fecha de vigencia.',
            'cum_minimo.required'                    => 'El CUM mínimo es obligatorio.',
            'cum_minimo.numeric'                     => 'El CUM mínimo debe ser un número.',
            'cum_minimo.min'                         => 'El CUM mínimo no puede ser negativo.',
            'cum_minimo.max'                         => 'El CUM mínimo no puede superar 10.',
            'nota_minima_materia.required'           => 'La nota mínima por materia es obligatoria.',
            'nota_minima_materia.max'                => 'La nota mínima por materia no puede superar 10.',
            'max_reprobaciones_permitidas.required'  => 'El máximo de reprobaciones es obligatorio.',
            'max_reprobaciones_permitidas.min'       => 'El máximo de reprobaciones no puede ser negativo.',
            'max_materias_reprobadas_ciclo.required' => 'El máximo de materias reprobadas por ciclo es obligatorio.',
            'uv_minimas_cursadas.required'           => 'Las UV mínimas cursadas son obligatorias.',
            'vigente_desde.required'                 => 'La fecha de vigencia es obligatoria.',
            'vigente_desde.date'                     => 'La fecha de vigencia no es válida.',
        ];
    }
}