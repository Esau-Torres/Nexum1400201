<?php

namespace App\Http\Controllers\Cajero;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Adfinanciero\CargoEstudiante;
use App\Models\Adfinanciero\ConceptoPago;
use Illuminate\Support\Facades\DB; // Para actualizar el estado y guardar el pago
use App\Mail\ReciboPagoMail;
use Illuminate\Support\Facades\Mail; // Para el correo
use Illuminate\Support\Facades\Auth; // Para saber qué cajero cobró

class CajeroController extends Controller
{

    //Muestra la pantalla de ventanilla y busca cargos PENDIENTES.
    public function deudaIndex(Request $request)
    {
        $cargos = collect();

        if ($request->filled('id_alumno')) {
            $request->merge([
                'id_alumno' => strtoupper(trim($request->id_alumno))
            ]);

            $request->validate([
                'id_alumno' => 'required|string|exists:alumnos,codigo_estudiante'
            ], [
                'id_alumno.exists' => 'Error: El código ingresado no corresponde a ningún alumno registrado.'
            ]);

            $alumno = \Illuminate\Support\Facades\DB::table('alumnos')
                        ->where('codigo_estudiante', $request->id_alumno)
                        ->first();

            $cargos = CargoEstudiante::with('concepto')
                ->where('id_alumno', $alumno->alumno_id)
                ->where('estado_cargo', 'PENDIENTE')
                ->orderBy('fecha_vencimiento', 'asc')
                ->get();

            // ==========================================
            // NUEVO: APLICACIÓN DE REGLAS DE MORA
            // ==========================================
            $hoy = now()->startOfDay();

            // 1. Unimos reglas_pago con concepto_pago para obtener los $5 o $10 dinámicamente
            $reglasActivas = \Illuminate\Support\Facades\DB::table('reglas_pago')
                ->join('concepto_pago', 'reglas_pago.id_concepto_recargo', '=', 'concepto_pago.concepto_pago_id')
                ->select('reglas_pago.tipo', 'concepto_pago.monto_base', 'concepto_pago.nombre as nombre_recargo')
                ->where('reglas_pago.estado', 'ACTIVO')
                ->get();

            $reglaMensualidad = $reglasActivas->firstWhere('tipo', 'Mensualidad');
            $reglaInscripcion = $reglasActivas->firstWhere('tipo', 'Inscripción');

            // 2. Evaluamos cargo por cargo si ya está vencido
            foreach ($cargos as $cargo) {
                // Valores base (por si el cargo no está vencido)
                $cargo->tiene_mora = false;
                $cargo->monto_mora = 0;
                $cargo->total_con_mora = $cargo->monto_neto_estudiante ?? $cargo->monto_original;

                $fechaVencimiento = \Carbon\Carbon::parse($cargo->fecha_vencimiento)->startOfDay();

                // Si hoy es mayor a la fecha de vencimiento (está en rojo)
                if ($hoy->gt($fechaVencimiento)) {
                    $nombreCargo = strtoupper($cargo->concepto->nombre);
                    $reglaAplicable = null;

                    // Asignar regla según el texto del concepto
                    if (str_contains($nombreCargo, 'MENSUAL')) {
                        $reglaAplicable = $reglaMensualidad;
                    } elseif (str_contains($nombreCargo, 'MATRICULA') || str_contains($nombreCargo, 'INSCRIPCI')) {
                        $reglaAplicable = $reglaInscripcion;
                    }

                    // Si se encontró una regla válida, inyectamos la mora al objeto
                    if ($reglaAplicable) {
                        $cargo->tiene_mora = true;
                        $cargo->monto_mora = $reglaAplicable->monto_base;
                        $cargo->nombre_mora = $reglaAplicable->nombre_recargo;
                        // Se suma la mora al monto con beneficio
                        $cargo->total_con_mora = ($cargo->monto_neto_estudiante ?? $cargo->monto_original) + $reglaAplicable->monto_base;
                    }
                }
            }
        }

        return view('cajero.deuda.index', compact('cargos'));
    }

