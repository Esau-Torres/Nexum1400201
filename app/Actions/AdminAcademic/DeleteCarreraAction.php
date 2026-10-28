<?php

namespace App\Actions\AdminAcademic;

use App\Models\Estudiante\Carreras;
use App\Rules\EliminarCarreraRules;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeleteCarreraAction
{
    /**
     * @throws ValidationException
     */
    public function execute(Carreras $carrera): void
    {
        $validator = EliminarCarreraRules::validate($carrera);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        DB::transaction(fn () => $carrera->delete());
        //  Invalidar cache
        Carreras::olvidarCacheActivas();
    }
}