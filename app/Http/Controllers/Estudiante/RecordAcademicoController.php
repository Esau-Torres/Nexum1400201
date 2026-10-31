<?php

namespace App\Http\Controllers\Estudiante;

use App\Actions\Student\ObtenerRecordAcademicoAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RecordAcademicoController extends Controller
{
    public function index(Request $request, ObtenerRecordAcademicoAction $action)
    {
        $alumno = $request->user()->alumno;
        abort_if(!$alumno, 403);

        $detalles = $action->execute($alumno);

        $actuales  = $detalles->where('estado_materia', 'CURSANDO')->values();
        $historicas = $detalles->where('estado_materia', '!=', 'CURSANDO')->values();

        return view('student.record_academico', compact('alumno', 'actuales', 'historicas'));
    }
}