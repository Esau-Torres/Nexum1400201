<?php

namespace App\Rules;

use App\Enums\EstadoMateria;
use App\Models\Academico\Materias;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ActualizarMateriaRules
{
    public static function validate(Materias $materia, array $data): ValidatorContract
    {
        $validator = Validator::make($data, [
            'codigo_materia'       => [
                'required', 'string', 'max:20',
                'regex:/^[A-Z0-9\-]{3,20}$/',
            ],
            'nombre'               => ['required', 'string', 'min:3', 'max:150'],
            'unidades_valorativas' => ['required', 'integer', 'min:1', 'max:20'],
            'estado'               => ['required', Rule::enum(EstadoMateria::class)],
        ], [
            'codigo_materia.regex' => 'El código solo admite mayúsculas, números y guiones (3-20 caracteres).',
        ]);

        $validator->after(function ($v) use ($materia, $data) {
            // -- Unicidad de código (excluyendo esta misma materia) --
            $existeCodigo = Materias::where('materia_id', '!=', $materia->materia_id)
                ->whereRaw('UPPER(codigo_materia) = ?', [strtoupper($data['codigo_materia'] ?? '')])
                ->exists();

            if ($existeCodigo) {
                $v->errors()->add('codigo_materia', 'Ya existe otra materia con ese código.');
            }

            // -- Unicidad de nombre --
            $existeNombre = Materias::where('materia_id', '!=', $materia->materia_id)
                ->whereRaw('UPPER(nombre) = ?', [strtoupper($data['nombre'] ?? '')])
                ->exists();

            if ($existeNombre) {
                $v->errors()->add('nombre', 'Ya existe otra materia con ese nombre.');
            }

            // -- Si tiene secciones activas, no se puede marcar como INACTIVO --
            $nuevoEstado = EstadoMateria::tryFrom($data['estado'] ?? '');
            if ($nuevoEstado === EstadoMateria::INACTIVO) {
                $tieneSecciones = $materia->secciones()
                    ->whereHas('cicloLectivo', fn($q) => $q->whereIn('estado', ['ACTIVO', 'MATRICULA_ABIERTA']))
                    ->exists();

                if ($tieneSecciones) {
                    $v->errors()->add('estado', 'No se puede marcar como INACTIVO: la materia tiene secciones activas en el ciclo actual.');
                }
            }
        });

        return $validator;
    }
}