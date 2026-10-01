<?php

namespace App\Http\Controllers\Cajero;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Adfinanciero\CargoEstudiante; 
use App\Models\Adfinanciero\ConceptoPago;
use Illuminate\Support\Facades\DB; // Para actualizar el estado y guardar el pago
use Illuminate\Support\Facades\Mail; // Para el correo
use Illuminate\Support\Facades\Auth; // Para saber qué cajero cobró

class CajeroController extends Controller
{

    //Muestra la pantalla de ventanilla y busca cargos PENDIENTES.
    public function deudaIndex(Request $request)
    {
        $cargos = collect();

        if ($request->filled('id_alumno')) {

            // 1. SANITIZAR EL INPUT (Ahora forzamos MINÚSCULAS)
            $request->merge([
                'id_alumno' => strtolower(trim($request->id_alumno))
            ]);

            // 2. VALIDACIÓN
            $request->validate([
                'id_alumno' => 'required|string|exists:alumnos,codigo_estudiante'
            ], [
                'id_alumno.exists' => 'Error: El código ingresado no corresponde a ningún alumno registrado.'
            ]);

            // 3. OBTENER EL ID NUMÉRICO
            $alumno = \Illuminate\Support\Facades\DB::table('alumnos')
                        ->where('codigo_estudiante', $request->id_alumno)
                        ->first();


            // 4. BÚSQUEDA DE CARGOS
            $cargos = CargoEstudiante::with('concepto')
                ->where('id_alumno', $alumno->alumno_id)
                ->where('estado_cargo', 'PENDIENTE')
                ->orderBy('fecha_vencimiento', 'asc')
                ->get();
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

    public function promociones()
    {
        // Traemos solo las promociones activas (Ajusta 'estado', 1 si tu BD usa booleanos, o 'ACTIVO' si usa texto)
        $promociones = \App\Models\Adfinanciero\Promocion::where('estado', true)->get();

        return view('cajero.promociones.index', compact('promociones'));
    }

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

        // --- VARIABLE NUEVA PARA LA VISTA ---
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

                    // Armamos un objeto limpio para la vista
                    $alumno = (object)[
                        'nombres' => $usuarioRelacionado ? $usuarioRelacionado->name : 'Estudiante',
                        'apellidos' => '',
                        'carnet' => $alumnoBD->codigo_estudiante
                    ];
                }
            }

            foreach ($cargos as $cargo) {
                $descuentoDinero = 0;

                // Buscar promos aplicables
                $promoEspecifica = $promocionesVigentes->where('id_concepto_aplicable', $cargo->id_concepto)->first();
                $promoGlobal = $promocionesVigentes->whereNull('id_concepto_aplicable')->first();

                // Regla del Mayor Beneficio
                $porcentajeEspecifico = $promoEspecifica ? $promoEspecifica->porcentaje_descuento : 0;
                $porcentajeGlobal = $promoGlobal ? $promoGlobal->porcentaje_descuento : 0;
                $mejorPorcentaje = max($porcentajeEspecifico, $porcentajeGlobal);

                if ($mejorPorcentaje > 0) {
                    $descuentoDinero = ($cargo->monto_original * $mejorPorcentaje) / 100;
                }

                $subtotalFinal = $cargo->monto_original - $descuentoDinero;
                $totalLiquidar += $subtotalFinal;

                // Formateamos el nombre agregando el Mes y Año (Ej. 10/2026)
                $mes = str_pad($cargo->mes_arancel, 2, '0', STR_PAD_LEFT);
                $nombreCompleto = $cargo->nombre_concepto . " ($mes/$cargo->anio_arancel)";

                $arancelesACobrar->push((object)[
                    'concepto_pago_id' => $cargo->id_concepto,
                    'id_cargo' => $cargo->cargo_id, // Vital para saber qué deuda marcar como PAGADA
                    'nombre' => $nombreCompleto,
                    'monto_base' => $cargo->monto_original,
                    'monto_final' => $subtotalFinal,
                    'porcentaje_descuento' => $mejorPorcentaje,
                    'descuento_dinero' => $descuentoDinero
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
                    $alumno = (object)[
                        'nombres' => $usuarioRelacionado ? $usuarioRelacionado->name : 'Estudiante',
                        'apellidos' => '',
                        'carnet' => $alumnoBD->codigo_estudiante
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
                    'descuento_dinero' => $descuentoDinero
                ]);
            }
        } else {
            // Protección por si ingresan a la ruta sin seleccionar nada
            return redirect()->route('cajero.ventanilla.index')->with('error', 'No se enviaron datos para procesar el cobro.');
        }

