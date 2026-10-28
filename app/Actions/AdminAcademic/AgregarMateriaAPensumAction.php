<?php

namespace App\Actions\AdminAcademic;

use App\Models\Academico\Pensum;
use App\Models\Academico\PensumMateria;
use App\Rules\AgregarMateriaAPensumRules;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AgregarMateriaAPensumAction
{
    /**
     * @throws ValidationException
     */
    public function execute(Pensum $pensum, array $data): PensumMateria
    {
        $validator = AgregarMateriaAPensumRules::validate($pensum, $data);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return DB::transaction(function () use ($pensum, $data) {
            return PensumMateria::create([
                'id_pensum'      => $pensum->pensum_id,
                'id_materia'     => $data['id_materia'],
                'ciclo_sugerido' => $data['ciclo_sugerido'],
                'es_obligatoria' => $data['es_obligatoria'] ?? true,
            ]);
        });
    }
}