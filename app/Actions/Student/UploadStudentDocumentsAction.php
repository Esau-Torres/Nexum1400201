<?php  

namespace App\Actions\Student;

use App\Models\Estudiante\Alumnos;
use App\Models\Estudiante\AlumnoDocumentos;
use Illuminate\Http\Request;

class UploadStudentDocumentsAction
{
    public function execute(Alumnos $alumno, Request $request): AlumnoDocumentos
    {
        $rutapr = "documentos/alumnos-ingreso/{$alumno->alumno_id}";
        $rutap = "estudiantes/imagenes/{$alumno->alumno_id}";

        return AlumnoDocumentos::create([
            'id_alumno'           => $alumno->alumno_id,
            'titulo_bachillerato' => $request->file('titulo_bachillerato')->store($rutapr),
            'partida_nacimiento'  => $request->file('partida_nacimiento')->store($rutapr),
            'fotografia_personal' => $request->file('fotografia_personal')->store($rutap, 'public'),
            'constancia_paes'     => $request->hasFile('constancia_paes') ? $request->file('constancia_paes')->store($rutapr) : null,
            'estado_documentos'   => 'PENDIENTE',
            'observaciones'       => $request->input('observaciones'),
        ]);
    }
}