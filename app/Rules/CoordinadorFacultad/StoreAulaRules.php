<?php

namespace App\Rules\CoordinadorFacultad;

use App\Enums\TipoAula;
use App\Models\Users\Role;
use App\Models\CoordinadorFacultad\Edificio;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreAulaRules extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(Role::COORDINADOR_FACULTAD) ?? false;
    }

    public function rules(): array
    {
        $aulaId = $this->route('aula')?->aula_id;

        return [
            'id_edificio' => ['required', 'integer', 'exists:edificio,edificio_id'],
            'nombre_aula' => [
                'required', 'string', 'max:50',
                // No puede repetirse el nombre en el mismo edificio + nivel
                Rule::unique('aula', 'nombre_aula')
                    ->where('id_edificio', $this->input('id_edificio'))
                    ->where('nivel', $this->input('nivel'))
                    ->ignore($aulaId, 'aula_id'),
            ],
            'nivel'       => ['required', 'integer', 'min:1'],
            'capacidad'   => ['required', 'integer', 'min:1', 'max:500'],
            'tipo'        => ['required', new Enum(TipoAula::class)],
            'activo'      => ['required', 'boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $edificioId = $this->input('id_edificio');
            $nivel      = (int) $this->input('nivel');

            if (!$edificioId || !$nivel) return;

            $edificio = Edificio::find($edificioId);
            if (!$edificio) return;

            if ($nivel > $edificio->niveles) {
                $v->errors()->add(
                    'nivel',
                    "El edificio '{$edificio->nombre}' solo tiene {$edificio->niveles} nivel(es)."
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'id_edificio.required' => 'Seleccioná un edificio.',
            'nombre_aula.required' => 'El nombre del aula es obligatorio.',
            'nombre_aula.unique'   => 'Ya existe un aula con ese nombre en el mismo edificio y nivel.',
            'capacidad.min'        => 'La capacidad debe ser mayor a 0.',
            'tipo.required'        => 'Seleccioná un tipo de aula.',
        ];
    }
}