    //Procesa el abono y cambia el estado del cargo a PAGADO.
    public function deudaLiquidar(Request $request, $id)
    {
        // 1. Validamos que el request venga con los datos del modal
        $request->validate([
            'estado_cargo' => 'required|in:PAGADO',
            'metodo_pago' => 'required|string'
        ]);

        // 2. Buscamos el cargo exacto
        $cargo = CargoEstudiante::findOrFail($id);

        // 3. Actualizamos el estado del cargo a PAGADO (Liquidación)
        $cargo->update([
            'estado_cargo' => 'PAGADO'
        ]);

        /*
         * NOTA PARA EL SIGUIENTE PASO:
         * Más adelante, justo aquí agregaremos el código para insertar la cabecera
         * en la tabla 'pago' y el desglose en 'detalle_pago' para generar el recibo oficial.
         */

        return redirect()->back()->with('success', 'Abono procesado: El cargo #' . $id . ' ha sido liquidado exitosamente.');
    }

    public function indexVentanilla()
    {
        $conceptos = ConceptoPago::where('nombre', 'not like', '%RECARGO POR MORA%')
            ->where('nombre', 'not like', '%PREGRADOS: LICENCIATURAS%')
            ->where('nombre', 'not like', '%DOCENCIA UNIVERSITARIA%')
            ->where('nombre', 'not like', '%PSICOLOGIA CLINICA%')
            ->orderBy('nombre', 'asc')
            ->get();

        return view('cajero.ventanilla.index', compact('conceptos'));
    }

    //Logica de las promociones
    public function promociones()
    {
        // Traemos solo las promociones activas (Ajusta 'estado', 1 si tu BD usa booleanos, o 'ACTIVO' si usa texto)
        $promociones = \App\Models\Adfinanciero\Promocion::where('estado', true)->get();

        return view('cajero.promociones.index', compact('promociones'));
    }

