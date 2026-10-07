<?php

namespace App\Actions\AdminAcademic;

use App\Enums\EstadoPensum;
use App\Models\Academico\Prerequisito;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class EliminarPrerequisitoAction
{
    /**
     * @throws ValidationException
     */
    public function execute(Prerequisito $prerequisito): void
    {
        $validator = Validator::make([], []);

        $validator->after(function ($v) use ($prerequisito) {
            if ($prerequisito->pensumMateria?->pensum?->estado !== EstadoPensum::MODIFICADO) {
                $v->errors()->add('estado', 'Solo se pueden eliminar prerequisitos de pensums en MODIFICADO.');
            }
        });

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        DB::transaction(fn () => $prerequisito->delete());
    }
}