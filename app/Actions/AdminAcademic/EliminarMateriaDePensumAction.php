<?php

namespace App\Actions\AdminAcademic;

use App\Enums\EstadoPensum;
use App\Models\Academico\PensumMateria;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;

class EliminarMateriaDePensumAction
{
    /**
     * @throws ValidationException
     */
    public function execute(PensumMateria $pensumMateria): void
    {
        $validator = Validator::make([], []);

        $validator->after(function ($v) use ($pensumMateria) {
            if ($pensumMateria->pensum->estado !== EstadoPensum::MODIFICADO) {
                $v->errors()->add('estado', 'Solo se pueden eliminar materias de pensums en MODIFICADO.');
            }
        });

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        DB::transaction(function () use ($pensumMateria) {
            // Los prerequisitos se eliminan por ON DELETE CASCADE
            $pensumMateria->delete();
        });
    }
}