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

    // --- LÓGICA PARA PROCESAR EL PAGO EN LÍNEA DESDE LA VISTA DEL ESTUDIANTE ---
    public function cargosIndex(Request $request)
    {
        // 1. Obtenemos el ID del alumno logueado
        $alumnoId = \Illuminate\Support\Facades\Auth::user()->alumno->alumno_id ?? null;

        // --- SECCIÓN DE DEUDAS (CARGOS) ---
        $query = \App\Models\Adfinanciero\CargoEstudiante::with(['ciclo', 'concepto']);

        if ($alumnoId) {
            $query->where('id_alumno', $alumnoId);
        } elseif ($request->filled('search')) {
            $query->where('id_alumno', $request->search);
        }

        // --- CÁLCULO EXACTO DEL SALDO PENDIENTE (CON MORAS INCLUIDAS) ---
        // --- CÁLCULO EXACTO DEL SALDO PENDIENTE (CON MORAS INCLUIDAS) ---
        $totalPendiente = 0;
        $hoy = \Carbon\Carbon::now();
        $cargosParaSumar = (clone $query)->where('estado_cargo', 'PENDIENTE')->get();

        foreach ($cargosParaSumar as $cargoPendiente) {
            $fechaVenc = \Carbon\Carbon::parse($cargoPendiente->fecha_vencimiento);
            $estaVencido = $hoy->gt($fechaVenc);

            $nombreArancel = strtoupper($cargoPendiente->concepto->nombre ?? '');
            $esCuota = (str_contains($nombreArancel, 'CUOTA') || str_contains($nombreArancel, 'MENSUAL'));
            $esMatricula = (str_contains($nombreArancel, 'MATRÍCULA') || str_contains($nombreArancel, 'MATRICULA') || str_contains($nombreArancel, 'INSCRIPCI'));

            $moraSumar = 0;
            if ($estaVencido) {
                if ($esCuota) {
                    $regla = \Illuminate\Support\Facades\DB::table('reglas_pago')->where('tipo', 'ILIKE', '%Mensualidad%')->first();
                    if ($regla && $regla->id_concepto_recargo) {
                        $conceptoMora = \Illuminate\Support\Facades\DB::table('concepto_pago')->where('concepto_pago_id', $regla->id_concepto_recargo)->first();
                        if ($conceptoMora) $moraSumar = floatval($conceptoMora->monto_base);
                    }
                } elseif ($esMatricula) {
                    $regla = \Illuminate\Support\Facades\DB::table('reglas_pago')->where('tipo', 'ILIKE', '%Inscripci%')->first();
                    if ($regla && $regla->id_concepto_recargo) {
                        $conceptoMora = \Illuminate\Support\Facades\DB::table('concepto_pago')->where('concepto_pago_id', $regla->id_concepto_recargo)->first();
                        if ($conceptoMora) $moraSumar = floatval($conceptoMora->monto_base);
                    }
                }
            }

            $totalPendiente += floatval($cargoPendiente->monto_original) + $moraSumar;
        }


        $query->orderBy('fecha_emision', 'asc');
        $cargos = $query->paginate(15)->appends($request->all());

        // --- SECCIÓN DE HISTORIAL DE PAGOS (RECIBOS) ---
        $queryPagos = \App\Models\Adfinanciero\Pago::with(['detalles.cargo', 'detalles.concepto'])
            ->whereHas('detalles.concepto', function ($q) {
                $q->where('nombre', 'ILIKE', '%CUOTA%')
                  ->orWhere('nombre', 'ILIKE', '%MATRÍCULA%')
                  ->orWhere('nombre', 'ILIKE', '%MATRICULA%');
            });

        if ($alumnoId) {
            $queryPagos->where('id_alumno', $alumnoId);
        } elseif ($request->filled('search')) {
            $queryPagos->where('id_alumno', $request->search);
        }

        $pagos = $queryPagos->orderBy('fecha_pago', 'desc')->get();
        $totalPagado = $pagos->sum('total');

        // --- DATOS ADICIONALES ---
        $ciclos = \App\Models\Adfinanciero\CicloLectivo::all();
        $conceptos = \App\Models\Adfinanciero\ConceptoPago::where('estado', true)->get();

        // Extraemos la mora de mensualidad para pasarla a la vista por compatibilidad
        $montoMora = 0;
        $reglaMensualidad = \Illuminate\Support\Facades\DB::table('reglas_pago')->where('tipo', 'ILIKE', '%Mensualidad%')->first();
        if ($reglaMensualidad && $reglaMensualidad->id_concepto_recargo) {
            $conceptoBD = \Illuminate\Support\Facades\DB::table('concepto_pago')->where('concepto_pago_id', $reglaMensualidad->id_concepto_recargo)->first();
            if ($conceptoBD) {
                $montoMora = floatval($conceptoBD->monto_base);
            }
        }

        return view('adfinanciero.cargosestudiante.index', compact('cargos', 'ciclos', 'conceptos', 'totalPendiente', 'totalPagado', 'pagos', 'montoMora'));
    }

    //pago en linea, se calcula la mora para mensualidad y matricula
    public function procesarPagoEnLinea(Request $request)
    {
        // 1. Validar que vengan cargos seleccionados
        $request->validate([
            'cargos_ids' => 'required|array',
            'cargos_ids.*' => 'exists:cargo_estudiante,cargo_id'
        ]);

        $alumnoId = \Illuminate\Support\Facades\Auth::user()->alumno->alumno_id ?? null;

        if (!$alumnoId) {
            return back()->with('error', 'No se encontró la información del estudiante.');
        }

        try {
            // Usamos transacción para proteger la base de datos
            \Illuminate\Support\Facades\DB::beginTransaction();

            // 2. Obtener los cargos pendientes
            $cargos = \App\Models\Adfinanciero\CargoEstudiante::whereIn('cargo_id', $request->cargos_ids)
                ->where('id_alumno', $alumnoId)
                ->where('estado_cargo', 'PENDIENTE')
                ->get();

            if ($cargos->isEmpty()) {
                return back()->with('error', 'Los aranceles seleccionados no son válidos o ya fueron pagados.');
            }

            // --- LÓGICA DE REGLAS DE COBRO (MORA Y MATRÍCULA) ---

            // Buscar regla de Mensualidad
            $montoMoraMensualidad = 0;
            $reglaMensualidad = \Illuminate\Support\Facades\DB::table('reglas_pago')->where('tipo', 'ILIKE', '%Mensualidad%')->first();
            if ($reglaMensualidad && $reglaMensualidad->id_concepto_recargo) {
                $conceptoBD = \Illuminate\Support\Facades\DB::table('concepto_pago')->where('concepto_pago_id', $reglaMensualidad->id_concepto_recargo)->first();
                if ($conceptoBD) $montoMoraMensualidad = floatval($conceptoBD->monto_base);
            }

            // Buscar regla de Inscripción/Matrícula
            $montoMoraMatricula = 0;
            $reglaMatricula = \Illuminate\Support\Facades\DB::table('reglas_pago')->where('tipo', 'ILIKE', '%Inscripci%')->first();
            if ($reglaMatricula && $reglaMatricula->id_concepto_recargo) {
                $conceptoBD = \Illuminate\Support\Facades\DB::table('concepto_pago')->where('concepto_pago_id', $reglaMatricula->id_concepto_recargo)->first();
                if ($conceptoBD) $montoMoraMatricula = floatval($conceptoBD->monto_base);
            }

            $totalMonto = 0;
            $detallesAGuardar = [];
            $hoy = \Carbon\Carbon::now();

            // Evaluamos cada cargo seleccionado
            foreach ($cargos as $cargo) {
                $fechaVencimiento = \Carbon\Carbon::parse($cargo->fecha_vencimiento);
                $estaVencido = $hoy->gt($fechaVencimiento);

                // Identificamos el tipo de arancel
                $nombreConcepto = strtoupper($cargo->concepto->nombre ?? '');
                $esMensualidad = (str_contains($nombreConcepto, 'CUOTA') || str_contains($nombreConcepto, 'MENSUAL'));
                $esMatricula = (str_contains($nombreConcepto, 'MATRÍCULA') || str_contains($nombreConcepto, 'MATRICULA') || str_contains($nombreConcepto, 'INSCRIPCI'));

                $moraAplicada = 0;

                // Si está vencido, le sumamos el castigo que le corresponde
                if ($estaVencido) {
                    if ($esMensualidad) {
                        $moraAplicada = $montoMoraMensualidad;
                    } elseif ($esMatricula) {
                        $moraAplicada = $montoMoraMatricula;
                    }
                }

                $subtotal = floatval($cargo->monto_original) + floatval($moraAplicada);
                $totalMonto += $subtotal;

                $detallesAGuardar[] = [
                    'id_cargo' => $cargo->cargo_id,
                    'id_concepto' => $cargo->id_concepto,
                    'monto_base' => $cargo->monto_original,
                    'descuento_aplicado' => 0,
                    'recargo_aplicado' => $moraAplicada, // Se guarda el recargo (Mora o Inscripción extraordinaria)
                    'cantidad' => 1,
                    'subtotal' => $subtotal,
                    'cargo_model' => $cargo
                ];
            }

            // 3. Crear el encabezado del Pago (Recibo)
            $numeroRecibo = 'REC-WEB-' . date('Y') . '-' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);

            $pago = \App\Models\Adfinanciero\Pago::create([
                'id_alumno' => $alumnoId,
                'fecha_pago' => $hoy->format('Y-m-d'),
                'total' => $totalMonto,
                'numero_recibo' => $numeroRecibo,
                'canal_pago' => 'WEB',
                'metodo_pago' => 'TARJETA',
            ]);

            // 4. Guardar los detalles y actualizar estado de la deuda
            foreach ($detallesAGuardar as $detalle) {
                \App\Models\Adfinanciero\DetallePago::create([
                    'id_pago' => $pago->pago_id,
                    'id_cargo' => $detalle['id_cargo'],
                    'id_concepto' => $detalle['id_concepto'],
                    'monto_base' => $detalle['monto_base'],
                    'descuento_aplicado' => $detalle['descuento_aplicado'],
                    'recargo_aplicado' => $detalle['recargo_aplicado'],
                    'cantidad' => $detalle['cantidad'],
                    'subtotal' => $detalle['subtotal']
                ]);

                $detalle['cargo_model']->update(['estado_cargo' => 'PAGADO']);
            }

            \Illuminate\Support\Facades\DB::commit();

            try {
                $usuario = \Illuminate\Support\Facades\Auth::user();
                $correoEstudiante = $usuario->email;

                $nombreEstudiante = $usuario->name;
                $codigoEstudiante = $usuario->alumno->codigo_estudiante ?? 'N/A';

                $pagoParaCorreo = \App\Models\Adfinanciero\Pago::with([
                    'detalles.concepto',
                    'detalles.cargo.ciclo'
                ])->find($pago->pago_id);

                $detallesDesglosados = [];

                foreach ($pagoParaCorreo->detalles as $detalle) {

                    // --- FILA 1: EL ARANCEL BASE ---
                    $nombreBase = $detalle->concepto->nombre ?? 'Arancel';
                    $mes = $detalle->cargo->ciclo->mes ?? '';
                    $anio = $detalle->cargo->ciclo->anio ?? '';

                    if (str_contains(strtoupper($nombreBase), 'CUOTA') && $mes && $anio) {
                        $mesFormateado = str_pad($mes, 2, '0', STR_PAD_LEFT);
                        $nombreBase .= " ($mesFormateado/$anio)";
                    }

                    // Formato exacto que usa el cajero
                    $detallesDesglosados[] = [
                        'nombre' => $nombreBase,
                        'monto'  => floatval($detalle->monto_base)
                    ];

                    // --- FILA 2: LA MORA (SI APLICA) ---
                    if (floatval($detalle->recargo_aplicado) > 0) {
                        $nombreMora = 'RECARGO POR MORA';

                        if (str_contains(strtoupper($detalle->concepto->nombre ?? ''), 'MATRÍCULA') ||
                            str_contains(strtoupper($detalle->concepto->nombre ?? ''), 'MATRICULA') ||
                            str_contains(strtoupper($detalle->concepto->nombre ?? ''), 'INSCRIPCI')) {
                            $nombreMora = 'INSCRIPCIÓN EXTRAORDINARIA';
                        }

                        // Formato exacto que usa el cajero
                        $detallesDesglosados[] = [
                            'nombre' => $nombreMora,
                            'monto'  => floatval($detalle->recargo_aplicado)
                        ];
                    }
                }

                \Illuminate\Support\Facades\Mail::to($correoEstudiante)->send(
                    new \App\Mail\ReciboPagoMail(
                        $numeroRecibo,
                        $totalMonto,
                        $detallesDesglosados,
                        'TARJETA',
                        'PAGO EN LÍNEA (PORTAL ESTUDIANTE)',
                        $hoy->format('d/m/Y h:i A'), // Mismo formato de fecha del cajero
                        $nombreEstudiante,
                        $codigoEstudiante
                    )
                );

            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Error al enviar correo de factura: ' . $e->getMessage());
                return redirect()->back()->with('success', 'Pago procesado con éxito (Recibo: ' . $numeroRecibo . '), pero hubo un retraso al enviar el correo.');
            }


            return redirect()->back()->with('success', 'Pago en línea procesado con éxito. Tu número de recibo es: ' . $numeroRecibo . '. Hemos enviado la factura a tu correo.');

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return back()->with('error', 'Ocurrió un error al procesar el pago: ' . $e->getMessage());
        }
    }

}
