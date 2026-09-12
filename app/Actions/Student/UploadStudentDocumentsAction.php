<?php  

namespace App\Actions\Student;

use App\Models\Estudiante\Alumno;
use App\Models\Estudiante\AlumnoDocumentos;
use Illuminate\Http\Request;

class UploadStudentDocumentsAction
{
    public function execute(Alumno $alumno, Request $request): AlumnoDocumentos
    {
        return AlumnoDocumentos::create([
            'id_alumno'           => $alumno->alumno_id,
            'titulo_bachillerato' => $request->file('titulo_bachillerato')->store('documentos/ingreso'),
            'partida_nacimiento'  => $request->file('partida_nacimiento')->store('documentos/ingreso'),
            'fotografia_personal' => $request->file('fotografia_personal')->store('documentos/ingreso'),
            'constancia_paes'     => $request->hasFile('constancia_paes') ? $request->file('constancia_paes')->store('documentos/ingreso') : null,
            'estado_documentos'   => 'PENDIENTE',
            'observaciones'       => $request->input('observaciones'),
        ]);
    }
}