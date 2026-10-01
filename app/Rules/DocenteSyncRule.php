<?php

namespace App\Rules;

use App\Models\Docente\Docentes;
use App\Models\Users\User;

class DocenteSyncRule
{
    /**
     * Acción recomendada según el estado actual del usuario y su perfil docente.
     */
    public const ACTION_CREATE      = 'create';
    public const ACTION_REACTIVATE  = 'reactivate';
    public const ACTION_DEACTIVATE  = 'deactivate';
    public const ACTION_NOOP        = 'noop';

    /**
     * Analiza el contexto y retorna la acción que debe ejecutarse.
     *
     * @param  User  $user         Usuario afectado.
     * @param  bool  $hasDocente   
     * @return array{
     *     action: string,
     *     docente: ?Docentes,
     *     reason: string
     * }
     */
    public function evaluate(User $user, bool $hasDocente): array
    {
        $docente = Docentes::where('id_usuario', $user->id)->first();

        // Caso 1: se asigna DOCENTE y NO existe perfil → crear
        if ($hasDocente && !$docente) {
            return [
                'action'  => self::ACTION_CREATE,
                'docente' => null,
                'reason'  => 'Rol DOCENTE asignado sin perfil previo.',
            ];
        }

        // Caso 2: se asigna DOCENTE y existe perfil → reactivar si está inactivo
        if ($hasDocente && $docente) {
            $estaInactivo = strtoupper($docente->estado) !== 'ACTIVO';

            return [
                'action'  => $estaInactivo ? self::ACTION_REACTIVATE : self::ACTION_NOOP,
                'docente' => $docente,
                'reason'  => $estaInactivo
                    ? 'Perfil docente inactivo que debe reactivarse.'
                    : 'Perfil docente ya activo, sin cambios.',
            ];
        }

        // Caso 3: se revoca DOCENTE y existe perfil activo → inactivar
        if (!$hasDocente && $docente && strtoupper($docente->estado) === 'ACTIVO') {
            return [
                'action'  => self::ACTION_DEACTIVATE,
                'docente' => $docente,
                'reason'  => 'Rol DOCENTE revocado, perfil docente activo debe inactivarse.',
            ];
        }

        // Caso 4: sin rol, sin perfil, o perfil ya inactivo → nada que hacer
        return [
            'action'  => self::ACTION_NOOP,
            'docente' => $docente,
            'reason'  => 'No hay cambios requeridos en el perfil docente.',
        ];
    }
}