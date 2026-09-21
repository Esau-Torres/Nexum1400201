<?php

namespace App\Actions\Role;

use App\Models\Users\Role;
use App\Models\Users\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Actions\Teacher\SyncDocenteProfileAction;
use Exception;

class AssignRolesToUserAction
{
     public function __construct(
        private readonly SyncDocenteProfileAction $syncDocente,
    ) {}
    /**
     * Sincroniza los roles de un usuario.
     *
     * Reglas de negocio aplicadas:
     *  - No se pueden asignar roles inactivos (estado = false).
     *  - El rol ESTUDIANTE no puede ser asignado manualmente.
     *  - Todos los roles enviados deben existir en el catálogo.
     *  - Si el actor se edita a sí mismo y tenía SUPER_ADMIN, se re-agrega
     *    automáticamente (no se puede quitar el propio SUPER_ADMIN en sesión).
     *  - Solo se registran en el pivote los NUEVOS roles (auditoría de asignado_por
     *    y fecha_asignacion).
     *
     * @param  int        $userId   ID del usuario a modificar.
     * @param  array<int> $roleIds  IDs de roles seleccionados.
     * @return array{added: array<int, string>, removed: array<int, string>, unchanged: array<int, string>}
     * @throws Exception
     */
    public function execute(int $userId, array $roleIds): array
    {
        try {
            return DB::transaction(function () use ($userId, $roleIds) {

                $roleIds = array_values(array_unique(array_map('intval', array_filter(
                    $roleIds,
                    fn($id) => $id !== null && $id !== ''
                ))));

                $user = User::lockForUpdate()->findOrFail($userId);

                $roles = Role::whereIn('rol_id', $roleIds)->get();
                $currentRoleIds = $user->roles()->pluck('roles.rol_id')->map(fn($id) => (int) $id)->toArray();
                //  Roles inactivos
                $inactiveRoles = $roles->where('estado', false);
                $nuevosInactivos = $inactiveRoles->filter(fn($r) => !in_array((int) $r->rol_id, $currentRoleIds, true));


                if ($nuevosInactivos->isNotEmpty()) {
                    throw new Exception(
                        'No se pueden asignar roles inactivos: ' .
                        $nuevosInactivos->pluck('nombre')->implode(', ')
                    );
                }

                // 3.2 Roles inexistentes
                if ($roles->count() !== count($roleIds)) {
                    throw new Exception('Uno o más roles seleccionados no existen en el catálogo.');
                }

                // 3.3 Bloquear ESTUDIANTE
                $studentRoleId = (int) Role::where('nombre', Role::ESTUDIANTE)->value('rol_id');
                if ($studentRoleId && in_array($studentRoleId, $roleIds, true)) {
                    throw new Exception('El rol ESTUDIANTE no puede ser asignado manualmente.');
                }

                /*
                 * 4. Protección: no quitarse SUPER_ADMIN a sí mismo
                 */
                $superAdminRoleId = (int) Role::where('nombre', Role::SUPER_ADMIN)->value('rol_id');

                if (auth()->id() === $userId && $superAdminRoleId) {
                    $hadSuperAdmin = $user->roles()
                        ->where('roles.rol_id', $superAdminRoleId)
                        ->exists();

                    if ($hadSuperAdmin && !in_array($superAdminRoleId, $roleIds, true)) {
                        $roleIds[] = $superAdminRoleId;
                        $roleIds   = array_values(array_unique($roleIds));
                    }
                }

                /*
                 * 5. Calcular diferencias (comparación estricta por tipo)
                 */
                $currentRoleIds = $user->roles()
                    ->pluck('roles.rol_id')
                    ->map(fn($id) => (int) $id)
                    ->toArray();

                $toAdd = array_values(array_filter(
                    $roleIds,
                    fn($id) => !in_array($id, $currentRoleIds, true)
                ));

                $toRemove = array_values(array_filter(
                    $currentRoleIds,
                    fn($id) => !in_array($id, $roleIds, true)
                ));

                $unchanged = array_values(array_intersect($roleIds, $currentRoleIds));

                /*
                 * 6. Aplicar cambios
                 */

                // 6.1 Attach de roles nuevos con auditoría
                if (!empty($toAdd)) {
                    $pivotData = [];
                    foreach ($toAdd as $roleId) {
                        $pivotData[(int) $roleId] = [
                            'fecha_asignacion' => now(),
                            'asignado_por'     => auth()->id(),
                        ];
                    }
                    $user->roles()->attach($pivotData);
                }

                // 6.2 Detach de roles removidos
                if (!empty($toRemove)) {
                    $user->roles()->detach($toRemove);
                }

                $docenteRoleId = (int) Role::where('nombre', Role::DOCENTE)->value('rol_id');
                $hasDocente    = $docenteRoleId && in_array($docenteRoleId, $roleIds, true);
                $docenteResult = $this->syncDocente->execute($user, $hasDocente);

                /* --------------------------------------------------------------
                 * 7. Auditoría
                 * -------------------------------------------------------------- */
                Log::info('[NEXUM_RBAC] Roles de usuario actualizados', [
                    'user_id'   => $userId,
                    'added'     => $toAdd,
                    'removed'   => $toRemove,
                    'unchanged' => $unchanged,
                    'actor_id'  => auth()->id(),
                ]);

                /* --------------------------------------------------------------
                 * 8. Resultado
                 * -------------------------------------------------------------- */
                $assignedRoleNames = Role::whereIn('rol_id', $toAdd)->pluck('nombre')->toArray();
                $revokedRoleNames  = Role::whereIn('rol_id', $toRemove)->pluck('nombre')->toArray();

                return [
                    'added'     => $assignedRoleNames,
                    'removed'   => $revokedRoleNames,
                    'unchanged' => Role::whereIn('rol_id', $unchanged)->pluck('nombre')->toArray(),
                ];
            });
        } catch (Exception $e) {
            Log::error('[NEXUM_RBAC] Error al asignar roles', [
                'user_id' => $userId,
                'error'   => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}