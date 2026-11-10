<?php

namespace App\Actions\AdminAcademic;

use App\Enums\EstadoCiclo;
use App\Models\Academico\CicloLectivo;
use App\Rules\CambiarEstadoCicloRules;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ChangeCicloStatusAction
{
    /**
     * Cambia el estado de un ciclo lectivo.
     * Permite reapertura si fue cerrado por error (fecha_fin > hoy).
     *
     * @throws ValidationException
     */
    public function execute(CicloLectivo $ciclo, EstadoCiclo $nuevoEstado): CicloLectivo
    {
        $validator = CambiarEstadoCicloRules::validate($ciclo, $nuevoEstado);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return DB::transaction(function () use ($ciclo, $nuevoEstado) {
            $ciclo->estado = $nuevoEstado;
            $ciclo->save();

            return $ciclo->refresh();
        });
    }
}