<?php

namespace App\Http\Controllers\AdminAcademico;

use App\Actions\AdminAcademic\ReassignStudentBenefitAction;
use App\Actions\AdminAcademic\RevokeStudentBenefitAction;
use App\Http\Controllers\Controller;
use App\Rules\ReassignBenefitRules;
use App\Rules\RevokeBenefitRules;
use App\Services\Beneficios\EvaluarReglasBeneficioService;
use App\Models\Academico\BeneficioEstudiante;
use App\Models\Estudiante\Alumnos;
use App\Models\Academico\CicloLectivo;
use App\Enums\EstadoBeneficio;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentBenefitController extends Controller
{
    /**
     * Listado de alumnos con beneficio + datos para los modales.
     * Una sola vista: modify-benefit-student.blade.php
     */
    public function index(Request $request, EvaluarReglasBeneficioService $evaluator): View
    {
        // 1. Beneficios activos con relaciones
        $beneficios = BeneficioEstudiante::query()
            ->where('estado', EstadoBeneficio::ACTIVO)
            ->with(['alumno.usuario', 'alumno.carrera.facultad', 'cicloLectivo'])
            ->orderByDesc('beneficio_id')
            ->paginate(12)
            ->withQueryString();

        // 2. Evaluación de cada beneficio
        $evaluaciones = $beneficios->mapWithKeys(function ($b) use ($evaluator) {
            return [$b->beneficio_id => $evaluator->execute($b->alumno, $b)];
        });

        // 3. Datos para el modal de reasignación
        $ciclosLectivos = CicloLectivo::orderByDesc('fecha_inicio')->get();

        return view('adminacademico.modify-benefit-student', compact(
            'beneficios',
            'evaluaciones',
            'ciclosLectivos'
        ));
    }

    public function revoke(
        RevokeBenefitRules $request,
        BeneficioEstudiante $beneficio,
        RevokeStudentBenefitAction $action
    ): RedirectResponse {
        try {
            $action->execute(
                beneficio: $beneficio,
                motivo: $request->validated()['motivo_revocacion'],
                admin: $request->user(),
                origen: 'MANUAL',
            );

            return redirect()
                ->route('admin-academico.admin-academico.beneficios.modify-benefit-student')
                ->with('success', 'Beneficio revocado y notificado al estudiante.');
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'No se pudo revocar: ' . $e->getMessage());
        }
    }

    public function reassign(
        ReassignBenefitRules $request,
        BeneficioEstudiante $beneficio,
        ReassignStudentBenefitAction $action
    ): RedirectResponse {
        try {
            $action->execute(
                alumno: $beneficio->alumno,
                beneficioAnterior: $beneficio,
                nuevoBeneficio: $request->validated(),
                admin: $request->user(),
                motivo: $request->validated()['motivo_cambio'] ?? 'Cambio de beneficio',
            );

            return redirect()
                ->route('admin-academico.admin-academico.beneficios.modify-benefit-student')
                ->with('success', 'Beneficio reasignado y notificado al estudiante.');
        } catch (\Throwable $e) {
            report($e);
            return back()->withInput()->with('error', 'No se pudo reasignar: ' . $e->getMessage());
        }
    }
}