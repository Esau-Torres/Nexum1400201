<?php
    namespace App\Actions\User;

    use App\Models\Users\User;
    use Illuminate\Support\Facades\DB;

    class AssignUserRoleAction
    {
        public function execute(User $user, int $roleId, ?int $assignedBy = null): void
        {
            DB::table('usuario_rol')->upsert(
                [
                    'id_usuario'       => $user->id,
                    'id_rol'           => $roleId,
                    'fecha_asignacion' => now(),
                    'asignado_por'     => $assignedBy,
                ],
                ['id_usuario', 'id_rol'],
                ['fecha_asignacion', 'asignado_por']
            );
        }
    }