    //funciona en la ventanilla para las deudas
    public function crearPago(Request $request)
    {
        // 1. OBTENEMOS LAS PROMOCIONES VIGENTES EL DÍA DE HOY
        $hoy = now();
        $promocionesVigentes = DB::table('promociones')
            ->where('fecha_inicio', '<=', $hoy)
            ->where('fecha_fin', '>=', $hoy)
            ->get();

        $arancelesACobrar = collect(); // Colección para guardar todo lo que se va a cobrar
        $totalLiquidar = 0;
        $metodoPagoEscogido = $request->input('metodo_pago', 'efectivo');
        $esPagoDeuda = false; // Bandera para saber si ocultar el input de Carnet en la vista

        // VARIABLE NUEVA PARA LA VISTA
        $alumno = null;

        // ==========================================
        // ESCENARIO A: DEUDA PENDIENTE (MÚLTIPLES CARGOS DESDE CHECKBOXES)
        // ==========================================
        if ($request->has('cargos_ids')) {
            $esPagoDeuda = true;
            $cargosIds = $request->input('cargos_ids', []);

            if (empty($cargosIds)) {
                return redirect()->back()->with('error', 'Debe seleccionar al menos una deuda.');
            }

            // Buscamos todas las deudas seleccionadas uniendo la tabla de conceptos para el nombre
            $cargos = DB::table('cargo_estudiante')
                ->join('concepto_pago', 'cargo_estudiante.id_concepto', '=', 'concepto_pago.concepto_pago_id')
                ->select('cargo_estudiante.*', 'concepto_pago.nombre as nombre_concepto')
                ->whereIn('cargo_id', $cargosIds)
                ->get();

            if($cargos->isNotEmpty()) {
                // Buscamos al alumno usando el ID del primer cargo para mostrar su identidad en la caja
                $alumnoBD = DB::table('alumnos')->where('alumno_id', $cargos->first()->id_alumno)->first();
                if($alumnoBD) {
                    $usuarioRelacionado = DB::table('users')->where('id', $alumnoBD->id_usuario)->first();

                    $tipoBeneficio = DB::table('beneficios_estudiante')
                        ->where('id_alumno', $alumnoBD->alumno_id)
                        ->value('tipo_beneficio');

                    // Armamos un objeto limpio para la vista
                    $alumno = (object)[
                        'nombres' => $usuarioRelacionado ? $usuarioRelacionado->name : 'Estudiante',
                        'apellidos' => '',
                        'carnet' => $alumnoBD->codigo_estudiante,
                        'carrera' => DB::table('carreras')->where('carrera_id', $alumnoBD->id_carrera)->value('nombre') ?? 'Sin carrera',
                        'beneficio' => $tipoBeneficio ? ucwords(strtolower(str_replace('_', ' ', $tipoBeneficio))) : 'Sin beneficio'
                    ];
                }
            }

            // ==========================================
            // NUEVO: LÓGICA DE REGLAS DE MORA
            // ==========================================
            $fechaHoyStart = now()->startOfDay();
            $reglasActivas = DB::table('reglas_pago')
                ->join('concepto_pago', 'reglas_pago.id_concepto_recargo', '=', 'concepto_pago.concepto_pago_id')
                ->select('reglas_pago.tipo', 'concepto_pago.monto_base', 'concepto_pago.nombre as nombre_recargo')
                ->where('reglas_pago.estado', 'ACTIVO')
                ->get();

            $reglaMensualidad = $reglasActivas->firstWhere('tipo', 'Mensualidad');
            $reglaInscripcion = $reglasActivas->firstWhere('tipo', 'Inscripción');

            // Recorremos los cargos aplicando descuentos y moras
            foreach ($cargos as $cargo) {
                // 1. APLICAMOS LA MORA SI EL CARGO ESTÁ VENCIDO
                $cargo->tiene_mora = false;
                $cargo->monto_mora = 0;
                $montoBaseCargo = $cargo->monto_neto_estudiante ?? $cargo->monto_original;
                $cargo->total_con_mora = $montoBaseCargo;

                if ($cargo->fecha_vencimiento) {
                    $fechaVencimiento = \Carbon\Carbon::parse($cargo->fecha_vencimiento)->startOfDay();

                    if ($fechaHoyStart->gt($fechaVencimiento)) {
                        $nombreCargo = strtoupper($cargo->nombre_concepto);
                        $reglaAplicable = null;

                        if (str_contains($nombreCargo, 'MENSUAL')) {
                            $reglaAplicable = $reglaMensualidad;
                        } elseif (str_contains($nombreCargo, 'MATRICULA') || str_contains($nombreCargo, 'INSCRIPCI')) {
                            $reglaAplicable = $reglaInscripcion;
                        }

                        if ($reglaAplicable) {
                            $cargo->tiene_mora = true;
                            $cargo->monto_mora = $reglaAplicable->monto_base;
                            $cargo->total_con_mora = $montoBaseCargo + $reglaAplicable->monto_base;
                        }
                    }
                }

                // 2. APLICAMOS PROMOCIONES AL MONTO ORIGINAL
                $descuentoDinero = 0;
                $promoEspecifica = $promocionesVigentes->where('id_concepto_aplicable', $cargo->id_concepto)->first();
                $promoGlobal = $promocionesVigentes->whereNull('id_concepto_aplicable')->first();

                $porcentajeEspecifico = $promoEspecifica ? $promoEspecifica->porcentaje_descuento : 0;
                $porcentajeGlobal = $promoGlobal ? $promoGlobal->porcentaje_descuento : 0;
                $mejorPorcentaje = max($porcentajeEspecifico, $porcentajeGlobal);

                if ($mejorPorcentaje > 0) {
                    // El descuento se calcula sobre el monto original, sin incluir la mora
                    $descuentoDinero = ($montoBaseCargo * $mejorPorcentaje) / 100;
                }

                // El subtotal es el Total con Mora menos el Descuento
                $subtotalFinal = $cargo->total_con_mora - $descuentoDinero;
                $totalLiquidar += $subtotalFinal;

                // Formateamos el nombre agregando el Mes y Año (Ej. 10/2026)
                $mes = str_pad($cargo->mes_arancel, 2, '0', STR_PAD_LEFT);
                $nombreCompleto = $cargo->nombre_concepto . " ($mes/$cargo->anio_arancel)";

                $arancelesACobrar->push((object)[
                    'concepto_pago_id' => $cargo->id_concepto,
                    'id_cargo' => $cargo->cargo_id, // Vital para saber qué deuda marcar como PAGADA
                    'nombre' => $nombreCompleto, //
                    'monto_base' => $montoBaseCargo,
                    'monto_final' => $subtotalFinal, //
                    'porcentaje_descuento' => $mejorPorcentaje, //
                    'descuento_dinero' => $descuentoDinero, //

                    // Pasamos a la vista los datos de la mora
                    'tiene_mora' => $cargo->tiene_mora,
                    'monto_mora' => $cargo->monto_mora
                ]);
            }
        }
        // ==========================================
        // ESCENARIO B: VENTA DIRECTA CON DESCUENTOS (VENTANILLA MÚLTIPLE)
        // ==========================================
        else if ($request->has('aranceles')) {
            $idsAranceles = $request->input('aranceles', []);

            if (empty($idsAranceles)) {
                return redirect()->back()->with('error', 'Debe seleccionar al menos un arancel.');
            }

            // En venta libre, vemos si mandaron el carnet en el request para mostrar su nombre
            if($request->has('codigo_estudiante') && !empty($request->input('codigo_estudiante'))) {
                $codigoIngresado = strtolower(trim($request->input('codigo_estudiante')));
                $alumnoBD = DB::table('alumnos')->where('codigo_estudiante', $codigoIngresado)->first();

                if($alumnoBD) {
                    $usuarioRelacionado = DB::table('users')->where('id', $alumnoBD->id_usuario)->first();

                    $tipoBeneficio = DB::table('beneficios_estudiante')
                        ->where('id_alumno', $alumnoBD->alumno_id)
                        ->value('tipo_beneficio');

                    $alumno = (object)[
                        'nombres' => $usuarioRelacionado ? $usuarioRelacionado->name : 'Estudiante',
                        'apellidos' => '',
                        'carnet' => $alumnoBD->codigo_estudiante,
                        'carrera' => DB::table('carreras')->where('carrera_id', $alumnoBD->id_carrera)->value('nombre') ?? 'Sin carrera',
                        'beneficio' => $tipoBeneficio ? ucwords(strtolower(str_replace('_', ' ', $tipoBeneficio))) : 'Sin beneficio'
                    ];
                }
            }

            $arancelesOriginales = \App\Models\Adfinanciero\ConceptoPago::whereIn('concepto_pago_id', $idsAranceles)->get();

            foreach ($arancelesOriginales as $arancel) {
                $descuentoDinero = 0;

                // Buscar promos aplicables
                $promoEspecifica = $promocionesVigentes->where('id_concepto_aplicable', $arancel->concepto_pago_id)->first();
                $promoGlobal = $promocionesVigentes->whereNull('id_concepto_aplicable')->first();

                // Regla del Mayor Beneficio
                $porcentajeEspecifico = $promoEspecifica ? $promoEspecifica->porcentaje_descuento : 0;
                $porcentajeGlobal = $promoGlobal ? $promoGlobal->porcentaje_descuento : 0;
                $mejorPorcentaje = max($porcentajeEspecifico, $porcentajeGlobal);

                if ($mejorPorcentaje > 0) {
                    $descuentoDinero = ($arancel->monto_base * $mejorPorcentaje) / 100;
                }

                $subtotalFinal = $arancel->monto_base - $descuentoDinero;
                $totalLiquidar += $subtotalFinal;

                $arancelesACobrar->push((object)[
                    'concepto_pago_id' => $arancel->concepto_pago_id,
                    'id_cargo' => null, // Como es venta directa, no hay deuda previa que saldar
                    'nombre' => $arancel->nombre,
                    'monto_base' => $arancel->monto_base,
                    'monto_final' => $subtotalFinal,
                    'porcentaje_descuento' => $mejorPorcentaje,
                    'descuento_dinero' => $descuentoDinero,

                    // La venta directa no tiene moras
                    'tiene_mora' => false,
                    'monto_mora' => 0
                ]);
            }
        } else {
            // Protección por si ingresan a la ruta sin seleccionar nada
            return redirect()->route('cajero.ventanilla.index')->with('error', 'No se enviaron datos para procesar el cobro.');
        }

        // Enviamos TODO a la vista del recibo, INCLUYENDO la nueva variable $alumno
        return view('cajero.pagos.create', compact('arancelesACobrar', 'totalLiquidar', 'metodoPagoEscogido', 'esPagoDeuda', 'alumno'));
    }

