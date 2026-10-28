<?php

namespace App\Actions\AdminAcademic;

use App\Models\Academico\Materias;
use App\Rules\CrearMateriaRules;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateMateriaAction
{
    /**
     * @throws ValidationException
     */
    public function execute(array $data): Materias
    {
        $validator = CrearMateriaRules::validate($data);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return DB::transaction(function () use ($data) {
            return Materias::create([
                'codigo_materia'       => strtoupper(trim($data['codigo_materia'])),
                'nombre'               => trim($data['nombre']),
                'unidades_valorativas' => $data['unidades_valorativas'],
                'estado'               => $data['estado'],
            ]);
        });
    }
}