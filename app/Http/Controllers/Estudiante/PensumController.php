<?php 

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use App\Actions\Student\ObtenerPensumAlumnoAction;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf; // Requiere barryvdh/laravel-dompdf
use App\Models\Estudiante\Alumnos;

class PensumController extends Controller
{
    public function index(Request $request, ObtenerPensumAlumnoAction $action)
    {
        $alumno = Alumnos::activoDeUsuario($request->user()->id);
        
        $pensumPorCiclos = $action->execute($alumno);

        return view('student.pensum', compact('pensumPorCiclos', 'alumno'));
    }

    public function descargarPdf(Request $request, ObtenerPensumAlumnoAction $action)
    {
        $alumno = Alumnos::activoDeUsuario($request->user()->id);
        $pensumPorCiclos = $action->execute($alumno);

        $pdf = Pdf::loadView( 'student.pdf', compact('pensumPorCiclos', 'alumno') )
                    ->setPaper('a4', 'landscape')
                    ->setOption('isHtml5ParserEnabled', true)
                    ->setOption('isRemoteEnabled', true);

        return $pdf->download('pensum_academico_' . $alumno->codigo_estudiante . '.pdf');
    }
}