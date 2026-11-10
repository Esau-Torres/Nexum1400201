<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use App\Models\Academico\EvaluacionDocente;
use App\Models\Estudiante\DetalleInscripcion;
use App\Models\Estudiante\Inscripcion;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DesempenoDocenteController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $alumno = $user->alumno;

        if (!$alumno) {
            abort(403, 'No tenés perfil de alumno.');
        }

        // Inscripción activa del alumno (solo una puede estar ACTIVA)
        $inscripcion = Inscripcion::query()
            ->where('id_alumno', $alumno->alumno_id)
            ->where('estado_inscripcion', 'ACTIVA')
            ->with('cicloLectivo')
            ->latest('inscripcion_id')
            ->first();

        if (!$inscripcion) {
            return view('student.desempeño_docente', [
                'inscripcion' => null,
                'materias'    => collect(),
            ]);
        }

        // Materias que cursa el alumno en el ciclo activo
        $detalles = DetalleInscripcion::query()
            ->where('id_inscripcion', $inscripcion->inscripcion_id)
            ->whereIn('estado_materia', ['CURSANDO', 'RECURSANDO'])
            ->with([
                'seccion.materia',
                'seccion.docente.user',
                'seccion.horarios.horario',
                'seccion.horarios.aula.edificio',
            ])
            ->get();

        // Mapa de evaluaciones registradas para las secciones del alumno en este ciclo
        $idsSecciones = $detalles->pluck('id_seccion')->unique()->toArray();

        $evaluaciones = EvaluacionDocente::query()
            ->whereIn('id_seccion', $idsSecciones)
            ->where('id_ciclo_lectivo', $inscripcion->id_ciclo_lectivo)
            ->get()
            ->keyBy('id_seccion');

        return view('student.desempeño_docente', [
            'inscripcion' => $inscripcion,
            'detalles'    => $detalles,
            'evaluaciones' => $evaluaciones,
        ]);
    }
}