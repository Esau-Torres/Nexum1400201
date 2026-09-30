<?php

namespace App\Rules;

use App\Models\Users\Role;
use Illuminate\Foundation\Http\FormRequest;

class RevokeBenefitRules extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(Role::ADMIN_ACADEMICO) ?? false;
    }

    public function rules(): array
    {
        return [
            'motivo_revocacion' => ['required', 'string', 'min:10', 'max:500'],
        ];
    }
}