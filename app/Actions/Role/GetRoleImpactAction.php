<?php

namespace App\Actions\Role;

use App\Models\Users\Role;
use App\Models\Users\User;

class GetRoleImpactAction
{
    /**
     * Retorna el impacto de desactivar un rol (para previsualización en el modal).
     */
    public function execute(int $roleId): array
    {
        $role = Role::findOrFail($roleId);

        $affectedUsers = User::whereHas('roles', function ($q) use ($roleId) {
            $q->where('roles.rol_id', $roleId);
        })->get(['id', 'name', 'email']);

        $orphanList = $affectedUsers->filter(function ($user) use ($roleId) {
            return !$user->roles()
                ->where('roles.estado', true)
                ->where('roles.rol_id', '!=', $roleId)
                ->exists();
        })->values();

        return [
            'role_id'         => $role->rol_id,
            'role_name'       => $role->nombre,
            'is_critical'     => in_array($role->nombre, [
                Role::SUPER_ADMIN,
                Role::ADMIN_ACADEMICO,
                Role::DIRECTIVO,
            ]),
            'affected_users'  => $affectedUsers->count(),
            'orphan_users'    => $orphanList->count(),
            'users'           => $affectedUsers->map(fn($u) => [
                'id'     => $u->id,
                'name'   => $u->name,
                'email'  => $u->email,
                'orphan' => $orphanList->contains('id', $u->id),
            ])->values()->all(),
        ];
    }
}