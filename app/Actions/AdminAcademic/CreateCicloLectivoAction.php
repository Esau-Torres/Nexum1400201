<?php

namespace App\Actions\AdminAcademic;

use App\Enums\EstadoCiclo;
use App\Enums\TipoCiclo;
use App\Models\Academico\CicloLectivo;
use App\Rules\CrearCicloLectivoRules;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class CreateCicloLectivoAction
{
    /**
     * Crea un nuevo ciclo lectivo.
     * - Solo si todos los ciclos previos están CERRADOS.
     *
     * @throws ValidationException
     */
    public function execute(array $data): CicloLectivo
    {
        $validator = CrearCicloLectivoRules::validate($data);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return DB::transaction(function () use ($data) {
            $tipo = TipoCiclo::from($data['numero_ciclo']);

            $nombreCiclo = !empty($data['nombre_ciclo'])
                ? $data['nombre_ciclo']
                : "CICLO {$tipo->value}-{$data['anio']}";

            return CicloLectivo::create([
                'anio'         => $data['anio'],
                'numero_ciclo' => $tipo->value,
                'nombre_ciclo' => $nombreCiclo,
                'fecha_inicio' => $data['fecha_inicio'],
                'fecha_fin'    => $data['fecha_fin'],
                'estado'       => EstadoCiclo::CONFIGURACION->value,
            ]);
        });
    }
}