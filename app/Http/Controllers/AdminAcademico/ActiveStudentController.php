<?php

namespace App\Http\Controllers\AdminAcademico;

use App\Actions\AdminAcademic\CreateActiveStudentAction;
use App\Actions\AdminAcademic\CreateMassiveStudentAction;
use App\Models\Estudiante\Carreras;
use App\Imports\StudentsImport;
use App\Rules\StoreActiveStudentRules;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\StudentTemplateExport;
use App\Mail\NewAccountStudentDirectMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class ActiveStudentController extends Controller
{
    public function __construct(
        protected CreateActiveStudentAction $createActiveStudent,
        protected CreateMassiveStudentAction $createMassiveStudent,
    ) {}

    public function index(): View
    {
        $tipo_documento_identidad = DB::table('tipo_documento_identidad')->get();
        $carreras = Carreras::activasConPensum();
        $regionales     = DB::table('regional_activo')->get();
        $ciclosLectivos = DB::table('ciclo_lectivo')->orderByDesc('fecha_inicio')->get();
        return view('adminacademico.create-student', compact('tipo_documento_identidad', 'carreras', 'regionales', 'ciclosLectivos'));
    }

    public function store(StoreActiveStudentRules $request): RedirectResponse
    {
        try {
            $alumno = $this->createActiveStudent->execute(
                input: $request->validated(),
                admin: $request->user(),
                request: $request,
            );

            // Enviar correo (encolado)
            Mail::to($alumno->usuario->email)->send(new NewAccountStudentDirectMail($alumno));
            Log::info('Usuario nuevo creado individualmente por el admin academico '. $request->user()->email .' en la base de datos. '.' Alumno: ' . $alumno->codigo_estudiante);
            return redirect()->route('admin-academico.create-student')->with('success', "Estudiante {$alumno->usuario->name} creado y activado correctamente. Código: {$alumno->codigo_estudiante}");

        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            Log::error('Error creando estudiante individualmente: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'No se pudo crear el estudiante. ' . $e->getMessage());
        }
    }

    // ---------- Masivo ----------
    public function massiveForm(): View
    {
        $tipo_documento_identidad = DB::table('tipo_documento_identidad')->get();
        $carreras                 = Carreras::activasConPensum();
        $regionales               = DB::table('regional_activo')->get();

        return view('adminacademico.create-student-massive',
            compact('tipo_documento_identidad', 'carreras', 'regionales'));
    }

    public function massiveStore(Request $request): RedirectResponse
    {
        $request->validate([
            'archivo' => ['required', 'file', 'mimes:csv,xlsx,xls', 'max:5120'],
        ], [
            'archivo.required' => 'Adjuntá el archivo con los estudiantes.',
            'archivo.mimes'    => 'Formato inválido. Usá CSV o XLSX.',
            'archivo.max'      => 'El archivo no debe superar 5 MB.',
        ]);

        $import = new StudentsImport($request->user(), $this->createMassiveStudent);

        try {
            Excel::import($import, $request->file('archivo'));
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'Error procesando el archivo: ' . $e->getMessage());
        }

        $totalCreados = count($import->creados);
        $totalErrores = count($import->errores);

        return redirect()
            ->route('admin-academico.create-student.massive')
            ->with('massive_result', [
                'creados' => $import->creados,
                'errores' => $import->errores,
                'resumen' => "{$totalCreados} creados · {$totalErrores} con errores",
            ]);
    }

    public function massiveTemplate()
    {
        $filename = 'plantilla_estudiantes_' . now()->format('Y-m-d') . '.xlsx';
        return Excel::download(new StudentTemplateExport(), $filename);
    }
    
}