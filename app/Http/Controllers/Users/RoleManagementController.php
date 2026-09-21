<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Actions\Role\UpdateRoleStatusAction;
use App\Actions\Role\AssignRolesToUserAction;
use App\Actions\Role\GetRoleImpactAction;
use App\Rules\UpdateRoleStatusRule;
use App\Rules\AssignRolesToUserRule;
use App\Models\Users\Role;
use App\Models\Users\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Exception;

class RoleManagementController extends Controller
{
    /**
     * Vista principal de gestión de roles.
     */
    // app/Http/Controllers/Users/RoleManagementController.php

    public function index(): View
    {
        $roles = Role::query()
            ->where('nombre', '!=', Role::ESTUDIANTE)
            ->orderBy('rol_id', 'asc')
            ->get();

        $usuarios = User::query()
            ->with(['roles', 'regionalactivo'])
            ->whereDoesntHave('roles', fn($q) => $q->where('nombre', Role::ESTUDIANTE))
            ->orderBy('id', 'desc')
            ->get();

        return view('superadmin.manageroles', compact('roles', 'usuarios'));
    }

    /**
     * Endpoint AJAX: previsualiza impacto de desactivar un rol.
     */
    public function impact(int $roleId, GetRoleImpactAction $action): JsonResponse
    {
        try {
            return response()->json($action->execute($roleId));
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    /**
     * Actualiza el estado de un rol.
     */
    public function updateStatus(
        UpdateRoleStatusRule $request,
        UpdateRoleStatusAction $action
    ): RedirectResponse {
        try {
            $result = $action->execute(
                (int) $request->input('role_id'),
                (bool) $request->input('estado')
            );

            $roleName = $result['role']->nombre;

            if ($result['new_status'] === true) {
                return back()->with(
                    'success',
                    "Rol {$roleName} reactivado correctamente. Los usuarios con este rol recuperan sus privilegios."
                );
            }

            // Desactivación
            $msg = "Rol {$roleName} desactivado. {$result['affected_users']} usuario(s) afectado(s).";

            if ($result['orphan_users'] > 0) {
                return back()
                    ->with('warning', $msg)
                    ->with('orphan_users', $result['orphan_list']);
            }

            return back()->with('success', $msg);

        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Sincroniza los roles de un usuario.
     */
    public function assignRoles(
        AssignRolesToUserRule $request,
        AssignRolesToUserAction $action
    ): RedirectResponse {
        try {
            $result = $action->execute(
                (int) $request->input('user_id'),
                $request->input('roles', [])
            );

            $user = User::find($request->input('user_id'));

            $parts = [];
            if (!empty($result['added'])) {
                $parts[] = 'asignados: ' . implode(', ', $result['added']);
            }
            if (!empty($result['removed'])) {
                $parts[] = 'revocados: ' . implode(', ', $result['removed']);
            }

            $detail = empty($parts) ? 'sin cambios' : implode(' | ', $parts);

            $docenteMsg = match ($result['docente']['action'] ?? 'noop') {
                'create'     => ' Se creó su perfil docente (ACTIVO).',
                'reactivate' => ' Se reactivó su perfil docente.',
                'deactivate' => ' Su perfil docente quedó INACTIVO.',
                default      => '',
            };

            return back()->with(
                'success',
                "Roles de {$user->name} actualizados correctamente: ({$detail})."
            );

        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}