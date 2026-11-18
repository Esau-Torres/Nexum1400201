<?php

namespace App\Http\Controllers\Estudiante; // ¡El namespace cambió a Estudiante!

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Adfinanciero\ConceptoPago;
use App\Models\Adfinanciero\CicloLectivo;
use App\Models\Adfinanciero\CicloArancelPeriodo;
use App\Models\Estudiante\CargoEstudiante;

class EstadoCuentaController extends Controller
{
    // LÓGICA PARA PROCESAR EL PAGO EN LÍNEA DESDE LA VISTA DEL ESTUDIANTE ---
    public function cargosIndex(Request $request)
    {
        // 1. Obtenemos el ID del alumno logueado
        $alumnoId = \Illuminate\Support\Facades\Auth::user()->alumno->alumno_id ?? null;

        // --- NUEVA LÓGICA DE SEGURIDAD ABSOLUTA (BLOQUEOS Y AUTOPAGO) ---
        $bloquearPantalla = false;
        $mesPrimeraCuota = '';
        $tipoAutopayServidor = null;

        $cicloActivo = \Illuminate\Support\Facades\DB::table('ciclo_lectivo')->where('estado', 'ACTIVO')->first();

        if ($alumnoId && $cicloActivo) {

            // REGLA A: ¿Tiene deudas de ciclos pasados?
            $tieneDeudaAnterior = \Illuminate\Support\Facades\DB::table('cargo_estudiante')
                ->where('id_alumno', $alumnoId)
                ->where('id_ciclo_lectivo', '!=', $cicloActivo->ciclo_lectivo_id)
                ->where('estado_cargo', 'PENDIENTE')
                ->exists();

            if ($tieneDeudaAnterior) {
                $bloquearPantalla = true;
                $tipoAutopayServidor = 'all'; // Forzamos a que pague TODO
            } else {
                // REGLA B: ¿Debe Matrícula o Primer Mes del ciclo actual?
                $esCiclo01 = str_contains($cicloActivo->nombre_ciclo, '01') || $cicloActivo->numero_ciclo == '01';
                $mesPrimeraCuota = $esCiclo01 ? 'Enero' : 'Junio';

                $cargosBloqueantes = CargoEstudiante::with('concepto')
                    ->where('id_alumno', $alumnoId)
                    ->where('id_ciclo_lectivo', $cicloActivo->ciclo_lectivo_id)
                    ->where('estado_cargo', 'PENDIENTE')
                    ->get();

                $debeMatricula = false;
                $debePrimeraCuota = false;

                foreach ($cargosBloqueantes as $cargo) {
                    $nombreArancel = strtoupper($cargo->concepto->nombre ?? '');
                    if (str_contains($nombreArancel, 'MATRÍCULA') || str_contains($nombreArancel, 'MATRICULA') || str_contains($nombreArancel, 'INSCRIPCI')) {
                        $debeMatricula = true;
                    }
                    if ((str_contains($nombreArancel, 'CUOTA') || str_contains($nombreArancel, 'MENSUAL')) && strcasecmp($cargo->mes_arancel, $mesPrimeraCuota) === 0) {
                        $debePrimeraCuota = true;
                    }
                }

                if ($debeMatricula || $debePrimeraCuota) {
                    $bloquearPantalla = true;
                    $tipoAutopayServidor = 'inicio'; // Forzamos a que pague el inicio
                }
            }
        }

        // --- SECCIÓN DE DEUDAS (CARGOS) ---
        $query = CargoEstudiante::with(['ciclo', 'concepto']);

        if ($alumnoId) {
            $query->where('id_alumno', $alumnoId);
        } elseif ($request->filled('search')) {
            $query->where('id_alumno', $request->search);
        }

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

            // Suma el precio ya con el descuento de la beca aplicado
            $totalPendiente += floatval($cargoPendiente->monto_neto_estudiante ?? $cargoPendiente->monto_original) + $moraSumar;
        }


        $query->orderBy('fecha_emision', 'asc');
        $cargos = $query->paginate(15)->appends($request->all());

        // --- SECCIÓN DE HISTORIAL DE PAGOS (RECIBOS) ---
        $queryPagos = \App\Models\Adfinanciero\Pago::with(['detalles.cargo', 'detalles.concepto'])
            ->whereHas('detalles.concepto', function ($q) {
                $q->where('nombre', 'ILIKE', '%CUOTA%')
                  ->orWhere('nombre', 'ILIKE', '%MATRÍCULA%')
                  ->orWhere('nombre', 'ILIKE', '%MATRICULA%')
                  ->orWhere('nombre', 'ILIKE', '%LABORATORIO%');
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

        // AQUÍ ESTÁ EL CAMBIO CLAVE: Cambiamos la ruta de la vista a la carpeta student
        return view('student.cargosestudiante.index', compact('cargos', 'ciclos', 'conceptos', 'totalPendiente', 'totalPagado', 'pagos', 'montoMora', 'bloquearPantalla', 'mesPrimeraCuota', 'cicloActivo', 'tipoAutopayServidor'));
    }

    // pago en linea, se calcula la mora para mensualidad y matricula
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
            $cargos = CargoEstudiante::whereIn('cargo_id', $request->cargos_ids)
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

                $montoNetoEstudiante = floatval($cargo->monto_neto_estudiante ?? $cargo->monto_original);

                $subtotal = $montoNetoEstudiante + floatval($moraAplicada);
                $totalMonto += $subtotal;

                $detallesAGuardar[] = [
                    'id_cargo' => $cargo->cargo_id,
                    'id_concepto' => $cargo->id_concepto,
                    'monto_base' => $montoNetoEstudiante,
                    'descuento_aplicado' => floatval($cargo->monto_absorbido_universidad ?? 0),
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
