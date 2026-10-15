<?php

namespace App\Rules\CoordinadorFacultad;

use App\Enums\DiaSemana;
use App\Enums\TipoJornada;
use App\Models\Users\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use App\Models\CoordinadorFacultad\Horarios;

class StoreHorarioRules extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(Role::COORDINADOR_FACULTAD) ?? false;
    }

    public function rules(): array
    {
        $horarioId = $this->route('horario')?->horario_id;

        return [
            'id_ciclo_lectivo' => ['required', 'integer', 'exists:ciclo_lectivo,ciclo_lectivo_id'],
            'dia'              => ['required', new Enum(DiaSemana::class)],
            'hora_inicio'      => [
                'required', 'date_format:H:i',
                'after_or_equal:06:00', 'before_or_equal:20:30',
            ],
            'hora_fin'         => [
                'required', 'date_format:H:i',
                'after:hora_inicio', 'before_or_equal:20:30',
            ],
            'jornada'          => ['required', new Enum(TipoJornada::class)],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            // Validar solapamiento con otros horarios del mismo ciclo + día
            $cicloId = $this->input('id_ciclo_lectivo');
            $dia     = $this->input('dia');
            $inicio  = $this->input('hora_inicio');
            $fin     = $this->input('hora_fin');

            if (!$cicloId || !$dia || !$inicio || !$fin) return;

            $horarioId = $this->route('horario')?->horario_id;

            $choque = Horarios::query()
                ->where('id_ciclo_lectivo', $cicloId)
                ->where('dia', $dia)
                ->when($horarioId, fn ($q) => $q->where('horario_id', '!=', $horarioId))
                ->where(function ($q) use ($inicio, $fin) {
                    // Solapamiento: inicio < fin_otro AND fin > inicio_otro
                    $q->where('hora_inicio', '<', $fin)
                      ->where('hora_fin', '>', $inicio);
                })
                ->exists();

            if ($choque) {
                $v->errors()->add(
                    'hora_inicio',
                    'Ya existe un bloque horario que se solapa con este rango en el mismo día y ciclo.'
                );
            }

            // Validar que hora_fin > hora_inicio (redundante con after, pero por si acaso)
            if ($inicio >= $fin) {
                $v->errors()->add('hora_fin', 'La hora de fin debe ser mayor a la hora de inicio.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'id_ciclo_lectivo.required' => 'Seleccioná un ciclo lectivo.',
            'dia.required'              => 'Seleccioná un día.',
            'hora_inicio.required'      => 'La hora de inicio es obligatoria.',
            'hora_inicio.after_or_equal'=> 'La hora de inicio no puede ser antes de las 6:00 AM.',
            'hora_inicio.before_or_equal'=> 'La hora de inicio no puede ser después de las 8:30 PM.',
            'hora_fin.after'            => 'La hora de fin debe ser posterior a la hora de inicio.',
            'hora_fin.before_or_equal'  => 'La hora de fin no puede ser después de las 8:30 PM.',
            'jornada.required'          => 'Seleccioná una jornada.',
        ];
    }
}