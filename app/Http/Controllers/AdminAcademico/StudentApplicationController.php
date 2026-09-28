<?php

namespace App\Http\Controllers\AdminAcademico;

use App\Actions\AdminAcademic\ApproveStudentApplicationAction;
use App\Actions\AdminAcademic\RejectStudentApplicationAction;
use App\Models\Estudiante\AlumnoDocumentos;
use App\Http\Controllers\Controller;
use App\Rules\ApproveStudentRules;
use App\Rules\RejectStudentRules;
use App\Models\Estudiante\Alumnos;
use App\Models\Users\RegionalActivo;
use App\Models\Academico\CicloLectivo;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class StudentApplicationController extends Controller
{
    public function index(): View
    {
        $solicitudes = Alumnos::pendientes()
            ->conSolicitudCompleta()
            ->orderByDesc('alumno_id')
            ->paginate(15);

        // Para el modal de aprobación
        $ciclosLectivos = CicloLectivo::orderByDesc('fecha_inicio')->get();
        $regionales     = RegionalActivo::orderBy('sede')->get();

        return view('adminacademico.solicitudes.approve-student', compact('solicitudes', 'ciclosLectivos', 'regionales'));
    }

    public function show(Alumnos $solicitud): View
    {
        abort_unless($solicitud->estado_carrera === 'PENDIENTE', 404);

        $solicitud->load([
            'usuario.tipodocumentoidentidad',
            'usuario.regionalactivo',
            'carrera.facultad',
            'documentos',
        ]);

        $ciclosLectivos = CicloLectivo::orderByDesc('fecha_inicio')->get();
        $regionales     = RegionalActivo::orderBy('sede')->get();

        return view('adminacademico.solicitudes.show', compact('solicitud', 'ciclosLectivos', 'regionales'));
    }

    public function approve(ApproveStudentRules $request, Alumnos $solicitud, ApproveStudentApplicationAction $action): RedirectResponse
    {
        try {
            $action->execute($solicitud, $request->validated(), $request->user());

            return redirect()
                ->route('admin-academico.admin.academico.solicitudes.approve-student')
                ->with('success', "Solicitud de {$solicitud->usuario->name} aprobada correctamente.");
        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with('error', 'No se pudo aprobar la solicitud: ' . $e->getMessage());
        }
    }

    public function reject(RejectStudentRules $request, Alumnos $solicitud, RejectStudentApplicationAction $action): RedirectResponse
    {
        try {
            $nombre = $solicitud->usuario->name;
            $action->execute($solicitud, $request->validated(), $request->user());

            return redirect()
                ->route('admin-academico.admin.academico.solicitudes.approve-student')
                ->with('success', "Solicitud de {$nombre} rechazada y eliminada del sistema.");
        } catch (\Throwable $e) {
            report($e);

            return back()
                ->with('error', 'No se pudo rechazar la solicitud: ' . $e->getMessage());
        }
    }

    public function downloadDocument(int $alumno, string $campo)
    {
        $camposValidos = ['titulo_bachillerato', 'partida_nacimiento', 'fotografia_personal', 'constancia_paes'];

        abort_unless(in_array($campo, $camposValidos, true), 404);

        $doc = AlumnoDocumentos::where('id_alumno', $alumno)->firstOrFail();
        $path = $doc->$campo;

        abort_unless($path && \Storage::exists($path), 404);

        return \Storage::download($path);
    }
}