<?php

namespace App\Services\SuperAdmin;

use App\Models\Academico\ConfiguracionAcademica;

class CalculadoraEstadoMateriaService
{
    /**
     * Determina si un promedio final aprueba o reprueba según la config.
     */
    public function aprobo(float $promedioFinal, int $idCarrera): bool
    {
        $config = ConfiguracionAcademica::paraCarrera($idCarrera);
        return $promedioFinal >= (float) $config->nota_minima_aprobatoria;
    }

    /**
     * Determina si el alumno ha agotado las oportunidades.
     */
    public function agotoOportunidades(int $reprobacionesPrevias, int $idCarrera): bool
    {
        $config = ConfiguracionAcademica::paraCarrera($idCarrera);
        return $reprobacionesPrevias >= (int) $config->max_reprobaciones_materia;
    }

    /**
     * Calcula si el alumno aplica a la excepción de 5ª materia por CUM.
     */
    public function cumpleCumParaMateriaExtra(float $cum, int $idCarrera): bool
    {
        $config = ConfiguracionAcademica::paraCarrera($idCarrera);
        return $cum >= (float) $config->cum_minimo_materia_extra;
    }
}