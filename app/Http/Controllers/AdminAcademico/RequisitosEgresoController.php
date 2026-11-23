<?php

namespace App\Http\Controllers\AdminAcademico;

use App\Actions\AdminAcademic\RegistrarEgresoAction;
use App\Actions\AdminAcademic\ValidarEgresoAction;
use App\Actions\AdminAcademic\GestionarHorasSocialesAction;
use App\Actions\AdminAcademic\GestionarPasantiaAction;
use App\Actions\AdminAcademic\MarcarSolvenciaFinancieraAction;
use App\Http\Controllers\Controller;
use App\Rules\Egreso\HorasSocialesRequest;
use App\Rules\Egreso\PasantiaRequest;
use App\Rules\Egreso\UvMinimasHorasSocialesRule;
use App\Rules\Egreso\UvMinimasPasantiaRule;
use App\Models\Estudiante\Alumnos;
use App\Models\Estudiante\RequisitosEgreso;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;


class RequisitosEgresoController extends Controller
{
    public function index(Request $request, ValidarEgresoAction $validarAction)
    {
        $alumnos = Alumnos::with(['usuario', 'carrera.facultad', 'carrera.nivel'])
            ->where('estado_carrera', 'ACTIVO')
            ->orderBy('codigo_estudiante')
            ->get();

        $data = $alumnos->map(function ($alumno) use ($validarAction) {
            $req = RequisitosEgreso::paraAlumno($alumno->alumno_id);
            $validacion = $validarAction->execute($alumno);

            // UV ganadas y nivel académico
            $uvGanadas = (int) DB::table('detalle_inscripcion as di')
                ->join('inscripciones as i', 'di.id_inscripcion', '=', 'i.inscripcion_id')
                ->join('seccion as s', 'di.id_seccion', '=', 's.seccion_id')
                ->join('materias as m', 's.id_materia', '=', 'm.materia_id')
                ->where('i.id_alumno', $alumno->alumno_id)
                ->where('di.estado_materia', 'APROBADA')
                ->sum('m.unidades_valorativas');

            $nivelCodigo = $alumno->carrera?->nivel?->codigo;
            $uvMinimas = match ($nivelCodigo) {
                'LICENCIATURA' => 64,
                'PROFESORADO'  => 32,
                'TECNICO'      => 32,
                'MAESTRIA'     => 16,
                default        => 64,
            };

            $tienearancelSS = DB::table('pago as p')
                ->join('detalle_pago as dp', 'p.pago_id', '=', 'dp.id_pago')
                ->join('concepto_pago as cp', 'dp.id_concepto', '=', 'cp.concepto_pago_id')
                ->where('p.id_alumno', $alumno->alumno_id)
                ->where('cp.codigo_concepto', 'TRA-014')
                ->exists();

            return [
                'alumno'      => $alumno,
                'req'         => $req,
                'validacion'  => $validacion,
                'uv_ganadas'  => $uvGanadas,
                'nivel'       => $nivelCodigo,
                'uv_minimas'  => $uvMinimas,
                'puede_hs'    => $uvGanadas >= $uvMinimas,
                'puede_pas'   => $uvGanadas >= $uvMinimas,
                'tiene_arancel_ss' => $tienearancelSS,
            ];
        });

        return view('adminacademico.requsitos_egreso', [
            'registros' => $data,
        ]);
    }

    public function registrar(Request $request, int $alumnoId, RegistrarEgresoAction $action)
    {
        $alumno = Alumnos::findOrFail($alumnoId);

        try {
            $action->execute($alumno, $request->user());

            return redirect()
                ->route('admin-academico.egresos.index')
                ->with('success', "El alumno {$alumno->codigo_estudiante} ha sido registrado como EGRESADO.");
        } catch (ValidationException $e) {
            return redirect()
                ->route('admin-academico.egresos.index')
                ->with('error', collect($e->errors())->flatten()->first());
        }
    }

    public function gestionarHorasSociales(HorasSocialesRequest $request, int $alumnoId, GestionarHorasSocialesAction $action) {
        $alumno = Alumnos::findOrFail($alumnoId);

        // Validar UV mínimas
        $validator = Validator::make(
            ['uv' => true],
            ['uv' => [new UvMinimasHorasSocialesRule($alumno)]]
        );

        if ($validator->fails()) {
            return redirect()
                ->route('admin-academico.egresos.index')
                ->with('error', $validator->errors()->first());
        }

        try {
            $action->execute(
                $alumno,
                (int) $request->horas_sociales_completadas,
                $request->boolean('horas_sociales_aprobadas'),
                $request->file('documento')
            );

            return redirect()
                ->route('admin-academico.egresos.index')
                ->with('success', 'Horas sociales actualizadas correctamente.');
        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first());
        }
    }

    public function gestionarPasantia(PasantiaRequest $request, int $alumnoId, GestionarPasantiaAction $action) {
        $alumno = Alumnos::findOrFail($alumnoId);

        // Solo validar UV si se requiere pasantía
        if ($request->boolean('requiere_pasantia')) {
            $validator = Validator::make(
                ['uv' => true],
                ['uv' => [new UvMinimasPasantiaRule($alumno)]]
            );

            if ($validator->fails()) {
                return redirect()
                    ->route('admin-academico.egresos.index')
                    ->with('error', $validator->errors()->first());
            }
        }

        try {
            $action->execute(
                $alumno,
                $request->boolean('requiere_pasantia'),
                $request->boolean('pasantia_aprobada'),
                $request->institucion_pasantia,
                $request->file('documento')
            );

            return redirect()
                ->route('admin-academico.egresos.index')
                ->with('success', 'Pasantía actualizada correctamente.');
        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first());
        }
    }

    /**
     * Descarga segura de documentos (solo ADMIN_ACADEMICO).
     */
    public function descargarDocumento(int $alumnoId, string $tipo)
    {
        $req = RequisitosEgreso::where('id_alumno', $alumnoId)->firstOrFail();

        $path = match ($tipo) {
            'social'    => $req->documento_constancia_social,
            'pasantia'  => $req->documento_pasantia,
            default     => null,
        };

        if (!$path || !\Storage::disk('local')->exists($path)) {
            abort(404, 'Documento no encontrado.');
        }

        return \Storage::disk('local')->download($path);
    }

    public function marcarSolvencia( int $alumnoId, MarcarSolvenciaFinancieraAction $action) {
        $alumno = Alumnos::findOrFail($alumnoId);

        try {
            $action->execute($alumno);

            return redirect()
                ->route('admin-academico.egresos.index')
                ->with('success', "Solvencia financiera marcada como APROBADA para {$alumno->codigo_estudiante}.");
        } catch (ValidationException $e) {
            return redirect()
                ->route('admin-academico.egresos.index')
                ->with('error', collect($e->errors())->flatten()->first());
        } catch (\Throwable $e) {
            report($e);
            return redirect()
                ->route('admin-academico.egresos.index')
                ->with('error', 'Error al calcular la solvencia financiera.');
        }
    }
}