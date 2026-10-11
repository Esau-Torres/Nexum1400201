<?php

namespace App\Rules;

use App\Enums\TipoBeneficio;
use App\Models\Users\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class AssignBenefitRules extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(Role::ADMIN_ACADEMICO) ?? false;
    }

    public function rules(): array
    {
        return [
            'id_ciclo_lectivo'    => ['required', 'integer', 'exists:ciclo_lectivo,ciclo_lectivo_id'],
            'tipo_beneficio'      => ['required', new Enum(TipoBeneficio::class)],
            'nombre_convenio'     => ['required', 'string', 'max:150'],
            'resolucion_academica'=> ['nullable', 'string', 'max:100'],

            'porcentaje_estudiante'  => [
                'nullable', 'numeric', 'min:0', 'max:100',
                'required_if:tipo_beneficio,' . TipoBeneficio::BECA_PARCIAL->value . ',' . TipoBeneficio::CUOTA_ESPECIAL->value,
            ],
            'porcentaje_universidad' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'monto_fijo_cuota'       => [
                'nullable', 'numeric', 'min:0',
                'required_if:tipo_beneficio,' . TipoBeneficio::FRANJA_BECARIA->value,
            ],

            'incluye_matricula'      => [
                'nullable', 'boolean',
                Rule::prohibitedIf(fn () => !in_array(
                    $this->input('tipo_beneficio'),
                    [TipoBeneficio::BECA_PARCIAL->value, TipoBeneficio::CUOTA_ESPECIAL->value],
                    true
                )),
            ],
            'incluye_laboratorio'    => [
                'nullable', 'boolean',
                Rule::prohibitedIf(fn () => $this->input('tipo_beneficio') !== TipoBeneficio::BECA_COMPLETA->value),
            ],
            'incluye_derechos_grado' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_ciclo_lectivo.required'      => 'Seleccioná el ciclo lectivo.',
            'tipo_beneficio.required'        => 'Seleccioná el tipo de beneficio.',
            'nombre_convenio.required'       => 'Indicá el nombre del convenio o resolución.',
            'monto_fijo_cuota.required_if'   => 'La Franja Becaria requiere un monto fijo.',
            'porcentaje_estudiante.required_if' => 'Indicá el porcentaje que paga el estudiante.',
        ];
    }
}