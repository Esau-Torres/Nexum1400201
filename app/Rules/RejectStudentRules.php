<?php

namespace App\Rules;

use App\Models\Users\Role;
use Illuminate\Foundation\Http\FormRequest;

class RejectStudentRules extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(Role::ADMIN_ACADEMICO) ?? false;
    }

    public function rules(): array
    {
        return [
            'motivo_rechazo' => ['required', 'string', 'min:10', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'motivo_rechazo.required' => 'Indicá el motivo del rechazo (queda registrado).',
            'motivo_rechazo.min'      => 'El motivo debe tener al menos 10 caracteres.',
        ];
    }
}