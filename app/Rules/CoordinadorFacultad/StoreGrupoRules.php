<?php

namespace App\Rules\CoordinadorFacultad;

use App\Models\Users\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\CoordinadorFacultad\Horarios;

class StoreGrupoRules extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(Role::COORDINADOR_FACULTAD) ?? false;
    }

    public function rules(): array
    {
        $grupoId = $this->route('grupo')?->grupo_id;

        return [
            'id_ciclo_lectivo' => [
                'required', 'integer', 'exists:ciclo_lectivo,ciclo_lectivo_id',
                // No permitir crear grupos en ciclos cerrados
                Rule::exists('ciclo_lectivo', 'ciclo_lectivo_id')
                    ->whereIn('estado', ['CONFIGURACION', 'MATRICULA_ABIERTA']),
            ],
            'nombre'    => [
                'required', 'string', 'min:1', 'max:20',
                'regex:/^[A-ZÑ0-9\-]+$/i',
                Rule::unique('grupo', 'nombre')
                    ->where('id_ciclo_lectivo', $this->input('id_ciclo_lectivo'))
                    ->ignore($grupoId, 'grupo_id'),
            ],
            'activo'    => ['required', 'boolean'],
            'horarios'  => ['nullable', 'array'],
            'horarios.*'=> ['integer', 'exists:horarios,horario_id'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $cicloId  = $this->input('id_ciclo_lectivo');
            $horarios = $this->input('horarios', []);

            if (!$cicloId || empty($horarios)) return;

            // Validar que los horarios elegidos pertenezcan al ciclo
            $validos = Horarios::query()
                ->whereIn('horario_id', $horarios)
                ->where('id_ciclo_lectivo', $cicloId)
                ->pluck('horario_id')
                ->toArray();

            $invalidos = array_diff($horarios, $validos);

            if (!empty($invalidos)) {
                $v->errors()->add(
                    'horarios',
                    'Uno o más bloques seleccionados no pertenecen al ciclo elegido.'
                );
            }

            // Validar que no haya choques entre los bloques del propio grupo
            $bloques = Horarios::query()
                ->whereIn('horario_id', $horarios)
                ->get();

            $choques = [];
            $porDia  = $bloques->groupBy(fn ($h) => $h->dia->value);

            foreach ($porDia as $dia => $items) {
                $ordenados = $items->sortBy('hora_inicio')->values();
                for ($i = 0; $i < count($ordenados) - 1; $i++) {
                    $a = $ordenados[$i];
                    $b = $ordenados[$i + 1];

                    if ($a->hora_fin > $b->hora_inicio) {
                        $choques[] = "{$dia}: {$a->hora_inicio}-{$a->hora_fin} se solapa con {$b->hora_inicio}-{$b->hora_fin}";
                    }
                }
            }

            if (!empty($choques)) {
                $v->errors()->add(
                    'horarios',
                    'El grupo tiene bloques que se solapan: ' . implode(' · ', $choques)
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'id_ciclo_lectivo.required' => 'Seleccioná un ciclo lectivo.',
            'id_ciclo_lectivo.exists'   => 'El ciclo lectivo debe estar en Configuración o en Matricula Abierta.',
            'nombre.required'           => 'El nombre del grupo es obligatorio.',
            'nombre.regex'              => 'El nombre solo puede contener letras, números y guiones.',
            'nombre.unique'             => 'Ya existe un grupo con ese nombre en este ciclo.',
        ];
    }
}