    //guarda el pago ya sea del mes o cualquiero arancel
    public function guardarPago(Request $request)
    {

        // 1. Armamos las reglas básicas dinámicas
        $reglas = [
            'total_liquidado' => 'required|numeric|gt:0',
        ];

        // CORRECCIÓN CLAVE: Buscamos ambos nombres de input posibles para garantizar que detecte 'tarjeta'
        $canalPago = strtolower($request->input('metodo_pago', $request->input('canal', 'efectivo')));

        // Limpiamos los espacios visuales de la tarjeta antes de que Laravel la valide
        if ($canalPago === 'tarjeta' && $request->has('numero_tarjeta')) {
            $request->merge([
                'numero_tarjeta' => str_replace(' ', '', $request->numero_tarjeta)
            ]);
        }

        // Si es Efectivo, validamos que el dinero alcance
        if ($canalPago === 'efectivo') {
            $reglas['monto_recibido'] = 'required|numeric|min:' . $request->input('total_liquidado', 0);
        }
        // Si es Tarjeta, validamos número exacto, CVV y la fecha lógicamente
        else if ($canalPago === 'tarjeta') {
            $reglas['numero_tarjeta'] = 'required|digits:16';
            $reglas['cvv'] = 'required|digits_between:3,4';
            $reglas['fecha_vencimiento'] = [
                'required',
                'regex:/^(0[1-9]|1[0-2])\/\d{2}$/',
                function ($attribute, $value, $fail) {
                    $partes = explode('/', $value);
                    if (count($partes) == 2) {
                        $mesVencimiento = (int) $partes[0];
                        $anioVencimiento = (int) ('20' . $partes[1]);

                        $mesActual = (int) date('m');
                        $anioActual = (int) date('Y');

                        // Si el año es menor, o si es el mismo año pero el mes ya pasó
                        if ($anioVencimiento < $anioActual || ($anioVencimiento == $anioActual && $mesVencimiento < $mesActual)) {
                            $fail('Operación rechazada: La tarjeta ingresada se encuentra vencida.');
                        }
                    }
                }
            ];
        }

        // Ejecutamos la validación
        $request->validate($reglas, [
            'total_liquidado.gt' => 'El total del recibo debe ser mayor a $0.00.',
            'monto_recibido.min' => 'El efectivo recibido no alcanza para cubrir el total.',
            'monto_recibido.required' => 'Debe ingresar el monto de efectivo recibido.',
            'numero_tarjeta.required' => 'Debe ingresar el número completo de la tarjeta.',
            'numero_tarjeta.digits' => 'El número de tarjeta debe contener exactamente 16 dígitos numéricos.',
            'cvv.required' => 'Debe ingresar el código de seguridad (CVV).',
            'cvv.digits_between' => 'El código de seguridad (CVV) debe contener 3 o 4 dígitos.',
            'fecha_vencimiento.required' => 'Debe ingresar la fecha de vencimiento.',
            'fecha_vencimiento.regex' => 'La fecha de vencimiento es inválida. Use el formato MM/AA.'
        ]);

        DB::beginTransaction();

        try {
            $totalLiquidarRecibido = $request->input('total_liquidado');
            $metodoPago = strtoupper($canalPago);

            $ultimoRecibo = DB::table('pago')->orderBy('pago_id', 'desc')->first();
            $numeroCorrelativo = $ultimoRecibo ? intval(str_replace('REC-2026-', '', $ultimoRecibo->numero_recibo)) + 1 : 1;
            $reciboNuevo = 'REC-2026-' . str_pad($numeroCorrelativo, 4, '0', STR_PAD_LEFT);

            $idPago = null;
            $detallesParaCorreo = [];
            $totalRealFacturado = 0;

            $hoy = now();
            $promocionesVigentes = DB::table('promociones')->where('fecha_inicio', '<=', $hoy)->where('fecha_fin', '>=', $hoy)->get();

            // ==========================================
            // ESCENARIO A: PAGO DE DEUDA (Múltiples Cargos)
            // ==========================================
            if ($request->has('cargos_ids')) {
                $cargosIds = $request->input('cargos_ids', []);

                $cargos = DB::table('cargo_estudiante')
                    ->join('concepto_pago', 'cargo_estudiante.id_concepto', '=', 'concepto_pago.concepto_pago_id')
                    ->select('cargo_estudiante.*', 'concepto_pago.nombre as nombre_concepto')
                    ->whereIn('cargo_id', $cargosIds)
                    ->get();

                if ($cargos->isEmpty()) throw new \Exception('No se encontraron las deudas en el sistema.');

                // --- NUEVO: TRAEMOS LAS REGLAS DE MORA ANTES DEL BUCLE ---
                $fechaHoyStart = now()->startOfDay();
                $reglasActivas = DB::table('reglas_pago')
                    ->join('concepto_pago', 'reglas_pago.id_concepto_recargo', '=', 'concepto_pago.concepto_pago_id')
                    ->select('reglas_pago.tipo', 'concepto_pago.monto_base', 'concepto_pago.nombre as nombre_recargo')
                    ->where('reglas_pago.estado', 'ACTIVO')
                    ->get();

                $reglaMensualidad = $reglasActivas->firstWhere('tipo', 'Mensualidad');
                $reglaInscripcion = $reglasActivas->firstWhere('tipo', 'Inscripción');
                // ---------------------------------------------------------

                $idAlumno = $cargos->first()->id_alumno;

                $idPago = DB::table('pago')->insertGetId([
                    'id_cajero' => Auth::id() ?? 1,
                    'id_alumno' => $idAlumno,
                    'numero_recibo' => $reciboNuevo,
                    'canal_pago' => 'VENTANILLA',
                    'metodo_pago' => $metodoPago,
                    'total' => $totalLiquidarRecibido,
                    'fecha_pago' => now(),
                ], 'pago_id');

                // Marcamos todas las deudas como pagadas de un solo golpe
                DB::table('cargo_estudiante')->whereIn('cargo_id', $cargosIds)->update(['estado_cargo' => 'PAGADO']);

                $detallesInsertar = [];
                $sumaTotalCalculada = 0;

                foreach ($cargos as $cargo) {

                    // 1. LEEMOS EL BENEFICIO (Igual que en la pantalla)
                    $montoBaseCargo = $cargo->monto_neto_estudiante ?? $cargo->monto_original;

                    // --- NUEVO: CÁLCULO DE LA MORA PARA ESTE CARGO ---
                    $montoMora = 0;
                    if ($cargo->fecha_vencimiento) {
                        $fechaVencimiento = \Carbon\Carbon::parse($cargo->fecha_vencimiento)->startOfDay();
                        if ($fechaHoyStart->gt($fechaVencimiento)) {
                            $nombreCargo = strtoupper($cargo->nombre_concepto);
                            $reglaAplicable = null;
                            if (str_contains($nombreCargo, 'MENSUAL')) {
                                $reglaAplicable = $reglaMensualidad;
                            } elseif (str_contains($nombreCargo, 'MATRICULA') || str_contains($nombreCargo, 'INSCRIPCI')) {
                                $reglaAplicable = $reglaInscripcion;
                            }
                            if ($reglaAplicable) {
                                $montoMora = $reglaAplicable->monto_base;
                            }
                        }
                    }
                    // ------------------------------------------------

                    // Promociones
                    $descuentoDinero = 0;
                    $promoEspecifica = $promocionesVigentes->where('id_concepto_aplicable', $cargo->id_concepto)->first();
                    $promoGlobal = $promocionesVigentes->whereNull('id_concepto_aplicable')->first();
                    $mejorPorcentaje = max($promoEspecifica ? $promoEspecifica->porcentaje_descuento : 0, $promoGlobal ? $promoGlobal->porcentaje_descuento : 0);

                    if ($mejorPorcentaje > 0) {
                        // El descuento aplica a la cuota base
                        $descuentoDinero = ($montoBaseCargo * $mejorPorcentaje) / 100;
                    }

                    // --- CAMBIO CLAVE: Sumamos la mora al subtotal ---
                    $subtotalFinal = ($montoBaseCargo + $montoMora) - $descuentoDinero;
                    $sumaTotalCalculada += $subtotalFinal;

                    $detallesInsertar[] = [
                        'id_pago' => $idPago,
                        'id_cargo' => $cargo->cargo_id,
                        'id_concepto' => $cargo->id_concepto,
                        'monto_base' => $montoBaseCargo,
                        'descuento_aplicado' => $descuentoDinero,
                        'recargo_aplicado' => $montoMora, // AHORA SÍ SE GUARDA LA MORA EN CONTABILIDAD
                        'cantidad' => 1,
                        'subtotal' => $subtotalFinal
                    ];

                    $mes = str_pad($cargo->mes_arancel, 2, '0', STR_PAD_LEFT);

                    // --- NUEVO: DIBUJAMOS EL TEXTO FINAL PARA EL RECIBO ---
                    $textoRecibo = $cargo->nombre_concepto . " ($mes/$cargo->anio_arancel)";

                    // Si hubo mora, se lo pegamos al nombre para que aparezca en la descripción del recibo
                    if ($montoMora > 0) {
                        $textoRecibo .= " (+ $" . number_format($montoMora, 2) . " MORA)";
                    }
                    if ($mejorPorcentaje > 0) {
                        $textoRecibo .= " (Desc. $mejorPorcentaje%)";
                    }

                    $detallesParaCorreo[] = [
                        'nombre' => $textoRecibo,
                        'monto' => $subtotalFinal
                    ];
                }

                DB::table('detalle_pago')->insert($detallesInsertar);
                $totalRealFacturado = $sumaTotalCalculada;
            }
            // ==========================================
            // ESCENARIO B: VENTA DIRECTA (Ventanilla Múltiple)
            // ==========================================
            else if ($request->has('aranceles_ids')) {
                $arancelesIds = $request->input('aranceles_ids', []);
                $codigoEstudiante = strtoupper(trim($request->input('codigo_estudiante')));

                if (empty($arancelesIds)) throw new \Exception('No se enviaron aranceles para cobrar.');

                $alumno = DB::table('alumnos')->where('codigo_estudiante', $codigoEstudiante)->first();
                if (!$alumno) throw new \Exception('El carnet ' . strtoupper($codigoEstudiante) . ' no se encontró.');

                $aranceles = DB::table('concepto_pago')->whereIn('concepto_pago_id', $arancelesIds)->get();
                $sumaTotalCalculada = 0;

                $idPago = DB::table('pago')->insertGetId([
                    'id_cajero' => Auth::id() ?? 1,
                    'id_alumno' => $alumno->alumno_id,
                    'numero_recibo' => $reciboNuevo,
                    'canal_pago' => 'VENTANILLA',
                    'metodo_pago' => $metodoPago,
                    'total' => $totalLiquidarRecibido,
                    'fecha_pago' => now(),
                ], 'pago_id');

                $detallesInsertar = [];
                foreach ($aranceles as $arancel) {
                    $descuentoDinero = 0;
                    $promoEspecifica = $promocionesVigentes->where('id_concepto_aplicable', $arancel->concepto_pago_id)->first();
                    $promoGlobal = $promocionesVigentes->whereNull('id_concepto_aplicable')->first();
                    $mejorPorcentaje = max($promoEspecifica ? $promoEspecifica->porcentaje_descuento : 0, $promoGlobal ? $promoGlobal->porcentaje_descuento : 0);

                    if ($mejorPorcentaje > 0) {
                        $descuentoDinero = ($arancel->monto_base * $mejorPorcentaje) / 100;
                    }

                    $subtotalFinal = $arancel->monto_base - $descuentoDinero;
                    $sumaTotalCalculada += $subtotalFinal;

                    $detallesInsertar[] = [
                        'id_pago' => $idPago,
                        'id_cargo' => null,
                        'id_concepto' => $arancel->concepto_pago_id,
                        'monto_base' => $arancel->monto_base,
                        'descuento_aplicado' => $descuentoDinero,
                        'recargo_aplicado' => 0.00, // Venta directa no tiene mora
                        'cantidad' => 1,
                        'subtotal' => $subtotalFinal
                    ];

                    $detallesParaCorreo[] = [
                        'nombre' => $arancel->nombre . ($mejorPorcentaje > 0 ? " (Desc. $mejorPorcentaje%)" : ""),
                        'monto' => $subtotalFinal
                    ];
                }

                DB::table('detalle_pago')->insert($detallesInsertar);
                $totalRealFacturado = $sumaTotalCalculada;
            } else {
                throw new \Exception('No se encontraron cargos ni aranceles para procesar.');
            }

            DB::commit();

            // --- LÓGICA DE CORREO (Se mantiene igual a lo que ya tenías) ---
            try {
                $idAlumnoPagador = DB::table('pago')->where('pago_id', $idPago)->value('id_alumno');
                $alumnoMail = DB::table('alumnos')->where('alumno_id', $idAlumnoPagador)->first();
                $correoDestino = ($alumnoMail && !empty($alumnoMail->correo_institucional)) ? $alumnoMail->correo_institucional : 'amelara583@gmail.com';
                $codigoEstudianteMail = $alumnoMail ? $alumnoMail->codigo_estudiante : 'N/A';

                $nombreEstudiante = 'Estudiante UMA';
                if ($alumnoMail && $alumnoMail->id_usuario) {
                    $usuarioEstudiante = DB::table('users')->where('id', $alumnoMail->id_usuario)->first();
                    if ($usuarioEstudiante) {
                        $nombreEstudiante = $usuarioEstudiante->name;
                    }
                }

                $nombreCajero = Auth::user()->name ?? 'Cajero UMA';
                $fechaFormateada = now()->format('d/m/Y h:i A');

                \Illuminate\Support\Facades\Mail::to($correoDestino)->send(new \App\Mail\ReciboPagoMail(
                        $reciboNuevo,
                        $totalRealFacturado,
                        $detallesParaCorreo,
                        $metodoPago,
                        $nombreCajero,
                        $fechaFormateada,
                        $nombreEstudiante,
                        $codigoEstudianteMail
                ));

            } catch (\Exception $mailError) {
                \Illuminate\Support\Facades\Log::error('Fallo correo: ' . $mailError->getMessage());
            }

            return redirect()->route('cajero.ventanilla.index')
                             ->with('success', 'Cobro realizado con éxito. Recibo emitido: ' . $reciboNuevo);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Ocurrió un error al procesar el pago: ' . $e->getMessage());
        }
    }

