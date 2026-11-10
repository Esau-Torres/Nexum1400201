<?php

namespace App\Actions\AdminAcademic;

use App\Models\Academico\Materias;
use App\Rules\ActualizarMateriaRules;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateMateriaAction
{
    /**
     * @throws ValidationException
     */
    public function execute(Materias $materia, array $data): Materias
    {
        $validator = ActualizarMateriaRules::validate($materia, $data);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return DB::transaction(function () use ($materia, $data) {
            $materia->update([
                'codigo_materia'       => strtoupper(trim($data['codigo_materia'])),
                'nombre'               => trim($data['nombre']),
                'unidades_valorativas' => $data['unidades_valorativas'],
                'estado'               => $data['estado'],
            ]);

            return $materia->refresh();
        });
    }
}