<?php

namespace App\Http\Controllers\Estudiante;

use App\Actions\Student\ObtenerRecordAcademicoAction;
use App\Actions\Student\ValidarPuedeRetirarMateriaAction;
use App\Models\Estudiante\DetalleInscripcion;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RecordAcademicoController extends Controller
{
    public function index(Request $request, ObtenerRecordAcademicoAction $action, ValidarPuedeRetirarMateriaAction $validarRetiro)
    {
        $alumno = $request->user()->alumno;
        abort_if(!$alumno, 403);

        $detalles = $action->execute($alumno);

        $actuales  = $detalles->where('estado_materia', 'CURSANDO')->values();
        $historicas = $detalles->where('estado_materia', '!=', 'CURSANDO')->values();

         // Validar retiro por cada materia actual
        $validacionesRetiro = collect();
        foreach ($actuales as $detalle) {
            $model = DetalleInscripcion::find($detalle->detalle_inscripcion_id);
            $validacionesRetiro[$detalle->detalle_inscripcion_id] =
                $validarRetiro->execute($alumno, $model);
        }

        return view('student.record_academico', compact('alumno', 'actuales', 'historicas', 'validacionesRetiro'));
    }
}