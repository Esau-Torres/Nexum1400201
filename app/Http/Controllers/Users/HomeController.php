<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use App\Models\Estudiante\Alumnos;

class HomeController extends Controller
{
    public function index(): View|RedirectResponse
    {
        $usuario = auth()->user();

        if ($usuario->hasRole('DOCENTE')) {
            return redirect()->route('docente.home');
        }

        $relations = ['roles', 'regionalactivo'];
        $metricas = null;
        $alumnoActivo = null;

        if ($usuario->hasRole('ESTUDIANTE')) {
            $alumnoActivo = Alumnos::activoDeUsuario($usuario->id);

            if ($alumnoActivo) {
                $alumnoActivo->load([
                    'carrera.facultad',
                    'documentos',
                    'beneficioCicloActual.cicloLectivo',
                ]);

                $metricas = $this->calcularMetricasAcademicas($alumnoActivo->alumno_id);
            }

            $usuario->load($relations);

            return view('home', compact('usuario', 'metricas', 'alumnoActivo'));
        }

        $usuario->load($relations);

        return view('home', compact('usuario', 'metricas', 'alumnoActivo'));
    }

    /**
     * Calcula UV ganadas y CUM del estudiante.
     *
     * - UV ganadas: suma de unidades_valorativas de materias APROBADAS.
     * - CUM: promedio de promedio_final de materias APROBADAS y REPROBADAS.
     */
    private function calcularMetricasAcademicas(int $alumnoId): array
    {
        // UV ganadas
        $uvGanadas = (int) DB::table('detalle_inscripcion as di')
            ->join('inscripciones as i', 'di.id_inscripcion', '=', 'i.inscripcion_id')
            ->join('seccion as s', 'di.id_seccion', '=', 's.seccion_id')
            ->join('materias as m', 's.id_materia', '=', 'm.materia_id')
            ->where('i.id_alumno', $alumnoId)
            ->where('di.estado_materia', 'APROBADA')
            ->sum('m.unidades_valorativas');

        // CUM
        $cum = DB::table('detalle_inscripcion as di')
            ->join('inscripciones as i', 'di.id_inscripcion', '=', 'i.inscripcion_id')
            ->where('i.id_alumno', $alumnoId)
            ->whereIn('di.estado_materia', ['APROBADA', 'REPROBADA'])
            ->whereNotNull('di.promedio_final')
            ->avg('di.promedio_final');

        $cum = $cum !== null ? round((float) $cum, 2) : null;

        // Materias aprobadas (conteo, opcional)
        $materiasAprobadas = (int) DB::table('detalle_inscripcion as di')
            ->join('inscripciones as i', 'di.id_inscripcion', '=', 'i.inscripcion_id')
            ->where('i.id_alumno', $alumnoId)
            ->where('di.estado_materia', 'APROBADA')
            ->distinct()
            ->count('di.id_seccion');

        return [
            'uv_ganadas'         => $uvGanadas,
            'cum'                => $cum,
            'materias_aprobadas' => $materiasAprobadas,
        ];
    }
}