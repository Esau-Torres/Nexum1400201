<?php

namespace App\Http\Controllers\AdminAcademico;

use App\Actions\AdminAcademic\RegistrarCambioCarreraAction;
use App\Enums\EstadoCiclo;
use App\Models\Academico\CicloLectivo;
use App\Models\Estudiante\Alumnos;
use App\Models\Estudiante\Carreras;
use App\Models\Estudiante\DetalleInscripcion;
use App\Http\Controllers\Controller;
use App\Models\Academico\CargoEstudiante;
use App\Models\Adfinanciero\DetallePago;
use App\Models\Adfinanciero\ConceptoPago;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Support\Collection;

class CambioCarreraController extends Controller
{
    public function __construct(
        protected RegistrarCambioCarreraAction $cambiarCarrera,
    ) {}

    /**
     * Formulario principal de cambio de carrera.
     * - Si viene ?alumno_id=X, precarga al alumno y sus datos.
     * - Permite búsqueda por documento/código/nombre.
     */
    public function index(Request $request): View
    {
        $alumnoSeleccionado = null;
        $motivo             = $request->input('motivo', 'VOLUNTARIO');
        $materiaReprobadaId = $request->input('materia_reprobada_id');
        $carreras           = collect();
        $materiasReprobadas = collect();
        $materiasEnCurso    = collect();
        $esGraduado         = false;
        $resumenFinanciero  = null;

        if ($request->filled('alumno_id')) {
            $alumnoSeleccionado = Alumnos::with([
                'usuario',
                'carrera.facultad',
                'documentos',
            ])->findOrFail($request->input('alumno_id'));

            $materiasEnCurso = $this->materiasEnCurso($alumnoSeleccionado);

            $esGraduado = $this->cambiarCarrera->esGraduado($alumnoSeleccionado);

            $materiasReprobadas = $this->obtenerMateriasReprobadasTresVeces($alumnoSeleccionado);

            $carreras = RegistrarCambioCarreraAction::carrerasPermitidas(
                $alumnoSeleccionado,
                $motivo,
                $materiaReprobadaId ? (int) $materiaReprobadaId : null
            );

            $resumenFinanciero = $this->resumenFinanciero($alumnoSeleccionado, $esGraduado);
        }

        // Listado/buscador de alumnos
        $alumnos = Alumnos::with('usuario', 'carrera')
            ->whereIn('estado_carrera', ['ACTIVO', 'GRADUADO'])
            ->when($request->filled('q'), function ($q) use ($request) {
                $q->whereHas('usuario', function ($sub) use ($request) {
                    $sub->where('name', 'ilike', "%{$request->q}%")
                        ->orWhere('documento_identidad', 'ilike', "%{$request->q}%");
                })->orWhere('codigo_estudiante', 'ilike', "%{$request->q}%");
            })
            ->limit(50)
            ->get();

        $cicloVigente = CicloLectivo::whereIn('estado', [
            EstadoCiclo::MATRICULA_ABIERTA,
            EstadoCiclo::ACTIVO,
        ])->orderByDesc('fecha_inicio')->first();

        return view('adminacademico.cambio-carrera', compact(
            'alumnos',
            'alumnoSeleccionado',
            'carreras',
            'materiasReprobadas',
            'materiasEnCurso',  
            'motivo',
            'materiaReprobadaId',
            'esGraduado',
            'resumenFinanciero',
            'cicloVigente',
        ));
    }

    /**
     * Procesa el cambio de carrera.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'alumno_id'            => ['required', 'exists:alumnos,alumno_id'],
            'nueva_carrera_id'     => ['required', 'exists:carreras,carrera_id'],
            'motivo'               => ['required', 'in:VOLUNTARIO,FORZOSO_REPROBACION'],
            'materia_reprobada_id' => ['nullable', 'exists:materias,materia_id', 'required_if:motivo,FORZOSO_REPROBACION'],
            'es_graduado'          => ['nullable', 'boolean'],
        ]);

        $alumno = Alumnos::findOrFail($validated['alumno_id']);

        try {
            $resultado = $this->cambiarCarrera->execute(
                alumno:             $alumno,
                nuevaCarreraId:     (int) $validated['nueva_carrera_id'],
                motivo:             $validated['motivo'],
                materiaReprobadaId: isset($validated['materia_reprobada_id']) ? (int) $validated['materia_reprobada_id'] : null,
                esGraduado:         (bool) ($validated['es_graduado'] ?? false),
            );

            return redirect()
                ->route('admin-academico.cambio-carrera', ['alumno_id' => $resultado['nuevo_alumno']->alumno_id])
                ->with('success', $resultado['message'] . ' Código nuevo: ' . $resultado['nuevo_alumno']->codigo_estudiante);
        } catch (\Throwable $e) {
            report($e);
            return back()->withInput()->with('error', 'No se pudo procesar el cambio: ' . $e->getMessage());
        }
    }
    
    /**
     * Materias reprobadas >= 3 veces.
     */
    private function obtenerMateriasReprobadasTresVeces(Alumnos $alumno)
    {
        $inscripciones = DetalleInscripcion::query()
            ->whereHas('inscripcion', function ($q) use ($alumno) {
                $q->where('id_alumno', $alumno->alumno_id);
            })
            ->where('estado_materia', 'REPROBADA')
            ->with('seccion.materia')
            ->get();

        return $inscripciones
            ->groupBy(fn ($d) => $d->seccion?->materia?->materia_id)
            ->map(function ($grupo) {
                $materia = $grupo->first()->seccion?->materia;
                return [
                    'materia_id'    => $materia?->materia_id,
                    'codigo'        => $materia?->codigo_materia,
                    'nombre'        => $materia?->nombre,
                    'reprobaciones' => $grupo->count(),
                ];
            })
            ->filter(fn ($m) => $m['reprobaciones'] >= 3)
            ->values();
    }

