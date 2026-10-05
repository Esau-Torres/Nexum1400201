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
use App\Models\Users\TipoDocumentoIdentidad;
use Illuminate\Http\RedirectResponse;
use App\Models\Estudiante\AlumnoDocumentos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StudentModifyController extends Controller
{
    public function index(Request $request): View
    {
        $alumnos = Alumnos::query()
            ->with(['usuario.tipodocumentoidentidad', 'usuario.regionalactivo', 'carrera.facultad', 'documentos'])
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

        return view('adminacademico.modify-student', compact('alumnos', 'regionalactivo', 'documentosidentidad'));
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