    //deuda pendiente ver la caja con el nombre y el carnet
    public function verificarAlumnoAPI($carnet)
    {
        try {
            $alumno = \Illuminate\Support\Facades\DB::table('alumnos')
                ->join('users', 'alumnos.id_usuario', '=', 'users.id')
                // Hacemos LEFT JOIN con carreras por si el estudiante no tiene carrera asignada
                ->leftJoin('carreras', 'alumnos.id_carrera', '=', 'carreras.carrera_id')
                // Hacemos LEFT JOIN con beneficios por si no tiene beneficio asignado
                ->leftJoin('beneficios_estudiante', 'alumnos.alumno_id', '=', 'beneficios_estudiante.id_alumno')
                ->select(
                    'alumnos.codigo_estudiante as carnet',
                    'users.name as nombres',
                    'carreras.nombre as carrera',
                    'beneficios_estudiante.tipo_beneficio as beneficio_crudo'
                )
                ->where('alumnos.codigo_estudiante', strtoupper(trim($carnet)))
                ->first();

            if ($alumno) {
                // Limpiamos y formateamos el beneficio (ej. de BECA_PARCIAL a Beca Parcial)
                $beneficioFormateado = 'Sin beneficio';
                if (!empty($alumno->beneficio_crudo)) {
                    $beneficioFormateado = ucwords(strtolower(str_replace('_', ' ', $alumno->beneficio_crudo)));
                }

                return response()->json([
                    'success' => true,
                    'alumno' => [
                        'nombres' => $alumno->nombres,
                        'carnet'  => $alumno->carnet,
                        'carrera' => $alumno->carrera ?? 'Sin carrera',
                        'beneficio' => $beneficioFormateado
                    ]
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'El carnet ingresado no corresponde a ningún alumno registrado.'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error del servidor: ' . $e->getMessage()
            ], 500);
        }
    }


}