        // Enviamos TODO a la vista del recibo, INCLUYENDO la nueva variable $alumno
        return view('cajero.pagos.create', compact('arancelesACobrar', 'totalLiquidar', 'metodoPagoEscogido', 'esPagoDeuda', 'alumno'));
    }


    public function guardarPago(Request $request)
    {
        // 1. Armamos las reglas básicas dinámicas
        $reglas = [
            'total_liquidado' => 'required|numeric|gt:0',
        ];

        $canalPago = strtolower($request->input('canal', 'efectivo'));

        // Si es Efectivo, validamos que el dinero alcance
        if ($canalPago === 'efectivo') {
            $reglas['monto_recibido'] = 'required|numeric|min:' . $request->input('total_liquidado', 0);
        }
        // Si es Tarjeta, validamos los 4 dígitos y la fecha MM/AA
        else if ($canalPago === 'tarjeta') {
            $reglas['numero_tarjeta'] = 'required|digits:4';
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

                        if ($anioVencimiento < $anioActual || ($anioVencimiento == $anioActual && $mesVencimiento < $mesActual)) {
                            $fail('No se puede procesar el pago. La tarjeta ingresada se encuentra vencida.');
                        }
                    }
                }
            ];
        }

        // Ejecutamos la validación con mensajes en español
        $request->validate($reglas, [
            'total_liquidado.gt' => 'El total del recibo debe ser mayor a $0.00.',
            'monto_recibido.min' => 'El efectivo recibido no alcanza para cubrir el total.',
            'monto_recibido.required' => 'Debe ingresar el monto de efectivo recibido.',
            'numero_tarjeta.required' => 'Debe ingresar la terminación de la tarjeta.',
            'numero_tarjeta.digits' => 'El número de tarjeta debe contener exactamente 4 dígitos (Ej. 4091).',
            'fecha_vencimiento.required' => 'Debe ingresar la fecha de vencimiento.',
            'fecha_vencimiento.regex' => 'La fecha de vencimiento es inválida. Use el formato MM/AA (Ej. 12/28).'
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

                // Tomamos el alumno del primer cargo (todas las deudas son del mismo)
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
                    $descuentoDinero = 0;
                    $promoEspecifica = $promocionesVigentes->where('id_concepto_aplicable', $cargo->id_concepto)->first();
                    $promoGlobal = $promocionesVigentes->whereNull('id_concepto_aplicable')->first();
                    $mejorPorcentaje = max($promoEspecifica ? $promoEspecifica->porcentaje_descuento : 0, $promoGlobal ? $promoGlobal->porcentaje_descuento : 0);

                    if ($mejorPorcentaje > 0) {
                        $descuentoDinero = ($cargo->monto_original * $mejorPorcentaje) / 100;
                    }

                    $subtotalFinal = $cargo->monto_original - $descuentoDinero;
                    $sumaTotalCalculada += $subtotalFinal;

                    $detallesInsertar[] = [
                        'id_pago' => $idPago,
                        'id_cargo' => $cargo->cargo_id,
                        'id_concepto' => $cargo->id_concepto,
                        'monto_base' => $cargo->monto_original,
                        'descuento_aplicado' => $descuentoDinero,
                        'recargo_aplicado' => 0.00,
                        'cantidad' => 1,
                        'subtotal' => $subtotalFinal
                    ];

                    $mes = str_pad($cargo->mes_arancel, 2, '0', STR_PAD_LEFT);
                    $detallesParaCorreo[] = [
                        'nombre' => $cargo->nombre_concepto . " ($mes/$cargo->anio_arancel)" . ($mejorPorcentaje > 0 ? " (Desc. $mejorPorcentaje%)" : ""),
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
                $codigoEstudiante = strtolower(trim($request->input('codigo_estudiante')));

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
                        'recargo_aplicado' => 0.00,
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
                // 1. Obtenemos el ID del alumno directo del pago recién creado
                $idAlumnoPagador = DB::table('pago')->where('pago_id', $idPago)->value('id_alumno');

                // 2. Buscamos toda la información del alumno
                $alumnoMail = DB::table('alumnos')->where('alumno_id', $idAlumnoPagador)->first();

                // 3. Extraemos correos y el carnet
                $correoDestino = ($alumnoMail && !empty($alumnoMail->correo_institucional)) ? $alumnoMail->correo_institucional : 'amelara583@gmail.com';
                $codigoEstudianteMail = $alumnoMail ? $alumnoMail->codigo_estudiante : 'N/A';

                // 4. Obtenemos el nombre real desde la tabla users
                $nombreEstudiante = 'Estudiante UMA';
                if ($alumnoMail && $alumnoMail->id_usuario) {
                    $usuarioEstudiante = DB::table('users')->where('id', $alumnoMail->id_usuario)->first();
                    if ($usuarioEstudiante) {
                        $nombreEstudiante = $usuarioEstudiante->name;
                    }
                }

                $nombreCajero = Auth::user()->name ?? 'Cajero UMA';
                $fechaFormateada = now()->format('d/m/Y h:i A');

                // 5. Enviamos TODOS los parámetros al Mailable, incluyendo el carnet al final
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

            // Redirección de éxito
            return redirect()->route('cajero.ventanilla.index')
                             ->with('success', 'Cobro realizado con éxito. Recibo emitido: ' . $reciboNuevo);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Ocurrió un error al procesar el pago: ' . $e->getMessage());
        }
    }


}
