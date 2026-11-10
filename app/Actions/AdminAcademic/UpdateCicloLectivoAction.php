<?php

namespace App\Actions\AdminAcademic;

use App\Enums\EstadoCiclo;
use App\Models\Academico\CicloLectivo;
use App\Rules\ActualizarCicloLectivoRules;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateCicloLectivoAction
{
    /**
     * Actualiza un ciclo lectivo existente.
     *
     * @throws ValidationException
     */
    public function execute(CicloLectivo $ciclo, array $data): CicloLectivo
    {
        $validator = ActualizarCicloLectivoRules::validate($ciclo, $data);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return DB::transaction(function () use ($ciclo, $data) {
            $ciclo->update([
                'nombre_ciclo' => $data['nombre_ciclo'],
                'fecha_inicio' => $data['fecha_inicio'],
                'fecha_fin'    => $data['fecha_fin'],
                'numero_ciclo' => $data['numero_ciclo'],
                'estado'       => $data['estado'],
            ]);

            return $ciclo->refresh();
        });
    }
}