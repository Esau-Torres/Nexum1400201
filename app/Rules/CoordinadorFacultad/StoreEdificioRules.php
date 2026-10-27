<?php

namespace App\Rules\CoordinadorFacultad;

use App\Models\Users\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEdificioRules extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(Role::COORDINADOR_FACULTAD) ?? false;
    }

    public function rules(): array
    {
        $edificioId = $this->route('edificio')?->edificio_id;

        return [
            'nombre'      => [
                'required', 'string', 'max:100',
                Rule::unique('edificio', 'nombre')->ignore($edificioId, 'edificio_id'),
            ],
            'niveles'     => ['required', 'integer', 'min:1', 'max:50'],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'activo'      => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del edificio es obligatorio.',
            'nombre.unique'   => 'Ya existe un edificio con ese nombre.',
            'niveles.min'     => 'El edificio debe tener al menos 1 nivel.',
            'niveles.max'     => 'No puede tener más de 50 niveles.',
        ];
    }
}