<?php

namespace App\Rules;

use App\Enums\TipoBeneficio;
use App\Models\Users\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class ReassignBenefitRules extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(Role::ADMIN_ACADEMICO) ?? false;
    }

    public function rules(): array
    {
        return [
            'motivo_cambio'        => ['nullable', 'string', 'max:255'],
            'tipo_beneficio'       => ['required', new Enum(TipoBeneficio::class)],
            'nombre_convenio'      => ['required', 'string', 'max:150'],
            'resolucion_academica' => ['nullable', 'string', 'max:100'],
            'porcentaje_estudiante'  => ['nullable', 'numeric', 'min:0', 'max:100'],
            'porcentaje_universidad' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'monto_fijo_cuota'     => ['nullable', 'numeric', 'min:0'],
            'incluye_matricula'      => ['nullable', 'boolean'],
            'incluye_laboratorio'    => ['nullable', 'boolean'],
            'incluye_derechos_grado' => ['nullable', 'boolean'],
        ];
    }
}