<?php

namespace App\Http\Controllers\Adfinanciero;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Adfinanciero\ConceptoPago;
use App\Models\Adfinanciero\CicloLectivo;
use App\Models\Adfinanciero\CicloArancelPeriodo;

class AdminfinancieroController extends Controller
{

    // Método para mostrar la lista de conceptos de pago
    public function conceptosIndex(Request $request)
    {
        $query = \App\Models\Adfinanciero\ConceptoPago::query();

        if ($request->filled('categoria')) {
            $query->where('categoria', $request->categoria);
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                // Si usas MySQL en local, cambia 'ilike' por 'like'
                $q->where('nombre', 'ilike', '%' . $request->search . '%')
                  ->orWhere('codigo_concepto', 'ilike', '%' . $request->search . '%');
            });
        }

        $conceptos = $query->paginate(10)->appends($request->all());

        $categoriasUnicas = \App\Models\Adfinanciero\ConceptoPago::select('categoria')->distinct()->pluck('categoria');

        return view('adfinanciero.conceptospagos.index', compact('conceptos', 'categoriasUnicas'));
    }

    public function update(Request $request, $id)
    {
        // 1. Buscamos el concepto en la base de datos por su ID
        $concepto = \App\Models\Adfinanciero\ConceptoPago::findOrFail($id);

        // 2. Actualizamos con los 7 campos que vienen del modal
        $concepto->update([
            'codigo_concepto' => $request->codigo_concepto,
            'nombre' => $request->nombre,
            'categoria' => $request->categoria,
            'monto_base' => $request->monto_base,
            'aplica_descuento_estudiante' => $request->aplica_descuento_estudiante,
            'es_recurrente' => $request->es_recurrente,
            'estado' => $request->estado,
        ]);

        // 3. Redirigimos de vuelta a la misma página
        return redirect()->back();
    }

    public function store(Request $request)
    {
        // 1. Validar que el código de concepto no esté repetido
        $request->validate([
            // unique:tabla,columna
            'codigo_concepto' => 'required|unique:concepto_pago,codigo_concepto',
            'nombre' => 'required',
            'categoria' => 'required',
            'monto_base' => 'required|numeric',
        ], [
            // 2. Personalizamos el mensaje de error para que sea amigable
            'codigo_concepto.unique' => 'El código ingresado ya está en uso. Por favor, utiliza uno diferente.'
        ]);

        // 3. Si pasa la validación, procedemos a crear el registro
        \App\Models\Adfinanciero\ConceptoPago::create([
            'codigo_concepto' => $request->codigo_concepto,
            'nombre' => $request->nombre,
            'categoria' => $request->categoria,
            'monto_base' => $request->monto_base,
            'aplica_descuento_estudiante' => $request->aplica_descuento_estudiante,
            'es_recurrente' => $request->es_recurrente,
            'estado' => $request->estado,
        ]);

        // 4. Redirigimos de vuelta con un mensaje de éxito
        return redirect()->back()->with('success', 'Concepto creado exitosamente.');
    }

    //ciclos y aranceles
    public function ciclosIndex(Request $request)
    {
        $query = \App\Models\Adfinanciero\CicloArancelPeriodo::with(['ciclo', 'recargo']);

        // 1. Filtro Desplegable por Ciclo Exacto
        if ($request->filled('ciclo_filter')) {
            $query->where('id_ciclo_lectivo', $request->ciclo_filter);
        }

        // 2. Buscador Reactivo Expandido (Busca ciclo O código/nombre del concepto)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                // Busca en el nombre del ciclo
                $q->whereHas('ciclo', function($qCiclo) use ($search) {
                    $qCiclo->where('nombre_ciclo', 'ilike', '%' . $search . '%');
                })
                // O busca en el código/nombre del recargo
                ->orWhereHas('recargo', function($qRecargo) use ($search) {
                    $qRecargo->where('nombre', 'ilike', '%' . $search . '%')
                             ->orWhere('codigo_concepto', 'ilike', '%' . $search . '%');
                });
            });
        }

        // 3. Ordenamiento Lógico: Por fecha de inicio más antigua primero
        $query->orderBy('fecha_inicio_ordinario', 'asc');

        // Paginación
        $periodos = $query->paginate(5)->appends($request->all());

        $ciclos = \App\Models\Adfinanciero\CicloLectivo::all();
        $conceptos = \App\Models\Adfinanciero\ConceptoPago::where('estado', true)->get();

        return view('adfinanciero.ciclosarancel.index', compact('periodos', 'ciclos', 'conceptos'));
    }

    public function ciclosStore(Request $request)
    {
        $request->validate([
            'id_ciclo_lectivo' => 'required|exists:App\Models\Adfinanciero\CicloLectivo,ciclo_lectivo_id',
            'fecha_inicio_ordinario' => 'required|date',
            'fecha_fin_ordinario' => 'required|date|after_or_equal:fecha_inicio_ordinario',
            // La mora debe empezar después de que termina el periodo ordinario
            'fecha_inicio_extraordinario' => 'required|date|after:fecha_fin_ordinario',
            'fecha_fin_extraordinario' => 'required|date|after_or_equal:fecha_inicio_extraordinario',
            'id_concepto_recargo_extra' => 'required|exists:App\Models\Adfinanciero\ConceptoPago,concepto_pago_id'
        ], [
            'id_ciclo_lectivo.unique' => 'Este ciclo ya tiene aranceles configurados. Edite el registro existente.',
            'fecha_inicio_extraordinario.after' => 'El periodo extraordinario debe iniciar después de que finalice el ordinario.'
        ]);

        \App\Models\Adfinanciero\CicloArancelPeriodo::create($request->all());

        return redirect()->back()->with('success', 'Periodo configurado exitosamente.');
    }

    public function ciclosUpdate(Request $request, $id)
    {
        $periodo = \App\Models\Adfinanciero\CicloArancelPeriodo::findOrFail($id);

        $request->validate([
            'id_ciclo_lectivo' => 'required|exists:App\Models\Adfinanciero\CicloLectivo,ciclo_lectivo_id',
            'fecha_inicio_ordinario' => 'required|date',
            'fecha_fin_ordinario' => 'required|date|after_or_equal:fecha_inicio_ordinario',
            'fecha_inicio_extraordinario' => 'required|date|after:fecha_fin_ordinario',
            'fecha_fin_extraordinario' => 'required|date|after_or_equal:fecha_inicio_extraordinario',
            'id_concepto_recargo_extra' => 'required|exists:concepto_pago,concepto_pago_id'
        ]);

        $periodo->update($request->all());

        return redirect()->back()->with('success', 'Periodo actualizado exitosamente.');
    }

    //promociones
    public function promocionesIndex(Request $request)
    {
        // Traemos las promociones junto con su concepto relacionado
        $query = \App\Models\Adfinanciero\Promocion::with('concepto');

        if ($request->filled('search')) {
            $query->where('nombre', 'ilike', '%' . $request->search . '%');
        }

        if ($request->filled('estado_filter')) {
            $query->where('estado', $request->estado_filter === 'ACTIVO' ? true : false);
        }

        $query->orderBy('fecha_fin', 'desc');

        $promociones = $query->paginate(10)->appends($request->all());

        // Obtenemos los conceptos activos para llenar el select del modal
        $conceptos = \App\Models\Adfinanciero\ConceptoPago::where('estado', true)->get();

        return view('adfinanciero.promociones.index', compact('promociones', 'conceptos'));
    }

    public function promocionesStore(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:150',
            'porcentaje_descuento' => 'required|numeric|min:0.01|max:100',
            'id_concepto_aplicable' => 'required|exists:App\Models\Adfinanciero\ConceptoPago,concepto_pago_id',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'requiere_solvencia_hasta' => 'nullable|date',
            'estado' => 'required|boolean'
        ]);

        \App\Models\Adfinanciero\Promocion::create($request->all());

        return redirect()->back()->with('success', 'Promoción registrada exitosamente.');
    }

    public function promocionesUpdate(Request $request, $id)
    {
        $promocion = \App\Models\Adfinanciero\Promocion::findOrFail($id);

        $request->validate([
            'nombre' => 'required|string|max:150',
            'porcentaje_descuento' => 'required|numeric|min:0.01|max:100',
            'id_concepto_aplicable' => 'required|exists:App\Models\Adfinanciero\ConceptoPago,concepto_pago_id',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'requiere_solvencia_hasta' => 'nullable|date',
            'estado' => 'required|boolean'
        ]);

        $promocion->update($request->all());

        return redirect()->back()->with('success', 'Promoción actualizada exitosamente.');
    }

    public function cargosBatch(Request $request)
    {
        $request->validate([
            'id_ciclo_lectivo' => 'required|exists:App\Models\Adfinanciero\CicloLectivo,ciclo_lectivo_id',
            'id_concepto' => 'required|exists:App\Models\Adfinanciero\ConceptoPago,concepto_pago_id',
            'mes_arancel' => 'required|integer|min:1|max:12',
            'anio_arancel' => 'required|integer|min:2020|max:2100',
            'fecha_vencimiento' => 'required|date'
        ]);

        $concepto = \App\Models\Adfinanciero\ConceptoPago::findOrFail($request->id_concepto);

        // Simulación: Array de IDs numéricos de alumnos (ya no carnets en texto)
        $estudiantes_matriculados = [1001, 1002, 1003];

        $cargosGenerados = 0;

        foreach ($estudiantes_matriculados as $id_alumno) {
            $existe = \App\Models\Adfinanciero\CargoEstudiante::where('id_alumno', $id_alumno)
                ->where('id_ciclo_lectivo', $request->id_ciclo_lectivo)
                ->where('id_concepto', $request->id_concepto)
                ->where('mes_arancel', $request->mes_arancel)
                ->where('anio_arancel', $request->anio_arancel)
                ->exists();

            if (!$existe) {
                \App\Models\Adfinanciero\CargoEstudiante::create([
                    'id_alumno' => $id_alumno,
                    'id_concepto' => $concepto->concepto_pago_id,
                    'id_ciclo_lectivo' => $request->id_ciclo_lectivo,
                    'mes_arancel' => $request->mes_arancel,
                    'anio_arancel' => $request->anio_arancel,
                    'monto_original' => $concepto->monto,
                    'fecha_emision' => now()->format('Y-m-d'),
                    'fecha_vencimiento' => $request->fecha_vencimiento,
                    'estado_cargo' => 'PENDIENTE'
                ]);
                $cargosGenerados++;
            }
        }

        return redirect()->back()->with('success', "Proceso Batch completado: Se generaron $cargosGenerados cargos exitosamente.");
    }

    public function cargosUpdateEstado(Request $request, $id)
    {
        $request->validate(['estado_cargo' => 'required|in:PENDIENTE,PAGADO,ANULADO']);

        $cargo = \App\Models\Adfinanciero\CargoEstudiante::findOrFail($id);
        $cargo->update(['estado_cargo' => $request->estado_cargo]);

        return redirect()->back()->with('success', 'Estado del cargo auditado/actualizado exitosamente.');
    }

    // pagos y reportes
    public function reportesIndex(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio', now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->input('fecha_fin', now()->endOfMonth()->format('Y-m-d'));

        // 1. MÉTRICAS GLOBALES (Sin filtro de estado, ya que no existe en la BD)
        $pagosQuery = \App\Models\Adfinanciero\Pago::whereBetween('fecha_pago', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59']);

        $totalIngresos = (clone $pagosQuery)->sum('total');
        $cantidadTransacciones = (clone $pagosQuery)->count();

        // 2. ANÁLISIS DE INGRESOS POR CONCEPTO
        $ingresosPorConcepto = \Illuminate\Support\Facades\DB::table('detalle_pago')
            ->join('pago', 'detalle_pago.id_pago', '=', 'pago.pago_id')
            ->join('concepto_pago', 'detalle_pago.id_concepto', '=', 'concepto_pago.concepto_pago_id')
            ->whereBetween('pago.fecha_pago', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
            ->select('concepto_pago.nombre', \Illuminate\Support\Facades\DB::raw('SUM(detalle_pago.subtotal) as total_recaudado'))
            ->groupBy('concepto_pago.nombre')
            ->orderByDesc('total_recaudado')
            ->get();

        // 3. HISTORIAL DE CONCILIACIÓN
        $pagos = \App\Models\Adfinanciero\Pago::with('detalles.concepto')
                                              ->whereBetween('fecha_pago', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
                                              ->orderBy('fecha_pago', 'desc')
                                              ->paginate(15)->appends($request->all());

        return view('adfinanciero.reportes.index', compact(
            'totalIngresos',
            'cantidadTransacciones',
            'ingresosPorConcepto',
            'pagos',
            'fechaInicio',
            'fechaFin'
        ));
    }

    //reglas de cobro
    public function reglasIndex(Request $request)
    {
        // 1. Buscamos si existe algún ciclo activo
        $cicloActivo = \App\Models\Adfinanciero\CicloLectivo::where('estado', 'ACTIVO')->first();

        // El estado que DEBERÍAN tener todas las reglas
        $estadoCorrecto = $cicloActivo ? 'ACTIVO' : 'INACTIVO';

        // =========================================================
        // 2. MOTOR DE AUTO-SINCRONIZACIÓN (UNO POR UNO)
        // Evita los bloqueos de Mass Assignment y fuerza la escritura
        // =========================================================
        $todasLasReglas = \App\Models\Adfinanciero\ReglaPago::all();

        foreach ($todasLasReglas as $regla) {
            if ($regla->estado !== $estadoCorrecto) {
                // Forzamos el cambio
                $regla->estado = $estadoCorrecto;
                $regla->save();
            }
        }

        // 3. Volvemos a traer las reglas ya actualizadas con sus relaciones
        $reglas = \App\Models\Adfinanciero\ReglaPago::with('conceptoRecargo')->get();
        $conceptos = \App\Models\Adfinanciero\ConceptoPago::where('estado', 'ACTIVO')->get();

        return view('adfinanciero.reglas.index', compact('reglas', 'conceptos', 'cicloActivo'));
    }

    public function reglasStore(Request $request)
    {
        $request->validate([
            'tipo' => 'required|string|max:255',
            'meses_aplicables' => 'required|string|max:30',
            'dia_inicio_ordinario' => 'required|integer|min:1|max:31',
            'dia_fin_ordinario' => 'required|integer|min:1|max:31|gte:dia_inicio_ordinario',
            'dia_inicio_extra' => 'nullable|integer|min:1|max:31',
            'dia_fin_extra' => 'nullable|integer|min:1|max:31|gte:dia_inicio_extra',
            // Validamos contra tu tabla de conceptos usando tu namespace
            'id_concepto_recargo' => 'nullable|exists:App\Models\Adfinanciero\ConceptoPago,concepto_pago_id',
            'estado' => 'required|in:ACTIVO,INACTIVO'
        ]);

        \App\Models\Adfinanciero\ReglaPago::create($request->all());

        // Usamos back() para mantener la consistencia con tus otros métodos store
        return redirect()->back()->with('success', 'Regla de cobro creada exitosamente.');
    }

    public function reglasUpdate(Request $request, $id)
    {
        $regla = \App\Models\Adfinanciero\ReglaPago::findOrFail($id);

        $request->validate([
            'tipo' => 'required|string|max:255',
            'meses_aplicables' => 'required|string|max:30',
            'dia_inicio_ordinario' => 'required|integer|min:1|max:31',
            'dia_fin_ordinario' => 'required|integer|min:1|max:31|gte:dia_inicio_ordinario',
            'dia_inicio_extra' => 'nullable|integer|min:1|max:31',
            'dia_fin_extra' => 'nullable|integer|min:1|max:31|gte:dia_inicio_extra',
            'id_concepto_recargo' => 'nullable|exists:App\Models\Adfinanciero\ConceptoPago,concepto_pago_id',
            'estado' => 'required|in:ACTIVO,INACTIVO'
        ]);

        $regla->update($request->all());

        return redirect()->back()->with('success', 'Regla actualizada correctamente.');
    }

    //para generar las mensualidades
    public function cargosCreate(Request $request)
    {
        $cicloActivo = \Illuminate\Support\Facades\DB::table('ciclo_lectivo')
            ->whereRaw("estado::text IN ('CONFIGURACION', 'MATRICULA_ABIERTA', 'ACTIVO')")
            ->orderBy('ciclo_lectivo_id', 'desc')
            ->first();

        $search = $request->input('search');

        $query = \Illuminate\Support\Facades\DB::table('alumnos')
            ->join('carreras', 'alumnos.id_carrera', '=', 'carreras.carrera_id')
            ->join('users', 'alumnos.id_usuario', '=', 'users.id')
            ->leftJoin('beneficios_estudiante', 'alumnos.alumno_id', '=', 'beneficios_estudiante.id_alumno')
            ->select(
                'alumnos.alumno_id',
                'alumnos.codigo_estudiante',
                'carreras.nombre as nombre_carrera',
                'beneficios_estudiante.tipo_beneficio',
                'users.name',
                'users.documento_identidad'
            )
            ->where('alumnos.estado_carrera', 'ACTIVO');

        // ==========================================================
        // CANDADO DE SEGURIDAD Y FILTROS FINANCIEROS
        // ==========================================================
        if (!$cicloActivo) {
            // Forzamos a que la consulta no devuelva nada (vacía la tabla)
            $query->whereRaw('1 = 0');
        } else {
            // REGLA 1: Excluir alumnos que ya tienen cargos en ESTE ciclo
            $query->whereNotExists(function($q) use ($cicloActivo) {
                $q->select(\Illuminate\Support\Facades\DB::raw(1))
                ->from('cargo_estudiante')
                ->whereColumn('cargo_estudiante.id_alumno', 'alumnos.alumno_id')
                ->where('cargo_estudiante.id_ciclo_lectivo', $cicloActivo->ciclo_lectivo_id);
            });

            // REGLA 2: Excluir alumnos MOROSOS de ciclos ANTERIORES
            $query->whereNotExists(function($q) use ($cicloActivo) {
                $q->select(\Illuminate\Support\Facades\DB::raw(1))
                ->from('cargo_estudiante')
                ->whereColumn('cargo_estudiante.id_alumno', 'alumnos.alumno_id')
                ->where('cargo_estudiante.id_ciclo_lectivo', '!=', $cicloActivo->ciclo_lectivo_id) // Que el ciclo sea distinto al actual
                ->where('cargo_estudiante.estado_cargo', 'PENDIENTE'); // Y que la deuda siga pendiente
            });

            $this->aplicarCandadoAcademicoCargos($query);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('alumnos.codigo_estudiante', 'ILIKE', "%{$search}%")
                  ->orWhere('users.name', 'ILIKE', "%{$search}%")
                  ->orWhere('users.documento_identidad', 'ILIKE', "%{$search}%");
            });
        }

        $estudiantes = $query->orderBy('users.name')->paginate(10);

        return view('adfinanciero.cargos.create', compact('estudiantes', 'cicloActivo', 'search'));
    }

    // 2. FUNCIÓN PARA GUARDAR Y CALCULAR EL BENEFICIO
    public function cargosStore(Request $request)
    {
        $request->validate([
            'alumno_id' => 'required|integer',
        ]);

        try {
            // 1. BUSCAMOS AL ESTUDIANTE (Con su carrera) Y EL CICLO ACTIVO
            $alumno = DB::table('alumnos')
                        ->join('carreras', 'alumnos.id_carrera', '=', 'carreras.carrera_id')
                        ->select('alumnos.*', 'carreras.nombre as nombre_carrera')
                        ->where('alumno_id', $request->alumno_id)
                        ->first();

            $cicloActivo = DB::table('ciclo_lectivo')->where('estado', 'CONFIGURACION')->first();

            if (!$alumno || !$cicloActivo) {
                return redirect()->back()->with('error', 'Error: No se encontró al estudiante o no hay un ciclo activo.');
            }

            // CANDADO DE SEGURIDAD: Estado del alumno, egreso y materias pendientes
            if ($alumno->estado_carrera !== 'ACTIVO') {
                return redirect()->back()->with('error', 'No se pueden generar cargos: el estudiante no está en estado ACTIVO.');
            }

            $esEgresado = DB::table('requisitos_egreso')
                ->where('id_alumno', $alumno->alumno_id)
                ->where('estado_egreso', 'EGRESADO')
                ->exists();

            if ($esEgresado) {
                return redirect()->back()->with('error', 'No se pueden generar cargos: el estudiante ya está registrado como EGRESADO.');
            }

            $tieneMateriasPendientes = DB::table('pensum as p')
                ->join('pensum_materia as pm', 'pm.id_pensum', '=', 'p.pensum_id')
                ->where('p.id_carrera', $alumno->id_carrera)
                ->where('p.estado', 'ACTIVO')
                ->whereNotExists(function ($sub) use ($alumno) {
                    $sub->select(DB::raw(1))
                        ->from('inscripciones as i')
                        ->join('detalle_inscripcion as di', 'di.id_inscripcion', '=', 'i.inscripcion_id')
                        ->join('seccion as s', 's.seccion_id', '=', 'di.id_seccion')
                        ->where('i.id_alumno', $alumno->alumno_id)
                        ->whereColumn('s.id_materia', 'pm.id_materia')
                        ->where('di.estado_materia', 'APROBADA');
                })
                ->exists();

            if (!$tieneMateriasPendientes) {
                return redirect()->back()->with('error', 'No se pueden generar cargos: el estudiante ya aprobó todas las materias de su pensum.');
            }

            // 2. OBTENEMOS LOS ARANCELES BASE (Incluyendo el Laboratorio)
            $conceptoMatricula = DB::table('concepto_pago')->where('nombre', 'ILIKE', '%MATRÍCULA%')->orWhere('nombre', 'ILIKE', '%MATRICULA%')->first();
            $conceptoCuota = DB::table('concepto_pago')->where('nombre', 'ILIKE', '%CUOTA%')->first();
            $conceptoLaboratorio = DB::table('concepto_pago')->where('nombre', 'ILIKE', '%LABORATORIO%')->first();

            if (!$conceptoMatricula || !$conceptoCuota) {
                return redirect()->back()->with('error', 'Error: No se encontraron los conceptos base (Matrícula/Cuota).');
            }

            // 3. PREPARAMOS LOS MESES
            $esCiclo01 = str_contains($cicloActivo->nombre_ciclo, '01') || $cicloActivo->numero_ciclo == '01';
            $mesesGenerar = $esCiclo01
                ? [1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio']
                : [7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'];

            // 4. LECTURA DINÁMICA DEL BENEFICIO
            $beneficio = DB::table('beneficios_estudiante')
                ->where('id_alumno', $alumno->alumno_id)
                ->where('estado', 'ACTIVO')
                ->first();

            $idBeneficio = $beneficio->beneficio_id ?? null;
            $tipoBeneficio = $beneficio ? strtolower(trim($beneficio->tipo_beneficio)) : null;
            $porcentajeEstudiante = $beneficio->porcentaje_estudiante ?? 100;
            $montoFijoCuota = $beneficio->monto_fijo_cuota ?? 0;

            // Leemos tus columnas booleanas de la base de datos
            $cubreMatricula = $beneficio->incluye_matricula ?? false;
            $cubreLaboratorio = $beneficio->incluye_laboratorio ?? false;

            // 2. OBTENEMOS LOS ARANCELES BASE
            $conceptoMatricula = DB::table('concepto_pago')->where('nombre', 'ILIKE', '%MATRÍCULA%')->orWhere('nombre', 'ILIKE', '%MATRICULA%')->first();
            $conceptoCuota = DB::table('concepto_pago')->where('nombre', 'ILIKE', '%CUOTA%')->first();

            if (!$conceptoMatricula || !$conceptoCuota) {
                return redirect()->back()->with('error', 'Error: No se encontraron los conceptos base.');
            }

            // 3. PREPARAMOS LOS MESES
            $esCiclo01 = str_contains($cicloActivo->nombre_ciclo, '01') || $cicloActivo->numero_ciclo == '01';
            $mesesGenerar = $esCiclo01
                ? [1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio']
                : [7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'];

            // 4. LECTURA DINÁMICA DEL BENEFICIO (Solo los activos)
            $beneficio = DB::table('beneficios_estudiante')
                ->where('id_alumno', $alumno->alumno_id)
                ->where('estado', 'ACTIVO') // Filtro vital de seguridad
                ->first();

            // Usamos las columnas exactas de tus imágenes
            $idBeneficio = $beneficio->beneficio_id ?? null;
            $tipoBeneficio = $beneficio ? strtolower(trim($beneficio->tipo_beneficio)) : null;
            $porcentajeEstudiante = $beneficio->porcentaje_estudiante ?? 100;
            $montoFijoCuota = $beneficio->monto_fijo_cuota ?? 0;
            $cubreMatricula = $beneficio->incluye_matricula ?? false;


            // 5. SUPER FUNCIÓN MATEMÁTICA DINÁMICA (Con validación de Laboratorio)
            $calcularMonto = function($montoOriginal, $esMatricula, $esLaboratorio = false) use ($tipoBeneficio, $porcentajeEstudiante, $montoFijoCuota, $cubreMatricula, $cubreLaboratorio) {

                // Regla A: Matrícula
                if ($esMatricula && !$cubreMatricula) {
                    return ['neto' => $montoOriginal, 'absorbido' => 0];
                }

                // Regla B: LABORATORIO DE INFORMÁTICA
                if ($esLaboratorio) {
                    // SOLO se exonera si es Beca Completa Y el check en la BD es true
                    if ($tipoBeneficio === 'beca_completa' && $cubreLaboratorio) {
                        return ['neto' => 0.00, 'absorbido' => $montoOriginal];
                    } else {
                        // Beca Parcial, Tarifa Regular o Beca Completa sin el check, pagan completo
                        return ['neto' => $montoOriginal, 'absorbido' => 0];
                    }
                }

                // Regla C: Cuotas Mensuales
                $neto = $montoOriginal;
                $absorbido = 0;

                if ($tipoBeneficio) {
                    switch ($tipoBeneficio) {
                        case 'beca_completa':
                            $neto = 0.00;
                            $absorbido = $montoOriginal;
                            break;

                        case 'beca_parcial':
                            // Se basa estrictamente en el porcentaje (ej. 50.00%)
                            $neto = $montoOriginal * ($porcentajeEstudiante / 100);
                            $absorbido = $montoOriginal - $neto;
                            break;

                        case 'franja_becaria':
                        case 'cuota_especial':
                            // Se basa estrictamente en el monto fijo (ej. $53.00 o $61.83)
                            $neto = $montoFijoCuota;
                            $absorbido = $montoOriginal - $neto;
                            break;
                    }
                }
                return ['neto' => max(0, $neto), 'absorbido' => max(0, $absorbido)];
            };

            $cargosAGuardar = [];
            $fechaEmision = now()->toDateString();
            $anio = $cicloActivo->anio;

            // 6. CREAMOS LA MATRÍCULA (true = es matrícula)
            $calculoMatricula = $calcularMonto($conceptoMatricula->monto_base, true);
            $primerMesNum = array_key_first($mesesGenerar);

            $cargosAGuardar[] = [
                'id_alumno' => $alumno->alumno_id,
                'id_concepto' => $conceptoMatricula->concepto_pago_id,
                'id_ciclo_lectivo' => $cicloActivo->ciclo_lectivo_id,
                'mes_arancel' => $mesesGenerar[$primerMesNum],
                'anio_arancel' => $anio,
                'monto_original' => $conceptoMatricula->monto_base,
                'fecha_emision' => $fechaEmision,
                'fecha_vencimiento' => \Carbon\Carbon::create($anio, $primerMesNum, 18)->toDateString(),
                'estado_cargo' => ($calculoMatricula['neto'] == 0) ? 'PAGADO' : 'PENDIENTE',
                'id_beneficio' => $idBeneficio,
                'monto_absorbido_universidad' => $calculoMatricula['absorbido'],
                'monto_neto_estudiante' => $calculoMatricula['neto']
            ];

            // ====================================================================
            // 6.5 CREAMOS EL LABORATORIO DE INFORMÁTICA
            // ====================================================================
            // REGLA ESTRICTA: Solo se genera si tiene la casilla 'incluye_laboratorio' marcada
            if ($cubreLaboratorio && isset($conceptoLaboratorio) && $conceptoLaboratorio) {

                // Le enviamos "true" en el tercer parámetro para que la matemática sepa que es el Laboratorio
                $calculoLab = $calcularMonto($conceptoLaboratorio->monto_base, false, true);

                $cargosAGuardar[] = [
                    'id_alumno' => $alumno->alumno_id,
                    'id_concepto' => $conceptoLaboratorio->concepto_pago_id,
                    'id_ciclo_lectivo' => $cicloActivo->ciclo_lectivo_id,
                    'mes_arancel' => $mesesGenerar[$primerMesNum], // Se cobra junto con el inicio del ciclo
                    'anio_arancel' => $anio,
                    'monto_original' => $conceptoLaboratorio->monto_base,
                    'fecha_emision' => $fechaEmision,
                    'fecha_vencimiento' => \Carbon\Carbon::create($anio, $primerMesNum, 18)->toDateString(),

                    // Nace PAGADO si el neto de la fórmula dio $0.00
                    'estado_cargo' => ($calculoLab['neto'] == 0) ? 'PAGADO' : 'PENDIENTE',

                    'id_beneficio' => $idBeneficio,
                    'monto_absorbido_universidad' => $calculoLab['absorbido'],
                    'monto_neto_estudiante' => $calculoLab['neto']
                ];
            }

            // 7. CREAMOS LAS 6 CUOTAS (false = es cuota)
            $calculoCuota = $calcularMonto($conceptoCuota->monto_base, false);

            foreach ($mesesGenerar as $numMes => $nombreMes) {
                $cargosAGuardar[] = [
                    'id_alumno' => $alumno->alumno_id,
                    'id_concepto' => $conceptoCuota->concepto_pago_id,
                    'id_ciclo_lectivo' => $cicloActivo->ciclo_lectivo_id,
                    'mes_arancel' => $nombreMes,
                    'anio_arancel' => $anio,
                    'monto_original' => $conceptoCuota->monto_base,
                    'fecha_emision' => $fechaEmision,
                    'fecha_vencimiento' => \Carbon\Carbon::create($anio, $numMes, 10)->toDateString(),
                    'estado_cargo' => ($calculoCuota['neto'] == 0) ? 'PAGADO' : 'PENDIENTE',
                    'id_beneficio' => $idBeneficio,
                    'monto_absorbido_universidad' => $calculoCuota['absorbido'],
                    'monto_neto_estudiante' => $calculoCuota['neto']
                ];
            }

            // ========================================================================
            // 8. INSERCIÓN Y GENERACIÓN DE RECIBO AUTOMÁTICO (NIVEL AUDITORÍA)
            // ========================================================================
            DB::beginTransaction();
            try {
                // Verificamos si es un estudiante con Beca Completa para generarle su recibo
                $esBecaCompleta = ($tipoBeneficio === 'beca_completa');
                $pagoId = null;

                if ($esBecaCompleta) {
                    // 8.1 Creamos el "Encabezado" del recibo a costo $0.00
                    $numeroRecibo = 'REC-BECA-' . $anio . '-' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);

                    // CORRECCIÓN: Le decimos a Laravel que nos devuelva 'pago_id' en lugar de 'id'
                    $pagoId = DB::table('pago')->insertGetId([
                        'id_alumno' => $alumno->alumno_id,
                        'fecha_pago' => $fechaEmision,
                        'total' => 0.00,
                        'numero_recibo' => $numeroRecibo,
                        'canal_pago' => 'SISTEMA',
                        'metodo_pago' => 'EXONERACION'
                    ], 'pago_id');
                }

                // 8.2 Guardamos los cargos (Matrícula y Cuotas) uno por uno
                foreach ($cargosAGuardar as $cargoData) {

                    // CORRECCIÓN: Le decimos a Laravel que nos devuelva 'cargo_id' en lugar de 'id'
                    $cargoId = DB::table('cargo_estudiante')->insertGetId($cargoData, 'cargo_id');

                    // 8.3 Si es beca completa y el cargo nació PAGADO, creamos el detalle del recibo
                    if ($esBecaCompleta && $cargoData['estado_cargo'] === 'PAGADO') {
                        DB::table('detalle_pago')->insert([
                            'id_pago' => $pagoId,
                            'id_cargo' => $cargoId,
                            'id_concepto' => $cargoData['id_concepto'],
                            'monto_base' => $cargoData['monto_original'],
                            'descuento_aplicado' => $cargoData['monto_absorbido_universidad'],
                            'recargo_aplicado' => 0.00,
                            'cantidad' => 1,
                            'subtotal' => 0.00 // Neto es $0.00
                        ]);
                    }
                }

                // Confirmamos que todo se guardó perfectamente
                DB::commit();

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e; // Re-lanzamos el error para que tu 'catch' principal lo atrape y muestre la alerta
            }

            return redirect()->route('adfinanciero.cargos.create')
                ->with('success', '¡Cargos generados exitosamente aplicando las políticas de beneficios dinámicas!');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Ocurrió un error: ' . $e->getMessage());
        }
    }

    // NUEVA FUNCIÓN: GENERACIÓN MASIVA PARA TODOS LOS ESTUDIANTES PENDIENTES
    public function cargosStoreMasivo(Request $request)
    {
        try {
            $cicloActivo = \Illuminate\Support\Facades\DB::table('ciclo_lectivo')->where('estado', 'CONFIGURACION')->first();

            if (!$cicloActivo) {
                return redirect()->back()->with('error', 'Error: No hay ciclo activo.');
            }

            // 1. OBTENEMOS TODOS LOS ALUMNOS QUE NO TENGAN CARGOS EN ESTE CICLO Y NO DEBAN DE CICLOS ANTERIORES
            $alumnos = \Illuminate\Support\Facades\DB::table('alumnos')
                ->join('carreras', 'alumnos.id_carrera', '=', 'carreras.carrera_id')
                ->select('alumnos.*', 'carreras.nombre as nombre_carrera')
                ->where('alumnos.estado_carrera', 'ACTIVO')

                // REGLA 1: Que no tengan ya cargos en este ciclo
                ->whereNotExists(function($q) use ($cicloActivo) {
                    $q->select(\Illuminate\Support\Facades\DB::raw(1))
                    ->from('cargo_estudiante')
                    ->whereColumn('cargo_estudiante.id_alumno', 'alumnos.alumno_id')
                    ->where('cargo_estudiante.id_ciclo_lectivo', $cicloActivo->ciclo_lectivo_id);
                })

                // REGLA 2: Excluir alumnos MOROSOS de ciclos ANTERIORES
                ->whereNotExists(function($q) use ($cicloActivo) {
                    $q->select(\Illuminate\Support\Facades\DB::raw(1))
                    ->from('cargo_estudiante')
                    ->whereColumn('cargo_estudiante.id_alumno', 'alumnos.alumno_id')
                    ->where('cargo_estudiante.id_ciclo_lectivo', '!=', $cicloActivo->ciclo_lectivo_id)
                    ->where('cargo_estudiante.estado_cargo', 'PENDIENTE');
                })
                ->get();

            // REGLAS 3 y 4: Excluir egresados y alumnos con pensum 100% aprobado
            $this->aplicarCandadoAcademicoCargos($queryAlumnos);

            $alumnos = $queryAlumnos->get();

            if ($alumnos->isEmpty()) {
                return redirect()->back()->with('error', 'No hay estudiantes pendientes y solventes para generar cargos en este ciclo.');
            }

            // 2. OBTENEMOS LOS CONCEPTOS BASE
            $conceptoMatricula = \Illuminate\Support\Facades\DB::table('concepto_pago')->where('nombre', 'ILIKE', '%MATRÍCULA%')->orWhere('nombre', 'ILIKE', '%MATRICULA%')->first();
            $conceptoCuota = \Illuminate\Support\Facades\DB::table('concepto_pago')->where('nombre', 'ILIKE', '%CUOTA%')->first();
            $conceptoLaboratorio = \Illuminate\Support\Facades\DB::table('concepto_pago')->where('nombre', 'ILIKE', '%LABORATORIO%')->first();

            if (!$conceptoMatricula || !$conceptoCuota) {
                return redirect()->back()->with('error', 'Error: Faltan los conceptos base (Matrícula/Cuota) en la base de datos.');
            }

            // 3. PREPARAMOS LOS MESES
            $esCiclo01 = str_contains($cicloActivo->nombre_ciclo, '01') || $cicloActivo->numero_ciclo == '01';
            $mesesGenerar = $esCiclo01
                ? [1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio']
                : [7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'];

            $primerMesNum = array_key_first($mesesGenerar);
            $fechaEmision = now()->toDateString();
            $anio = $cicloActivo->anio;

            $cargosGeneradosTotales = 0;

            // ABRIMOS LA TRANSACCIÓN PARA PROTEGER LA BASE DE DATOS
            \Illuminate\Support\Facades\DB::beginTransaction();

            // 4. RECORREMOS A CADA ALUMNO Y LE APLICAMOS LA MISMA LÓGICA
            foreach ($alumnos as $alumno) {

                $beneficio = \Illuminate\Support\Facades\DB::table('beneficios_estudiante')
                    ->where('id_alumno', $alumno->alumno_id)
                    ->where('estado', 'ACTIVO')
                    ->first();

                $idBeneficio = $beneficio->beneficio_id ?? null;
                $tipoBeneficio = $beneficio ? strtolower(trim($beneficio->tipo_beneficio)) : null;
                $porcentajeEstudiante = $beneficio->porcentaje_estudiante ?? 100;
                $montoFijoCuota = $beneficio->monto_fijo_cuota ?? 0;
                $cubreMatricula = $beneficio->incluye_matricula ?? false;
                $cubreLaboratorio = $beneficio->incluye_laboratorio ?? false;

                // FUNCIÓN MATEMÁTICA IDÉNTICA A LA INDIVIDUAL
                $calcularMonto = function($montoOriginal, $esMatricula, $esLaboratorio = false) use ($tipoBeneficio, $porcentajeEstudiante, $montoFijoCuota, $cubreMatricula, $cubreLaboratorio) {
                    if ($esMatricula && !$cubreMatricula) {
                        return ['neto' => $montoOriginal, 'absorbido' => 0];
                    }
                    if ($esLaboratorio) {
                        if ($tipoBeneficio === 'beca_completa' && $cubreLaboratorio) {
                            return ['neto' => 0.00, 'absorbido' => $montoOriginal];
                        } else {
                            return ['neto' => $montoOriginal, 'absorbido' => 0];
                        }
                    }

                    $neto = $montoOriginal;
                    $absorbido = 0;

                    if ($tipoBeneficio) {
                        switch ($tipoBeneficio) {
                            case 'beca_completa':
                                $neto = 0.00;
                                $absorbido = $montoOriginal;
                                break;
                            case 'beca_parcial':
                                // Símbolos matemáticos corregidos aquí
                                $neto = $montoOriginal * ($porcentajeEstudiante / 100);
                                $absorbido = $montoOriginal - $neto;
                                break;
                            case 'franja_becaria':
                            case 'cuota_especial':
                                // Símbolo matemático corregido aquí
                                $neto = $montoFijoCuota;
                                $absorbido = $montoOriginal - $neto;
                                break;
                        }
                    }
                    return ['neto' => max(0, $neto), 'absorbido' => max(0, $absorbido)];
                };

                $cargosAGuardar = [];

                // Matrícula
                $calculoMatricula = $calcularMonto($conceptoMatricula->monto_base, true);
                $cargosAGuardar[] = [
                    'id_alumno' => $alumno->alumno_id,
                    'id_concepto' => $conceptoMatricula->concepto_pago_id,
                    'id_ciclo_lectivo' => $cicloActivo->ciclo_lectivo_id,
                    'mes_arancel' => $mesesGenerar[$primerMesNum],
                    'anio_arancel' => $anio,
                    'monto_original' => $conceptoMatricula->monto_base,
                    'fecha_emision' => $fechaEmision,
                    'fecha_vencimiento' => \Carbon\Carbon::create($anio, $primerMesNum, 18)->toDateString(),
                    'estado_cargo' => ($calculoMatricula['neto'] == 0) ? 'PAGADO' : 'PENDIENTE',
                    'id_beneficio' => $idBeneficio,
                    'monto_absorbido_universidad' => $calculoMatricula['absorbido'],
                    'monto_neto_estudiante' => $calculoMatricula['neto']
                ];

                // Laboratorio
                if ($cubreLaboratorio && isset($conceptoLaboratorio) && $conceptoLaboratorio) {
                    $calculoLab = $calcularMonto($conceptoLaboratorio->monto_base, false, true);
                    $cargosAGuardar[] = [
                        'id_alumno' => $alumno->alumno_id,
                        'id_concepto' => $conceptoLaboratorio->concepto_pago_id,
                        'id_ciclo_lectivo' => $cicloActivo->ciclo_lectivo_id,
                        'mes_arancel' => $mesesGenerar[$primerMesNum],
                        'anio_arancel' => $anio,
                        'monto_original' => $conceptoLaboratorio->monto_base,
                        'fecha_emision' => $fechaEmision,
                        'fecha_vencimiento' => \Carbon\Carbon::create($anio, $primerMesNum, 18)->toDateString(),
                        'estado_cargo' => ($calculoLab['neto'] == 0) ? 'PAGADO' : 'PENDIENTE',
                        'id_beneficio' => $idBeneficio,
                        'monto_absorbido_universidad' => $calculoLab['absorbido'],
                        'monto_neto_estudiante' => $calculoLab['neto']
                    ];
                }

                // Cuotas
                $calculoCuota = $calcularMonto($conceptoCuota->monto_base, false);
                foreach ($mesesGenerar as $numMes => $nombreMes) {
                    $cargosAGuardar[] = [
                        'id_alumno' => $alumno->alumno_id,
                        'id_concepto' => $conceptoCuota->concepto_pago_id,
                        'id_ciclo_lectivo' => $cicloActivo->ciclo_lectivo_id,
                        'mes_arancel' => $nombreMes,
                        'anio_arancel' => $anio,
                        'monto_original' => $conceptoCuota->monto_base,
                        'fecha_emision' => $fechaEmision,
                        'fecha_vencimiento' => \Carbon\Carbon::create($anio, $numMes, 10)->toDateString(),
                        'estado_cargo' => ($calculoCuota['neto'] == 0) ? 'PAGADO' : 'PENDIENTE',
                        'id_beneficio' => $idBeneficio,
                        'monto_absorbido_universidad' => $calculoCuota['absorbido'],
                        'monto_neto_estudiante' => $calculoCuota['neto']
                    ];
                }

                // AUDITORÍA Y GUARDADO POR ESTUDIANTE
                $esBecaCompleta = ($tipoBeneficio === 'beca_completa');
                $pagoId = null;

                if ($esBecaCompleta) {
                    $numeroRecibo = 'REC-BECA-' . $anio . '-' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
                    $pagoId = \Illuminate\Support\Facades\DB::table('pago')->insertGetId([
                        'id_alumno' => $alumno->alumno_id,
                        'fecha_pago' => $fechaEmision,
                        'total' => 0.00,
                        'numero_recibo' => $numeroRecibo,
                        'canal_pago' => 'SISTEMA',
                        'metodo_pago' => 'EXONERACION'
                    ], 'pago_id');
                }

                foreach ($cargosAGuardar as $cargoData) {
                    $cargoId = \Illuminate\Support\Facades\DB::table('cargo_estudiante')->insertGetId($cargoData, 'cargo_id');
                    $cargosGeneradosTotales++; // Contador para el mensaje final

                    if ($esBecaCompleta && $cargoData['estado_cargo'] === 'PAGADO') {
                        \Illuminate\Support\Facades\DB::table('detalle_pago')->insert([
                            'id_pago' => $pagoId,
                            'id_cargo' => $cargoId,
                            'id_concepto' => $cargoData['id_concepto'],
                            'monto_base' => $cargoData['monto_original'],
                            'descuento_aplicado' => $cargoData['monto_absorbido_universidad'],
                            'recargo_aplicado' => 0.00,
                            'cantidad' => 1,
                            'subtotal' => 0.00
                        ]);
                    }
                }
            }

            // CONFIRMAMOS TODOS LOS DATOS
            \Illuminate\Support\Facades\DB::commit();

            return redirect()->route('adfinanciero.cargos.create')
                ->with('success', "¡Proceso masivo completado! Se le generaron los aranceles a {$alumnos->count()} estudiantes ($cargosGeneradosTotales cargos insertados en total).");

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return redirect()->back()->with('error', 'Ocurrió un error en la generación masiva: ' . $e->getMessage());
        }
    }

    // vista de generar deuda, no genera si tiene materia pendiente
    private function aplicarCandadoAcademicoCargos($query)
    {
        return $query
            // REGLA 3: Excluir alumnos que ya están marcados como EGRESADO en requisitos_egreso
            ->whereNotExists(function ($q) {
                $q->select(\Illuminate\Support\Facades\DB::raw(1))
                    ->from('requisitos_egreso as re')
                    ->whereColumn('re.id_alumno', 'alumnos.alumno_id')
                    ->where('re.estado_egreso', 'EGRESADO');
            })
            // REGLA 4: Incluir solo si tiene al menos 1 materia del pensum ACTIVO que NO esté APROBADA
            ->whereExists(function ($q) {
                $q->select(\Illuminate\Support\Facades\DB::raw(1))
                    ->from('pensum as p')
                    ->join('pensum_materia as pm', 'pm.id_pensum', '=', 'p.pensum_id')
                    ->whereColumn('p.id_carrera', 'alumnos.id_carrera')
                    ->where('p.estado', 'ACTIVO')
                    ->whereNotExists(function ($sub) {
                        $sub->select(\Illuminate\Support\Facades\DB::raw(1))
                            ->from('inscripciones as i')
                            ->join('detalle_inscripcion as di', 'di.id_inscripcion', '=', 'i.inscripcion_id')
                            ->join('seccion as s', 's.seccion_id', '=', 'di.id_seccion')
                            ->whereColumn('i.id_alumno', 'alumnos.alumno_id')
                            ->whereColumn('s.id_materia', 'pm.id_materia')
                            ->where('di.estado_materia', 'APROBADA');
                    });
            });
    }
}
