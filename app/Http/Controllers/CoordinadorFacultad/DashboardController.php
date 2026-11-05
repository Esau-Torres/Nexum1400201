<?php

namespace App\Http\Controllers\CoordinadorFacultad;

use App\Http\Controllers\Controller;
use App\Models\Academico\CicloLectivo;
use App\Models\Estudiante\Carreras;
use App\Services\CoordinadorFacultad\ProyeccionSeccionesService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
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
    public function pdf(Request $request, ProyeccionSeccionesService $service)
    {
        $idCarrera     = (int) $request->input('id_carrera');
        $idCicloActual = (int) $request->input('id_ciclo_lectivo');

        $carrera = Carreras::find($idCarrera);
        $ciclo   = CicloLectivo::find($idCicloActual);

        if (!$carrera || !$ciclo) {
            abort(404, 'Carrera o ciclo no encontrado.');
        }

        $proyeccion = $service->execute($idCarrera, $idCicloActual);

        $pdf = Pdf::loadView('coordinador-facultad.dashboard-pdf', [
            'carrera'    => $carrera,
            'ciclo'      => $ciclo,
            'proyeccion' => $proyeccion,
            'fecha'      => now()->format('d/m/Y H:i'),
        ])->setPaper('a4', 'portrait');

        $nombre = 'proyeccion-' . \Str::slug($carrera->nombre) . '-' . \Str::slug($ciclo->nombre_ciclo) . '.pdf';

        return $pdf->download($nombre);
    }
}