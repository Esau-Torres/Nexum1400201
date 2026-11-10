<?php

namespace App\Http\Controllers\Docente;

use App\Http\Controllers\Controller;
use App\Models\Academico\Asistencia;
use App\Models\Academico\ClaseSesion;
use App\Models\CoordinadorFacultad\Seccion;
use App\Models\Estudiante\DetalleInscripcion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AsistenciaMateriaController extends Controller
{
    public function index(int $seccionId)
    {
        $user = Auth::user();
        $docente = $user->docente;

        if (!$docente) {
            abort(403, 'No tienes un perfil de docente asociado.');
        }

        $seccion = Seccion::with(['materia', 'cicloLectivo'])
            ->where('seccion_id', $seccionId)
            ->where('id_docente', $docente->docente_id)
            ->firstOrFail();

        // Obtener sesiones de clase de esta sección, ordenadas por fecha
        $sesiones = ClaseSesion::where('id_seccion', $seccionId)
            ->orderBy('fecha', 'asc')
            ->get();

        // Obtener estudiantes inscritos con sus registros de asistencia para estas sesiones
        $detalleInscripciones = DetalleInscripcion::with([
            'inscripcion.alumno.usuario',
            'asistencias' => function ($query) use ($sesiones) {
                $query->whereIn('id_sesion', $sesiones->pluck('clase_sesion_id'));
            }
        ])
            ->where('id_seccion', $seccionId)
            ->where('estado_materia', 'CURSANDO')
            ->get()
            ->sortBy(fn($d) => optional(optional(optional($d->inscripcion)->alumno)->usuario)->name ?? '');

        $materia = [
            'nombre' => $seccion->materia->nombre ?? 'Sin nombre',
            'codigo' => $seccion->materia->codigo_materia ?? 'S/C',
            'grupo' => $seccion->nombre_grupo,
            'ciclo' => $seccion->cicloLectivo?->nombre_ciclo ?? '',
            'total_estudiantes' => $detalleInscripciones->count(),
        ];

        return view('docente.asistencia_materia', [
            'materia' => $materia,
            'seccionId' => $seccionId,
            'sesiones' => $sesiones,
            'estudiantes' => $detalleInscripciones,
        ]);
    }

    public function store(Request $request, int $seccionId)
    {
        $user = Auth::user();
        $docente = $user->docente;

        $seccion = Seccion::where('seccion_id', $seccionId)
            ->where('id_docente', $docente->docente_id)
            ->firstOrFail();

        $validated = $request->validate([
            'id_sesion' => 'required|exists:clase_sesion,clase_sesion_id',
            'asistencias' => 'required|array',
            'asistencias.*.id_detalle_inscripcion' => 'required|exists:detalle_inscripcion,detalle_inscripcion_id',
            'asistencias.*.estado' => 'required|in:Asis,Falt,Per',
            'asistencias.*.observacion' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($validated, $seccionId) {
            foreach ($validated['asistencias'] as $asistenciaData) {
                // Verificar que el detalle de inscripción pertenezca a la sección
                $detalle = DetalleInscripcion::where('detalle_inscripcion_id', $asistenciaData['id_detalle_inscripcion'])
                    ->where('id_seccion', $seccionId)
                    ->first();

                if (!$detalle) continue;

                Asistencia::updateOrCreate(
                    [
                        'id_sesion' => $validated['id_sesion'],
                        'id_detalle_inscripcion' => $asistenciaData['id_detalle_inscripcion'],
                    ],
                    [
                        'estado' => $asistenciaData['estado'],
                        'observacion' => $asistenciaData['observacion'] ?? null,
                    ]
                );
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Asistencia guardada correctamente.'
        ]);
    }
}
