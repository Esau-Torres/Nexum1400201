<?php

namespace App\Rules;

use App\Enums\TipoBeneficio;
use App\Models\Users\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class ApproveStudentRules extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(Role::ADMIN_ACADEMICO) ?? false;
    }

    public function rules(): array
    {
        return [
            'id_regional_activo' => ['required', 'integer', 'exists:regional_activo,regional_activo_id'],

            // Toggle de beneficio
            'aplicar_beneficio' => ['nullable', 'boolean'],
            
            // Beneficio (obligatorio si aplicar_beneficio = 1)
            'id_ciclo_lectivo' => ['nullable', 'integer', 'exists:ciclo_lectivo,ciclo_lectivo_id', 'required_if:aplicar_beneficio,1'],
            'tipo_beneficio' => ['nullable', new Enum(TipoBeneficio::class), 'required_if:aplicar_beneficio,1'],
            'nombre_convenio' => ['nullable', 'string', 'max:150', 'required_if:aplicar_beneficio,1'],
            'porcentaje_estudiante' => ['nullable', 'numeric', 'min:0', 'max:100', 'required_if:tipo_beneficio,BECA_PARCIAL,CUOTA_ESPECIAL'],
            'porcentaje_universidad' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'monto_fijo_cuota' => ['nullable', 'numeric', 'min:0', 'required_if:tipo_beneficio,FRANJA_BECARIA'],
            'resolucion_academica' => ['nullable', 'string', 'max:100'],
            'incluye_matricula' => ['nullable', 'boolean',
                // solo permitido si el tipo es BECA_PARCIAL o CUOTA_ESPECIAL
                Rule::prohibitedIf(fn () => !in_array($this->input('tipo_beneficio'), ['BECA_PARCIAL', 'CUOTA_ESPECIAL'], true)),
            ],
            'incluye_laboratorio' => ['nullable', 'boolean',
                Rule::prohibitedIf(fn () => $this->input('tipo_beneficio') !== 'BECA_COMPLETA'),
            ],
            'incluye_derechos_grado' => ['nullable', 'boolean',
                Rule::prohibitedIf(fn () => true),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'id_ciclo_lectivo.required_if'    => 'Seleccioná el ciclo lectivo para el beneficio.',
            'tipo_beneficio.required_if'      => 'Seleccioná el tipo de beneficio.',
            'nombre_convenio.required_if'     => 'Indicá el nombre del convenio.',
            'monto_fijo_cuota.required_if'    => 'La Franja Becaria requiere monto fijo.',
            'porcentaje_estudiante.required_if'=> 'Indicá el porcentaje del estudiante.',
        ];
    }
}