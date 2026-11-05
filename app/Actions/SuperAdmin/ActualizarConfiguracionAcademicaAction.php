<?php

namespace App\Actions\SuperAdmin;

use App\Models\Academico\ConfiguracionAcademica;
use Illuminate\Support\Facades\DB;

class ActualizarConfiguracionAcademicaAction
{
    public function execute(int $idCarrera, array $datos): ConfiguracionAcademica
    {
        return DB::transaction(function () use ($idCarrera, $datos) {
            $config = ConfiguracionAcademica::firstOrNew(['id_carrera' => $idCarrera]);

            // Si es nueva, aplicar valores por defecto faltantes
            if (!$config->exists) {
                $config->fill(ConfiguracionAcademica::paraCarrera($idCarrera)->toArray());
            }

            $config->fill([
                'nota_minima_aprobatoria'      => $datos['nota_minima_aprobatoria'],
                'max_reprobaciones_materia'    => $datos['max_reprobaciones_materia'],
                'cum_minimo_materia_extra'     => $datos['cum_minimo_materia_extra'],
                'uv_requeridas_horas_sociales' => $datos['uv_requeridas_horas_sociales'],
                'uv_totales_egreso'            => $datos['uv_totales_egreso'],
            ]);

            $config->save();

            return $config;
        });
    }
}