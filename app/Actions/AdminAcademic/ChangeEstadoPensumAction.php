<?php

namespace App\Actions\AdminAcademic;

use App\Enums\EstadoPensum;
use App\Models\Academico\Pensum;
use App\Rules\CambiarEstadoPensumRules;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\Estudiante\Carreras;

class ChangeEstadoPensumAction
{
    /**
     * @throws ValidationException
     */
    public function execute(Pensum $pensum, EstadoPensum $nuevoEstado): Pensum
    {
        $validator = CambiarEstadoPensumRules::validate($pensum, $nuevoEstado);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return DB::transaction(function () use ($pensum, $nuevoEstado) {
            $pensum->estado = $nuevoEstado;
            $pensum->save();
            // Invalidar cache porque cambió el estado del pensum
            Carreras::olvidarCacheActivas();

            return $pensum->refresh();
        });
    }
}