<?php

namespace App\Actions\AdminAcademic;

use App\Models\Estudiante\Facultades;
use App\Rules\EliminarFacultadRules;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeleteFacultadAction
{
    /**
     * @throws ValidationException
     */
    public function execute(Facultades $facultad): void
    {
        $validator = EliminarFacultadRules::validate($facultad);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        DB::transaction(fn () => $facultad->delete());
    }
}