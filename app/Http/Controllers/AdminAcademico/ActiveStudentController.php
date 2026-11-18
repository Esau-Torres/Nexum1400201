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
use App\Models\Users\User;
use Illuminate\Http\JsonResponse;
use App\Enums\EstadoCiclo;
use App\Imports\RowsPreviewImport;
use Illuminate\Support\Facades\RateLimiter;

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
        $ciclosLectivos = DB::table('ciclo_lectivo')->where('estado', '!=', EstadoCiclo::CERRADO->value)->orderByDesc('fecha_inicio')->get();

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
        // =========================================================
        // 0. Rate limiting por usuario (evita abuso del endpoint)
        // =========================================================
        $key = 'massive-upload:' . $request->user()->id;

        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->with('error', "Demasiados intentos. Intenta de nuevo en {$seconds} segundos.");
        }

        RateLimiter::hit($key, 600); // ventana de 10 minutos

        // =========================================================
        // 1. Validación de tipo MIME y tamaño
        // =========================================================
        $request->validate([
            'archivo' => ['required', 'file', 'mimes:csv,xlsx,xls', 'max:2048'],
        ], [
            'archivo.required' => 'Adjuntá el archivo con los estudiantes.',
            'archivo.mimes'    => 'Formato inválido. Usá CSV o XLSX.',
            'archivo.max'      => 'El archivo no debe superar 2 MB.',
        ]);

        $archivo = $request->file('archivo');

        // =========================================================
        // 2. Leer el archivo UNA SOLA VEZ (con encabezados asociativos)
        // =========================================================
        $matriz = Excel::toArray(new RowsPreviewImport(), $archivo)[0] ?? [];

        if (empty($matriz)) {
            return back()->with('error', 'El archivo está vacío o no tiene el formato esperado.');
        }

        // =========================================================
        // 3. Filtrar filas REALES (con datos) antes de validar estructura
        //    Ignoramos las filas vacías que Excel mantiene por los dropdowns.
        // =========================================================
        $filasConDatos = collect($matriz)->filter(function ($fila) {
            $email = strtolower(trim((string) ($fila['email'] ?? '')));
            $name  = trim((string) ($fila['name'] ?? ''));

            // Sin email ni nombre → fila vacía
            if ($email === '' && $name === '') {
                return false;
            }

            // Filas de ejemplo residuales → también se ignoran
            if (str_ends_with($email, '@example.com')) {
                return false;
            }

            return true;
        })->values(); // reindexar

        // =========================================================
        // 4. Validar estructura de encabezados
        //    Usamos las CLAVES de la primera fila con datos
        //    (con WithHeadingRow, las claves ya son los encabezados).
        // =========================================================
        if ($filasConDatos->isEmpty()) {
            return back()->with('error', 'El archivo no contiene filas de datos.');
        }

        $columnasRequeridas = [
            'name', 'email', 'id_tipo_documento', 'documento_identidad',
            'fecha_nacimiento', 'genero', 'estado_civil', 'celular',
            'direccion', 'id_regional_activo', 'id_carrera',
        ];

        $primeraFila     = $filasConDatos->first();
        $columnasArchivo = array_keys($primeraFila);
        $faltantes       = array_diff($columnasRequeridas, $columnasArchivo);
        $sobrantes       = array_diff($columnasArchivo, $columnasRequeridas);

        if (!empty($faltantes) || !empty($sobrantes)) {
            return back()->with('error',
                'La estructura del archivo no coincide con la plantilla oficial. ' .
                'Columnas faltantes: ' . implode(', ', $faltantes) . '. ' .
                'Columnas no permitidas: ' . implode(', ', $sobrantes)
            );
        }

        // =========================================================
        // 5. Validar TOTAL de filas (atómico: si excede, no se guarda nada)
        // =========================================================
        $totalFilas = $filasConDatos->count();

        if ($totalFilas > 50) {
            return back()->with('error',
                "El archivo contiene {$totalFilas} filas y el máximo permitido es 50 por carga. " .
                "Divide el archivo en partes más pequeñas y volvé a intentarlo. " .
                "No se guardó ningún registro."
            );
        }

        // =========================================================
        // 6. Procesar SOLO las filas con datos (sin releer el archivo)
        // =========================================================
        $import = new StudentsImport($request->user(), $this->createMassiveStudent);

        try {
            $import->procesarFilas($filasConDatos);
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'Error procesando el archivo: ' . $e->getMessage());
        }

        // Liberar memoria
        unset($matriz, $filasConDatos);

        $totalCreados = count($import->creados);
        $totalErrores = count($import->errores);

        $mensaje = $totalCreados > 0 ? "Carga completada: {$totalCreados} estudiante(s) creado(s)." : "La carga no creó ningún estudiante. Revisá los errores.";
        $tipo = $totalCreados > 0 ? 'success' : 'error';

        return redirect()
            ->route('admin-academico.create-student.massive')
            ->with($tipo, $mensaje)
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

    public function checkEmail(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email:rfc'],
        ]);

        $email = strtolower(trim($request->email));

        $existe = User::where('email', $email)->exists();

        return response()->json(['available' => !$existe]);
    }
    
}