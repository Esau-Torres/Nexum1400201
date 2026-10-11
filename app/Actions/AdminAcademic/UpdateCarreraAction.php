<?php

namespace App\Actions\AdminAcademic;

use App\Models\Estudiante\Carreras;
use App\Rules\ActualizarCarreraRules;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateCarreraAction
{
    /**
     * @throws ValidationException
     */
    public function execute(Carreras $carrera, array $data): Carreras
    {
        $validator = ActualizarCarreraRules::validate($carrera, $data);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return DB::transaction(function () use ($carrera, $data) {
            $carrera->update([
                'id_facultad'        => $data['id_facultad'],
                'id_nivel_academico' => $data['id_nivel_academico'],
                'nombre'             => trim($data['nombre']),
                'duracion_ciclos'    => (int) $data['duracion_ciclos'],
            ]);
            // Invalidar cache 
            Carreras::olvidarCacheActivas();
            return $carrera->refresh();
        });
    }
}