<?php
namespace App\Actions\Student;

use App\Models\Users\User;
use App\Rules\CodigoEstudiante;
use App\Models\Estudiante\Alumnos;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CreateStudentProfileAction
{
    public function execute(User $user, int $carreraId): Alumnos
    {
       return DB::transaction(function () use ($user, $carreraId) {
            $codigo = CodigoEstudiante::generar($user->name, $user->id);

            // Verificación de colisión
            $existe = DB::table('alumnos')
                ->where('codigo_estudiante', $codigo)
                ->where('id_usuario', '!=', $user->id)
                ->exists();

            if ($existe) {
                throw new RuntimeException("El código de estudiante {$codigo} ya se encuentra registrado.");
            }

            // Actualizar credenciales y crear perfil
            $user->update([
                'password' => Hash::make($codigo)
            ]);

            return Alumnos::create([
                'id_usuario'           => $user->id,
                'id_carrera'           => $carreraId,
                'codigo_estudiante'    => $codigo,
                'correo_institucional' => strtolower($codigo) . '@uma.edu.sv',
                'estado_carrera'       => 'PENDIENTE',
            ]);
        });
    }
}