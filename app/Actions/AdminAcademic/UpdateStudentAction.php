<?php

namespace App\Actions\AdminAcademic;

use App\Models\Estudiante\Alumnos;
use App\Models\Users\User;
use Illuminate\Support\Facades\DB;

class UpdateStudentAction
{
    public function execute(Alumnos $alumno, array $data, User $admin): User
    {
        if (!$admin->hasRole('ADMIN_ACADEMICO')) {
            throw new RuntimeException('No tiene permisos para editar alumnos.');
        }
        
        $camposPermitidos = [
            'name',
            'email',
            'id_tipo_documento',
            'documento_identidad',
            'fecha_nacimiento',
            'genero',
            'estado_civil',
            'celular',
            'direccion',
            'id_regional_activo',
        ];

        $datos = array_intersect_key($data, array_flip($camposPermitidos));

        $alumno->usuario->update($datos);

        return $alumno->usuario->fresh();
    }
}