<?php

namespace App\Actions\AdminAcademic;

use App\Enums\EstadoPensum;
use App\Models\Academico\Pensum;
use App\Rules\CrearPensumRules;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\Estudiante\Carreras;

class CreatePensumAction
{
    /**
     * @throws ValidationException
     */
    public function execute(array $data): Pensum
    {
        $validator = CrearPensumRules::validate($data);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return DB::transaction(function () use ($data) {
            // Invalidar cache porque cambió el estado del pensum
            Carreras::olvidarCacheActivas();
            return Pensum::create([
                'id_carrera'    => $data['id_carrera'],
                'nombre'        => trim($data['nombre']),
                'anio_vigencia' => $data['anio_vigencia'],
                'estado'        => EstadoPensum::MODIFICADO->value,
            ]);
        });
    }
}