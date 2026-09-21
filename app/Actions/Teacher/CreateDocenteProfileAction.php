<?php  

namespace App\Actions\Teacher;

use App\Models\Docente\Docentes;
use App\Models\Users\User;
use App\Rules\GeneradorCodigoDocente;

class CreateDocenteProfileAction
{
    public function execute(User $user): Docentes
    {
        $codigoEmpleado = GeneradorCodigoDocente::generar($user);
        $correoInstitucional = strtolower(trim($codigoEmpleado)) . '@uma.edu.sv';

        return Docentes::updateOrCreate(
            ['id_usuario' => $user->id],
            [
                'codigo_empleado'      => $codigoEmpleado,
                'correo_institucional' => $correoInstitucional,
                'estado'               => 'ACTIVO',
            ]
        );
    }
}