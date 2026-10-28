<?php

namespace App\Http\Controllers\CoordinadorFacultad;

use App\Http\Controllers\Controller;
use App\Models\Academico\CicloLectivo;
use App\Models\Estudiante\Carreras;
use App\Services\CoordinadorFacultad\ProyeccionSeccionesService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request, ProyeccionSeccionesService $service): View
    {
        $carreras       = Carreras::orderBy('nombre')->get();
        $ciclosLectivos = CicloLectivo::orderByDesc('fecha_inicio')->get();

        // Filtros (con defaults)
        $idCarrera      = (int) $request->input('id_carrera', $carreras->first()->carrera_id ?? 0);
        $idCicloActual  = (int) $request->input('id_ciclo_lectivo', $ciclosLectivos->first()->ciclo_lectivo_id ?? 0);
        $incluirRecursantes = (bool) $request->input('incluir_recursantes', false);

        // Calcular proyección
        $proyeccion = null;
        if ($idCarrera && $idCicloActual) {
            $proyeccion = $service->execute($idCarrera, $idCicloActual, $incluirRecursantes);
        }

        return view('coordinador-facultad.dashboard', compact(
            'carreras',
            'ciclosLectivos',
            'idCarrera',
            'idCicloActual',
            'incluirRecursantes',
            'proyeccion'
        ));
    }
}