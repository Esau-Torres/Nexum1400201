<?php

namespace App\Rules\Egreso;

use Illuminate\Foundation\Http\FormRequest;

class PasantiaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'requiere_pasantia' => ['required', 'boolean'],
            'pasantia_aprobada' => ['required', 'boolean'],
            'institucion_pasantia' => ['nullable', 'string', 'max:150'],
            'documento'            => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'documento.mimes' => 'El documento debe ser PDF, JPG o PNG.',
            'documento.max'   => 'El archivo no puede exceder 5 MB.',
        ];
    }
}