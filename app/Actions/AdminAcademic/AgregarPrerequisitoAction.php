<?php

namespace App\Actions\AdminAcademic;

use App\Models\Academico\PensumMateria;
use App\Models\Academico\Prerequisito;
use App\Rules\AgregarPrerequisitoRules;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AgregarPrerequisitoAction
{
    /**
     * @throws ValidationException
     */
    public function execute(PensumMateria $pensumMateria, array $data): Prerequisito
    {
        $validator = AgregarPrerequisitoRules::validate($pensumMateria, $data);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return DB::transaction(function () use ($pensumMateria, $data) {
            return Prerequisito::create([
                'id_pensum_materia'     => $pensumMateria->pensum_materia_id,
                'id_materia_requisito'  => $data['id_materia_requisito'],
                'uv_minimas_requeridas' => $data['uv_minimas_requeridas'] ?? 0,
            ]);
        });
    }
}