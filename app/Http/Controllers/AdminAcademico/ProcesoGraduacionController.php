<?php

namespace App\Http\Controllers\AdminAcademico;

use App\Actions\AdminAcademic\ActualizarEstadoProcesoGraduacionAction;
use App\Actions\AdminAcademic\RegistrarProcesoGraduacionAction;
use App\Enums\EstadoProcesoGraduacion;
use App\Enums\ModalidadGraduacion;
use App\Http\Controllers\Controller;
use App\Rules\Graduacion\RegistrarProcesoGraduacionRequest;
use App\Models\Academico\ProcesoGraduacion;
use App\Models\Docente\Docentes;
use App\Models\Estudiante\Alumnos;
use App\Models\Estudiante\RequisitosEgreso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Enums\TamanoGrupo;
use App\Models\Academico\GrupoGraduacion;
use App\Enums\ArancelesGraduacion;

class ProcesoGraduacionController extends Controller
{
    public function index(Request $request)
    {

        $filtros = fn ($q) => $q
            ->when($request->filled('estado'), fn ($q2) => $q2->where('estado_proceso', $request->estado))
            ->when($request->filled('modalidad'), fn ($q2) => $q2->where('modalidad', $request->modalidad));

        $procesosActivos = ProcesoGraduacion::with([
                'alumno.usuario',
                'alumno.carrera.facultad',
                'grupo.docenteAsesor.usuario',
            ])
            ->where('es_activo', true)
            ->tap($filtros)
            ->orderByRaw("CASE estado_proceso
                WHEN 'INSCRITO' THEN 1
                WHEN 'EN_DESARROLLO' THEN 2
                WHEN 'EN_DEFENSA' THEN 3
                WHEN 'APROBADO' THEN 4
                WHEN 'GRADUADO' THEN 5
                WHEN 'REPROBADO' THEN 6
                ELSE 7 END")
            ->paginate(10);

        $procesosHistoricos = ProcesoGraduacion::with([
            'alumno.usuario',
            'alumno.carrera.facultad',
            'grupo.docenteAsesor.usuario',
        ])
            ->where('es_activo', false)
            ->tap($filtros)
            ->orderByDesc('updated_at')
            ->paginate(10, ['*'], 'page_historicos');

        // ── Egresados sin proceso activo ──
        $egresadosSinProceso = Alumnos::with(['usuario', 'carrera'])
            ->whereHas('requisitosEgreso', fn ($q) => $q->where('estado_egreso', 'EGRESADO'))
            ->whereDoesntHave('procesosGraduacion', fn ($q) => $q->where('es_activo', true))
            ->whereDoesntHave('procesosGraduacion', fn ($q) => $q->where('estado_proceso', 'GRADUADO'))
            ->orderBy('codigo_estudiante')
            ->get();

        // ── Datos auxiliares ──
        $gruposDisponibles = GrupoGraduacion::with(['docenteAsesor.usuario'])
            ->where('estado_grupo', 'ACTIVO')
            ->has('procesos', '<', 3)
            ->withCount('procesos')
            ->get();

        $docentes = Docentes::with('usuario')->where('estado', 'ACTIVO')->get();

        $modalidades = ModalidadGraduacion::cases();
        $estados     = EstadoProcesoGraduacion::cases();

        return view('adminacademico.proceso_graduacion', compact(
            'procesosActivos',
            'procesosHistoricos',
            'egresadosSinProceso',
            'gruposDisponibles',
            'docentes',
            'modalidades',
            'estados',
        ));
    }

    public function store(RegistrarProcesoGraduacionRequest $request, int $alumnoId, RegistrarProcesoGraduacionAction $action) {
        $alumno = Alumnos::findOrFail($alumnoId);

        try {
            $action->execute($alumno, $request->validated(), $request->user());

            return redirect()
                ->route('admin-academico.graduacion.index')
                ->with('success', "Proceso de graduación registrado para {$alumno->codigo_estudiante}.");
        } catch (ValidationException $e) {
            return redirect()
                ->route('admin-academico.graduacion.index')
                ->with('error', collect($e->errors())->flatten()->first());
        }
    }

    public function cambiarEstado(Request $request, int $procesoId, ActualizarEstadoProcesoGraduacionAction $action ) {
        $data = $request->validate([
            'estado'                 => ['required', 'string'],
            'nota_defensa'           => ['nullable', 'numeric', 'between:0,10'],
            'id_pago_derechos_grado' => ['nullable', 'integer', 'exists:pago,pago_id'],
        ]);

        $proceso = ProcesoGraduacion::findOrFail($procesoId);

        try {
            if ($request->filled('id_pago_derechos_grado') && !$proceso->id_pago_derechos_grado) {
                $proceso->id_pago_derechos_grado = $data['id_pago_derechos_grado'];
                $proceso->save();
            }

            $action->execute(
                $proceso,
                EstadoProcesoGraduacion::from($data['estado']),
                $data['nota_defensa'] ?? null
            );

            return redirect()
                ->route('admin-academico.graduacion.index')
                ->with('success', 'Estado del proceso actualizado correctamente.');
        } catch (ValidationException $e) {
            return redirect()
                ->route('admin-academico.graduacion.index')
                ->with('error', collect($e->errors())->flatten()->first());
        }
    }

    /**
     * Obtiene los pagos disponibles para derechos de grado según la modalidad y nivel.
     */
    public function pagosDisponibles(int $alumnoId, string $modalidad)
    {
        $alumno = Alumnos::findOrFail($alumnoId);

        $nivel = DB::table('carreras as c')
            ->join('niveles_academicos as na', 'c.id_nivel_academico', '=', 'na.nivel_academico_id')
            ->where('c.carrera_id', $alumno->id_carrera)
            ->value('na.codigo');

        $codigo = ArancelesGraduacion::derechosGrado($nivel, $modalidad);

        $pagos = DB::table('pago as p')
            ->join('detalle_pago as dp', 'p.pago_id', '=', 'dp.id_pago')
            ->join('concepto_pago as cp', 'dp.id_concepto', '=', 'cp.concepto_pago_id')
            ->where('p.id_alumno', $alumno->alumno_id)
            ->where('cp.codigo_concepto', $codigo)
            ->select('p.pago_id', 'p.numero_recibo', 'p.fecha_pago', 'p.total')
            ->get();

        return response()->json(['pagos' => $pagos, 'codigo' => $codigo]);
    }

    public function historial(int $alumnoId)
    {
        $procesos = ProcesoGraduacion::with(['grupo'])
            ->where('id_alumno', $alumnoId)
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'procesos' => $procesos->map(fn ($p) => [
                'proceso_id'       => $p->proceso_id,
                'modalidad'        => $p->modalidad->label(),
                'modalidad_badge'  => $p->modalidad->badge(),
                'estado'           => $p->estado_proceso->label(),
                'estado_badge'     => $p->estado_proceso->badge(),
                'intentos'         => $p->intentos_defensa,
                'titulo'           => $p->grupo?->titulo_tema ?? $p->nombre_curso ?? '—',
                'nota'             => $p->nota_defensa,
                'fecha_defensa'    => $p->fecha_defensa?->format('d/m/Y') ?? '—',
                'fecha_graduacion' => $p->fecha_graduacion?->format('d/m/Y') ?? '—',
                'es_activo'        => $p->es_activo,
            ]),
        ]);
    }
}