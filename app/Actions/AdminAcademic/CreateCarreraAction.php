<?php

namespace App\Actions\AdminAcademic;

use App\Models\Estudiante\Carreras;
use App\Rules\CrearCarreraRules;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateCarreraAction
{
    /**
     * @throws ValidationException
     */
    public function execute(array $data): Carreras
    {
        $validator = CrearCarreraRules::validate($data);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return DB::transaction(function () use ($data) {
            //  Invalidar cache 
            Carreras::olvidarCacheActivas();
            return Carreras::create([
                'id_facultad'         => $data['id_facultad'],
                'id_nivel_academico'  => $data['id_nivel_academico'],
                'nombre'              => trim($data['nombre']),
                'duracion_ciclos'     => (int) $data['duracion_ciclos'],
                'nivel_academico'     => 'GRADO', // legacy, se mantiene por compatibilidad
            ]);
        });
    }
}