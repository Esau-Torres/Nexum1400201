<?php

namespace App\Actions\AdminAcademic;

use App\Models\Estudiante\Facultades;
use App\Rules\ActualizarFacultadRules;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateFacultadAction
{
    /**
     * @throws ValidationException
     */
    public function execute(Facultades $facultad, array $data): Facultades
    {
        $validator = ActualizarFacultadRules::validate($facultad, $data);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return DB::transaction(function () use ($facultad, $data) {
            $facultad->update(['nombre' => trim($data['nombre'])]);
            return $facultad->refresh();
        });
    }
}