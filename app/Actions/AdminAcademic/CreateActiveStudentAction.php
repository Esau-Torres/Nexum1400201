<?php

namespace App\Actions\AdminAcademic;

use App\Models\Users\User;
use App\Actions\User\CreateUserAccountAction;
use App\Actions\User\AssignUserRoleAction;
use App\Actions\Student\CreateStudentProfileAction;
use App\Actions\Student\UploadStudentDocumentsAction;
use App\Actions\AdminAcademic\AssignStudentBenefitAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CreateActiveStudentAction
{
    /**
     * Roles permitidos para creación directa por ADMIN_ACADEMICO.
     * Solo ESTUDIANTE. Si querés escalar, agregá acá y validá permisos.
     */
    private const ROLES_PERMITIDOS = ['ESTUDIANTE'];

    public function __construct(
        protected CreateUserAccountAction $createUserAction,
        protected AssignUserRoleAction $assignRoleAction,
        protected CreateStudentProfileAction $createStudentAction,
        protected UploadStudentDocumentsAction $uploadDocsAction,
        protected AssignStudentBenefitAction $assignBenefitAction, 
    ) {}

    public function execute(array $input, User $admin, Request $request): User
    {
        // 1. Validación defensiva del rol solicitado
        $this->validarRolSolicitado($input['rol'] ?? 'ESTUDIANTE');

        // 2. Validación defensiva del admin
        if (!$admin->hasRole('ADMIN_ACADEMICO')) {
            throw new RuntimeException('No tiene permisos para crear estudiantes directamente.');
        }

        return DB::transaction(function () use ($input, $admin, $request) {

            // 3. Crear cuenta de usuario YA ACTIVA
            $user = $this->createUserAction->execute([
                ...$input,
                'estado' => 1,
            ]);

            // 4. Asignar rol ESTUDIANTE registrando quién lo asignó
            $this->assignRoleAction->execute(
                user: $user,
                roleId: 8, // ESTUDIANTE
                assignedBy: $admin->id
            );

            // 5. Crear perfil de alumno con estado ACTIVO
            $alumno = $this->createStudentAction->execute(
                user: $user,
                carreraId: $input['id_carrera'],
                estadoCarrera: 'ACTIVO',
                generarPasswordTemporal: true 
            );

            // 6. Subir documentos marcándolos como aprobados por el admin
            $this->uploadDocsAction->execute(
                alumno: $alumno,
                request: $request,
                estadoDocumentos: 'APROBADO',
                revisadoPor: $admin->id
            );

            // 7. Beneficio / arancel por ciclo lectivo (opcional)
            if (!empty($input['id_ciclo_lectivo']) && !empty($input['tipo_beneficio'])) {
                $this->assignBenefitAction->execute(
                    alumno: $alumno,
                    data: [
                        'id_ciclo_lectivo'       => $input['id_ciclo_lectivo'],
                        'tipo_beneficio'         => $input['tipo_beneficio'],
                        'nombre_convenio'        => $input['nombre_convenio'] ?? 'Sin convenio',
                        'porcentaje_estudiante'  => $input['porcentaje_estudiante']  ?? null,
                        'porcentaje_universidad' => $input['porcentaje_universidad'] ?? null,
                        'monto_fijo_cuota'       => $input['monto_fijo_cuota']       ?? null,
                        'resolucion_academica'   => $input['resolucion_academica']   ?? null,
                    ],
                    asignadoPor: $admin,
                );
            }

            return $user;
        });
    }

    private function validarRolSolicitado(string $rol): void
    {
        if (!in_array($rol, self::ROLES_PERMITIDOS, true)) {
            throw new RuntimeException(
                "El rol '{$rol}' no puede ser creado desde este módulo."
            );
        }
    }
}