<?php

namespace App\Actions\Role;

use App\Models\Users\Role;
use App\Models\Users\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class UpdateRoleStatusAction
{
    /**
     * Actualiza el estado de un rol aplicando la política de soft-disable.
     *
     * @return array{
     *     role: Role,
     *     previous_status: bool,
     *     new_status: bool,
     *     affected_users: int,
     *     orphan_users: int,
     *     orphan_list: array
     * }
     * @throws Exception
     */
    public function execute(int $roleId, bool $newStatus): array
    {
        try {
            return DB::transaction(function () use ($roleId, $newStatus) {
                $role = Role::lockForUpdate()->findOrFail($roleId);
                $previousStatus = (bool) $role->estado;

                // Idempotencia: si ya está en el estado deseado, no hacer nada
                if ($previousStatus === $newStatus) {
                    throw new Exception(
                        $newStatus
                            ? 'El rol ya se encuentra activo.'
                            : 'El rol ya se encuentra inactivo.'
                    );
                }

                // Protección: no permitir desactivar SUPER_ADMIN
                if ($role->nombre === Role::SUPER_ADMIN && $newStatus === false) {
                    throw new Exception(
                        'El rol SUPER_ADMIN es crítico para el sistema y no puede ser desactivado.'
                    );
                }

                $role->update(['estado' => $newStatus]);

                // Solo calculamos impacto si estamos desactivando
                $affectedUsers = 0;
                $orphanUsers   = 0;
                $orphanList    = [];

                if ($newStatus === false) {
                    $impact = $this->calculateImpact($roleId);
                    $affectedUsers = $impact['affected_users'];
                    $orphanUsers   = $impact['orphan_users'];
                    $orphanList    = $impact['orphan_list'];
                }

                Log::info('[NEXUM_RBAC] Estado de rol actualizado', [
                    'role_id'         => $roleId,
                    'role_name'       => $role->nombre,
                    'previous_status' => $previousStatus,
                    'new_status'      => $newStatus,
                    'affected_users'  => $affectedUsers,
                    'orphan_users'    => $orphanUsers,
                    'actor_id'        => auth()->id(),
                ]);

                return [
                    'role'            => $role->fresh(),
                    'previous_status' => $previousStatus,
                    'new_status'      => $newStatus,
                    'affected_users'  => $affectedUsers,
                    'orphan_users'    => $orphanUsers,
                    'orphan_list'     => $orphanList,
                ];
            });
        } catch (Exception $e) {
            Log::error('[NEXUM_RBAC] Error al actualizar estado de rol', [
                'role_id' => $roleId,
                'error'   => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Calcula el impacto de desactivar un rol.
     */
    private function calculateImpact(int $roleId): array
    {
        // Usuarios que tienen asignado este rol
        $affectedUsers = User::whereHas('roles', function ($q) use ($roleId) {
            $q->where('roles.rol_id', $roleId);
        })->get(['id', 'name', 'email']);

        // Usuarios que quedarían sin NINGÚN rol activo tras la desactivación
        $orphanList = $affectedUsers->filter(function ($user) use ($roleId) {
            return !$user->roles()
                ->where('roles.estado', true)
                ->where('roles.rol_id', '!=', $roleId)
                ->exists();
        })->values()->all();

        return [
            'affected_users' => $affectedUsers->count(),
            'orphan_users'   => count($orphanList),
            'orphan_list'    => $orphanList,
        ];
    }
}