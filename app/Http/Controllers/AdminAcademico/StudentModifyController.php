<?php

namespace App\Http\Controllers\AdminAcademico;

use App\Actions\AdminAcademic\DeleteStudentDocumentsAction;
use App\Actions\AdminAcademic\UpdateStudentAction;
use App\Actions\AdminAcademic\UploadStudentDocumentsAction;
use App\Http\Controllers\Controller;
use App\Rules\UpdateStudentRules;
use App\Rules\UploadStudentDocumentsRules;
use App\Models\Estudiante\Alumnos;
use App\Models\Users\RegionalActivo;
use App\Models\Estudiante\Carreras;
use App\Models\Academico\CicloLectivo;
use App\Models\Users\TipoDocumentoIdentidad;
use Illuminate\Http\RedirectResponse;
use App\Models\Estudiante\AlumnoDocumentos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use App\Mail\AssingBenefitStudentMail;
use Illuminate\Support\Facades\Mail;
use App\Actions\AdminAcademic\AssignStudentBenefitAction;
use App\Rules\AssignBenefitRules;
use App\Models\Academico\BeneficioEstudiante;
use App\Enums\EstadoBeneficio;
use Illuminate\Support\Facades\Log;

class StudentModifyController extends Controller
{
    public function assignBenefit(AssignBenefitRules $request, Alumnos $alumno, AssignStudentBenefitAction $action): RedirectResponse 
    {
        try {
            // Bloqueo: si ya tiene un beneficio activo, no permitir
            $tieneActivo = BeneficioEstudiante::where('id_alumno', $alumno->alumno_id)
                ->where('estado', EstadoBeneficio::ACTIVO)
                ->exists();

            if ($tieneActivo) {
                return back()->with(
                    'error',
                    'Este estudiante ya tiene un beneficio activo. Para cambiarlo ve a Gestión de Beneficios.'
                );
            }

            $beneficio = $action->execute(
                alumno: $alumno,
                data: $request->validated(),
                asignadoPor: $request->user(),
            );

            $beneficio->load(['alumno.usuario', 'alumno.carrera', 'cicloLectivo']);
             Mail::to($beneficio->alumno->usuario->email)->queue(new AssingBenefitStudentMail(beneficio: $beneficio,motivo: 'Asignación inicial de beneficio'));
             Log::info('Asignacion de beneficio de estudiante por:'. $request->user()->email .' al Alumno: ' . $beneficio->alumno->codigo_estudiante );

            return redirect()
                ->route('admin-academico.alumnos.modify-student')
                ->with('success', "Beneficio asignado a {$alumno->usuario->name} correctamente.");
        } catch (\Throwable $e) {
            report($e);
            return back()
                ->withInput()
                ->with('error', 'No se pudo asignar el beneficio: ' . $e->getMessage());
        }
    }

    public function index(Request $request): View
    {
        $alumnos = Alumnos::query()
            ->with(['usuario.tipodocumentoidentidad', 'usuario.regionalactivo', 'carrera.facultad', 'documentos', 'beneficios' => fn ($q) => $q->where('estado', 'ACTIVO'),'beneficios.cicloLectivo'])
            ->when($request->input('search'), function ($q, $search) {
                $q->where(function ($w) use ($search) {
                    $w->whereHas('usuario', fn ($u) => $u->where('name', 'ILIKE', "%{$search}%")
                                                          ->orWhere('email', 'ILIKE', "%{$search}%"))
                      ->orWhere('codigo_estudiante', 'ILIKE', "%{$search}%");
                });
            })
            ->orderByDesc('alumno_id')
            ->paginate(12)
            ->withQueryString();

        $regionalactivo = RegionalActivo::orderBy('sede')->get();
        $documentosidentidad = TipoDocumentoIdentidad::orderBy('nombre')->get();
        $ciclosLectivos = CicloLectivo::orderByDesc('fecha_inicio')->get();

        return view('adminacademico.modify-student', compact('alumnos', 'regionalactivo', 'documentosidentidad', 'ciclosLectivos'));
    }

    public function update(
        UpdateStudentRules $request,
        Alumnos $alumno,
        UpdateStudentAction $action
    ): RedirectResponse {
        try {
            $action->execute($alumno, $request->validated(), $request->user());

            return back()->with('success', 'Datos del estudiante actualizados correctamente.');
        } catch (\Throwable $e) {
            report($e);
            return back()->withInput()->with('error', 'No se pudo actualizar: ' . $e->getMessage());
        }
    }

    public function deleteDocument(
        Request $request,
        Alumnos $alumno,
        string $campo,
        DeleteStudentDocumentsAction $action
    ): RedirectResponse {
        try {
            $action->execute($alumno, $campo, $request->user());

            return back()->with('success', 'Documento eliminado correctamente.');
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'No se pudo eliminar: ' . $e->getMessage());
        }
    }

    public function uploadDocuments(
        UploadStudentDocumentsRules $request,
        Alumnos $alumno,
        UploadStudentDocumentsAction $action
    ): RedirectResponse {
        try {
            $action->execute(
                alumno: $alumno,
                request: $request,
                estadoDocumentos: $alumno->documentos->estado_documentos ?? 'PENDIENTE',
                revisadoPor: $request->user()->id,
            );

            return back()->with('success', 'Documentos actualizados correctamente.');
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'No se pudieron subir: ' . $e->getMessage());
        }
    }
}