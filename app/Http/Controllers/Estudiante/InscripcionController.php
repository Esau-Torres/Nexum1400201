<?php

namespace App\Http\Controllers\Estudiante;

use App\Actions\Student\InscribirMateriaAction;
use App\Actions\Student\ObtenerSeccionesDisponiblesAction;
use App\Actions\Student\ValidarPuedeInscribirseAction;
use App\Actions\Student\ValidarSolvenciaAction;
use App\Http\Controllers\Controller;
use App\Models\Academico\CicloLectivo;
use App\Models\CoordinadorFacultad\Seccion;
use App\Rules\Student\CumplePrerequisitosRule;
use App\Rules\Student\MaxReprobacionesRule;
use App\Enums\EstadoCiclo;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class InscripcionController extends Controller
{
    public function index(
        Request $request,
        ObtenerSeccionesDisponiblesAction $seccionesAction,
        ValidarPuedeInscribirseAction $validarAction
    ) {
        $alumno = $request->user()->alumno;
        abort_if(!$alumno, 403);

        $ciclo = CicloLectivo::where('estado', EstadoCiclo::MATRICULA_ABIERTA->value)
            ->orderByDesc('fecha_inicio')
            ->first();

        // ─────────────────────────────────────────────────────
        // Si NO hay ciclo en matrícula abierta → modal bloqueante
        // ─────────────────────────────────────────────────────
        if (!$ciclo) {
            $cicloReciente = CicloLectivo::orderByDesc('fecha_inicio')->first();

            return view('student.inscripcion', [
                'alumno'              => $alumno,
                'ciclo'               => $cicloReciente,
                'seccionesPorCiclo'   => collect(),
                'validacionGlobal'    => [
                    'solvente'           => true,
                    'motivos'            => [],
                    'limite_materias'    => 0,
                    'materias_inscritas' => 0,
                ],
                'matriculaCerrada'    => true,
            ]);
        }

        // ─────────────────────────────────────────────────────
        // Flujo normal
        // ─────────────────────────────────────────────────────
        $seccionesPorCiclo = $seccionesAction->execute($alumno, $ciclo->ciclo_lectivo_id);

        $solvencia = app(ValidarSolvenciaAction::class)
            ->execute($alumno, $ciclo->ciclo_lectivo_id);

        $validacionDummy = $validarAction->obtenerLimiteMaterias($alumno, $ciclo->ciclo_lectivo_id);

        $validacionGlobal = [
            'solvente'           => $solvencia['solvente'],
            'motivos'            => $solvencia['motivos'],
            'limite_materias'    => $validacionDummy['limite'],
            'materias_inscritas' => $validacionDummy['inscritas'],
        ];

        return view('student.inscripcion', [
            'alumno'              => $alumno,
            'ciclo'               => $ciclo,
            'seccionesPorCiclo'   => $seccionesPorCiclo,
            'validacionGlobal'    => $validacionGlobal,
            'matriculaCerrada'    => false,
        ]);
    }

    public function inscribir(
        Request $request,
        ValidarPuedeInscribirseAction $validarAction,
        InscribirMateriaAction $inscribirAction
    ) {
        $alumno = $request->user()->alumno;
        abort_if(!$alumno, 403);

        $data = $request->validate([
            'seccion_id' => ['required', 'integer', 'exists:seccion,seccion_id'],
        ]);

        $seccion = Seccion::findOrFail($data['seccion_id']);

        // 1. Validación completa con la Action
        $validacion = $validarAction->execute($alumno, $seccion, $seccion->id_ciclo_lectivo);

        if (!$validacion['puede']) {
            return back()->withErrors([
                'inscripcion' => $validacion['bloqueadores']
            ])->with('error', 'No puedes inscribirte: ' . implode(' ', $validacion['bloqueadores']));
        }

        // 2. Reglas Laravel adicionales (defensa en profundidad)
        try {
            validator(
                ['seccion_id' => $seccion->seccion_id],
                [
                    'seccion_id' => [
                        new CumplePrerequisitosRule($alumno, $seccion->id_materia),
                        new MaxReprobacionesRule($alumno, $seccion->id_materia),
                    ],
                ]
            )->validate();
        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first());
        }

        // 3. Inscribir
        try {
            $inscribirAction->execute($alumno, $seccion, $seccion->id_ciclo_lectivo);

            return redirect()
                ->route('student.inscripcion.index')
                ->with('success', "Te inscribiste correctamente en {$seccion->materia->nombre} ({$seccion->nombre_grupo}).");

        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first());
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'Ocurrió un error al procesar la inscripción.');
        }
    }

    protected function validacionGlobal($alumno, int $cicloLectivoId, ValidarPuedeInscribirseAction $action): array
    {
        $solvencia = app(ValidarSolvenciaAction::class)
            ->execute($alumno, $cicloLectivoId);

        return [
            'solvente' => $solvencia['solvente'],
            'motivos'  => $solvencia['motivos'],
        ];
    }
}