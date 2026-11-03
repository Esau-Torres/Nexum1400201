<?php

namespace App\Http\Controllers\Docente;

use App\Http\Controllers\Controller;
use App\Models\CoordinadorFacultad\Seccion;
use App\Models\Academico\Evaluaciones;
use App\Models\Academico\ClaseSesion;
use App\Models\Estudiante\DetalleInscripcion;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardDocenteController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $docente = $user->docente;

        if (!$docente) {
            abort(403, 'No tienes un perfil de docente asociado.');
        }

        // 1. Materias asignadas este ciclo (secciones activas del docente)
        $secciones = Seccion::where('id_docente', $docente->docente_id)
            ->where('estado', 'ACTIVA')
            ->get();

        $totalMaterias = $secciones->count();

        // 2. Estudiantes a cargo (suma de inscritos activos en esas secciones)
        $totalEstudiantes = DetalleInscripcion::whereIn('id_seccion', $secciones->pluck('seccion_id'))
            ->where('estado_materia', 'CURSANDO')
            ->count();

        // 3. Clases esta semana (sesiones programadas entre lunes y domingo)
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        $clasesSemana = ClaseSesion::whereIn('id_seccion', $secciones->pluck('seccion_id'))
            ->whereBetween('fecha', [$startOfWeek, $endOfWeek])
            ->count();

        // 4. Evaluaciones pendientes (estado PROGRAMADA en las categorías de sus secciones)
        $evaluacionesPendientes = Evaluaciones::whereIn('id_categoria', function ($query) use ($secciones) {
            $query->select('categoria_id')
                ->from('categoria_evaluacion')
                ->whereIn('id_seccion', $secciones->pluck('seccion_id'));
        })
            ->where('estado', 'PROGRAMADA')
            ->count();

        // Obtener el nombre del ciclo actual
        $cicloActual = $secciones->first()?->cicloLectivo?->nombre_ciclo ?? 'Ciclo Actual';

        return view('docente.home_docente', compact(
            'totalMaterias',
            'totalEstudiantes',
            'clasesSemana',
            'evaluacionesPendientes',
            'cicloActual'
        ));
    }
}
