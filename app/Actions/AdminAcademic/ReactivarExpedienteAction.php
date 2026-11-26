<?php

namespace App\Actions\AdminAcademic;

use App\Models\Estudiante\Alumnos;
use RuntimeException;

class ReactivarExpedienteAction
{
    /**
     * @deprecated La reactivación de expedientes no está habilitada en esta versión.
     *             Si el estudiante quiere volver a una carrera que ya cursó,
     *             debe solicitar un nuevo cambio de carrera desde su expediente actual.
     */
    public function execute(Alumnos $expedienteCongelado): Alumnos
    {
        throw new RuntimeException(
            'La reactivación de expedientes no está disponible. '
            . 'Si el estudiante desea volver a una carrera anterior, '
            . 'debe procesarse un nuevo cambio de carrera.'
        );
    }
}