<?php

namespace App\Actions\AdminAcademic;

use App\Models\Users\User;
use App\Models\Estudiante\AlumnoDocumentos;
use App\Actions\User\CreateUserAccountAction;
use App\Actions\User\AssignUserRoleAction;
use App\Actions\Student\CreateStudentProfileAction;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CreateMassiveStudentAction
{
    public function __construct(
        protected CreateUserAccountAction     $createUserAction,
        protected AssignUserRoleAction        $assignRoleAction,
        protected CreateStudentProfileAction  $createStudentAction,
    ) {}

    /**
     * Crea un estudiante activo sin documentos (quedan PENDIENTES).
     * Devuelve [User, Alumnos].
     */
    public function execute(array $row, User $admin): array
    {
        if (!$admin->hasRole('ADMIN_ACADEMICO')) {
            throw new RuntimeException('No tiene permisos para crear estudiantes masivamente.');
        }

        return DB::transaction(function () use ($row, $admin) {

            // 1. Crear usuario activo
            $user = $this->createUserAction->execute([
                ...$row,
                'estado' => 1,
            ]);

            // 2. Rol ESTUDIANTE (id 8 según tu código actual)
            $this->assignRoleAction->execute(
                user: $user,
                roleId: 8,
                assignedBy: $admin->id
            );

            // 3. Perfil de alumno activo + password temporal = código
            $alumno = $this->createStudentAction->execute(
                user: $user,
                carreraId: $row['id_carrera'],
                estadoCarrera: 'ACTIVO',
                generarPasswordTemporal: true
            );

            // 4. Registro de documentos vacío → el estudiante los sube luego
            AlumnoDocumentos::create([
                'id_alumno'           => $alumno->alumno_id,
                'titulo_bachillerato' => null,
                'partida_nacimiento'  => null,
                'fotografia_personal' => null,
                'constancia_paes'     => null,
                'estado_documentos'   => 'PENDIENTE',
                'observaciones'       => 'Creado por carga masiva. Pendiente de subir documentos.',
                'revisado_por'        => null,
            ]);

            return [$user, $alumno];
        });
    }
}