    /**
     * Resumen financiero del alumno para mostrar en la vista.
     */
    private function resumenFinanciero(Alumnos $alumno, bool $esGraduado): array
    {
        $ciclo = CicloLectivo::whereIn('estado', [
            EstadoCiclo::MATRICULA_ABIERTA,
            EstadoCiclo::ACTIVO,
            EstadoCiclo::CERRADO,
        ])->orderByDesc('fecha_inicio')->first();

        $queryCargos = CargoEstudiante::where('id_alumno', $alumno->alumno_id);

        if (!$esGraduado && $ciclo) {
            $queryCargos->where('id_ciclo_lectivo', $ciclo->ciclo_lectivo_id);
        }

        $cargos      = $queryCargos->get();
        $totalDeuda  = (float) $cargos->sum('monto_neto_estudiante');
        $totalPagado = (float) DetallePago::whereIn('id_cargo', $cargos->pluck('cargo_id'))
                            ->sum('subtotal');

        // Saldo nunca negativo
        $saldo = max(0, round($totalDeuda - $totalPagado, 2));

        // ---- Arancel TRA-005 ----
        $arancelInfo = $this->estadoArancelCambioCarrera($alumno, $cargos);

        return [
            'ciclo'            => $ciclo,
            'total_deuda'      => round($totalDeuda, 2),
            'total_pagado'     => round($totalPagado, 2),
            'saldo'            => $saldo,
            'solvente'         => $saldo <= 0.009,
            'arancel_pagado'   => $arancelInfo['pagado'],
            'arancel_pagado_monto' => $arancelInfo['pagado_monto'],
            'arancel_requerido'    => $arancelInfo['requerido'],
            'es_graduado'      => $esGraduado,
        ];
    }
    
    private function estadoArancelCambioCarrera(Alumnos $alumno, $cargos): array
    {
        $concepto = ConceptoPago::where('codigo_concepto', 'TRA-005')->first();

        if (!$concepto) {
            return ['pagado' => false, 'pagado_monto' => 0.0, 'requerido' => 0.0];
        }

        $cargoArancel = $cargos->firstWhere('id_concepto', $concepto->concepto_pago_id);

        $requerido = $cargoArancel
            ? (float) $cargoArancel->monto_neto_estudiante
            : (float) $concepto->monto_base;

        $pagado = 0.0;

        if ($cargoArancel) {
            $pagado += (float) DetallePago::where('id_cargo', $cargoArancel->cargo_id)
                ->sum('subtotal');
        }

        // Pagos directos por concepto (id_cargo NULL)
        $pagado += (float) DetallePago::where('id_concepto', $concepto->concepto_pago_id)
            ->whereNull('id_cargo')
            ->whereHas('pago', fn ($q) => $q->where('id_alumno', $alumno->alumno_id))
            ->sum('subtotal');

        return [
            'pagado'        => round($pagado, 2) >= round($requerido, 2),
            'pagado_monto'  => round($pagado, 2),
            'requerido'     => round($requerido, 2),
        ];
    }
    
    /**
     * Materias en curso del alumno en ciclos vigentes (para mostrar advertencia).
     */
    private function materiasEnCurso(Alumnos $alumno): Collection
    {
        $ciclosVigentes = CicloLectivo::whereIn('estado', [
            EstadoCiclo::ACTIVO,
            EstadoCiclo::MATRICULA_ABIERTA,
        ])->pluck('ciclo_lectivo_id');

        if ($ciclosVigentes->isEmpty()) {
            return collect();
        }

        return DetalleInscripcion::query()
            ->whereHas('inscripcion', function ($q) use ($alumno, $ciclosVigentes) {
                $q->where('id_alumno', $alumno->alumno_id)
                  ->whereIn('id_ciclo_lectivo', $ciclosVigentes);
            })
            ->whereIn('estado_materia', ['CURSANDO', 'INSCRITO', 'MATRICULADO', 'EN_CURSO'])
            ->with('seccion.materia')
            ->get();
    }
}