<?php

namespace App\Actions\AdminAcademic;

use App\Models\Estudiante\Facultades;
use App\Rules\CrearFacultadRules;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateFacultadAction
{
    /**
     * @throws ValidationException
     */
    public function execute(array $data): Facultades
    {
        $validator = CrearFacultadRules::validate($data);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return DB::transaction(function () use ($data) {
            return Facultades::create([
                'nombre' => trim($data['nombre']),
            ]);
        });
    }
}