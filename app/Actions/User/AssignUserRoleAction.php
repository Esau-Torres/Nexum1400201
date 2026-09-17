<?php
    namespace App\Actions\User;

    use App\Models\Users\User;
    use Illuminate\Support\Facades\DB;

    class AssignUserRoleAction
    {
        public function execute(User $user, int $roleId): void
        {
            DB::table('usuario_rol')->insert([
                'id_usuario'       => $user->id,
                'id_rol'           => $roleId,
                'fecha_asignacion' => now(),
            ]);
        }
    }