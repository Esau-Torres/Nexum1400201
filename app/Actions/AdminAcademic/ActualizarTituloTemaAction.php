<?php

namespace App\Actions\AdminAcademic;

use App\Models\Academico\ProcesoGraduacion;
use App\Rules\Graduacion\PuedeDefinirTituloRule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ActualizarTituloTemaAction
{
    public function execute(
        ProcesoGraduacion $proceso,
        string $nuevoTitulo,
        $usuario
    ): ProcesoGraduacion {
        if (!$proceso->grupo) {
            throw ValidationException::withMessages([
                'titulo' => 'Este proceso no tiene un grupo asociado para modificar el título.',
            ]);
        }

        $integrantes = $proceso->grupo->procesos()->count();

        $validator = Validator::make(
            ['titulo' => $nuevoTitulo],
            ['titulo' => [new PuedeDefinirTituloRule($proceso->alumno, $integrantes)]]
        );

        if ($validator->fails()) {
            throw ValidationException::withMessages([
                'titulo' => $validator->errors()->all(),
            ]);
        }

        return DB::transaction(function () use ($proceso, $nuevoTitulo) {
            $grupo = $proceso->grupo;
            $grupo->titulo_tema = trim($nuevoTitulo);
            $grupo->save();

            return $proceso->fresh();
        });
    }
}