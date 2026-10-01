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
            $query->where('estado', $request->estado_filter === 'activos' ? true : false);
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

  // Cargos Estudiantiles
    public function cargosIndex(Request $request)
    {
        // --- 1. SECCIÓN DE DEUDAS (CARGOS) ---
        $query = \App\Models\Adfinanciero\CargoEstudiante::with(['ciclo', 'concepto']);

        // Filtro por Estado_cargo
        if ($request->filled('estado_filter')) {
            $query->where('estado_cargo', strtoupper($request->estado_filter));
        }

        // Búsqueda por ID de Alumno
        if ($request->filled('search')) {
            $query->where('id_alumno', $request->search);
        }

        // Calculamos el total de deuda pendiente
        $totalPendiente = (clone $query)->where('estado_cargo', 'PENDIENTE')->sum('monto_original');

        // Ordenamos y paginamos los cargos
        $query->orderBy('fecha_emision', 'asc');
        $cargos = $query->paginate(15)->appends($request->all());


        // --- 2. SECCIÓN DE HISTORIAL DE PAGOS (RECIBOS) ---
        $queryPagos = \App\Models\Adfinanciero\Pago::with('detalles.concepto')
            ->whereHas('detalles.concepto', function ($q) {
                $q->where('nombre', 'ILIKE', '%CUOTA%')
                  ->orWhere('nombre', 'ILIKE', '%MATRÍCULA%')
                  ->orWhere('nombre', 'ILIKE', '%MATRICULA%');
            });

        // Búsqueda por ID de Alumno para los pagos
        if ($request->filled('search')) {
            $queryPagos->where('id_alumno', $request->search);
        }

        // Obtenemos los recibos ordenados por los más recientes
        $pagos = $queryPagos->orderBy('fecha_pago', 'desc')->get();

        // Sumamos el dinero real de los recibos (incluye descuentos)
        $totalPagado = $pagos->sum('total');


        // --- 3. DATOS ADICIONALES ---
        $ciclos = \App\Models\Adfinanciero\CicloLectivo::all();
        $conceptos = \App\Models\Adfinanciero\ConceptoPago::where('estado', true)->get();


        // --- 4. RETORNO A LA VISTA (Único return al final) ---
        return view('adfinanciero.cargosestudiante.index', compact('cargos', 'ciclos', 'conceptos', 'totalPendiente', 'totalPagado', 'pagos'));
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
        // Utilizamos tu estructura de namespaces (Asegúrate de que el modelo se llame ReglaPago o ajusta el nombre)
        $reglas = \App\Models\Adfinanciero\ReglaPago::with('conceptoRecargo')->get();

        // Traemos los aranceles activos para el select del modal
        $conceptos = \App\Models\Adfinanciero\ConceptoPago::where('estado', 'ACTIVO')->get();

        // Ajusta la ruta de la vista según tu estructura de carpetas (ej. adfinanciero.reglas.index)
        return view('adfinanciero.reglas.index', compact('reglas', 'conceptos'));
    }

    public function reglasStore(Request $request)
    {
        $request->validate([
            'tipo' => 'required|string|max:255',
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

}
