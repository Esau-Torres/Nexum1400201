<?php

namespace App\Actions\Student;

use App\Enums\EstadoSituacion;
use App\Models\Estudiante\SituacionUsuario;
use Illuminate\Validation\ValidationException;

class EliminarSituacionAction
{
    public function execute(SituacionUsuario $situacion, int $idUsuarioAutenticado): void
    {
        // 1. Solo el dueño puede eliminarla
        if ((int) $situacion->id_usuario !== (int) $idUsuarioAutenticado) {
            throw ValidationException::withMessages([
                'situacion' => 'No tienes permiso para eliminar esta situación.',
            ]);
        }

        // 2. Solo se puede eliminar si está en estado PENDIENTE
        if ($situacion->estado !== EstadoSituacion::PENDIENTE) {
            throw ValidationException::withMessages([
                'situacion' => 'Solo puedes eliminar situaciones en estado PENDIENTE. Esta ya está siendo gestionada.',
            ]);
        }

        $situacion->delete();
    }
}