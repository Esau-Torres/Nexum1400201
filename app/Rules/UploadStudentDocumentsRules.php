<?php

namespace App\Rules;

use App\Models\Users\Role;
use Illuminate\Foundation\Http\FormRequest;

class UploadStudentDocumentsRules extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(Role::ADMIN_ACADEMICO) ?? false;
    }

    public function rules(): array
    {
        return [
            'titulo_bachillerato' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
            'partida_nacimiento'  => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
            'fotografia_personal' => ['nullable', 'file', 'mimes:jpg,jpeg,png',     'max:2048'],
            'constancia_paes'     => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
            'observaciones'       => ['nullable', 'string', 'max:1000'],
        ];
    }
}