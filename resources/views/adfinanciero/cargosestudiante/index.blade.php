@extends('layouts.app')

@section('content')
<style>
    /* Colores oficiales de Nexum */
    .nav-pills .nav-link.active {
        background-color: #dc3545 !important;
        color: white !important;
    }
    .nav-pills .nav-link {
        color: #495057;
    }
    .text-nexum-blue {
        color: #dc3545 !important;
    }
    .btn-nexum-blue {
        background-color: #5dade2 !important;
        color: white !important;
        border: none;
    }
</style>

<div class="container mt-4">

    <!-- Encabezado y Datos del Estudiante -->
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <h2 class="fw-bold text-dark mb-1"><i class="bi bi-wallet2 me-2" style="color: #dc3545;"></i> MI ESTADO DE CUENTA</h2>
            <p class="text-muted mb-0">
                <span class="fw-bold text-dark">{{ Auth::user()->name ?? 'Estudiante' }}</span> | {{ Auth::user()->alumno->carrera->nombre ?? 'Carrera no definida' }} <br>

                @php
                    // Buscamos el ciclo directamente en la BD con los 3 estados operativos (a prueba de ENUM)
                    $cicloActual = \Illuminate\Support\Facades\DB::table('ciclo_lectivo')
                        ->whereRaw("estado::text IN ('CONFIGURACION', 'MATRICULA_ABIERTA', 'ACTIVO')")
                        ->orderBy('ciclo_lectivo_id', 'desc')
                        ->first();

                    // Buscamos si el estudiante actual tiene un beneficio activo en la BD
                    $beneficioActivo = \Illuminate\Support\Facades\DB::table('beneficios_estudiante')
                        ->where('id_alumno', Auth::user()->alumno->alumno_id ?? 0)
                        ->where('estado', 'ACTIVO')
                        ->first();

                    $textoBeneficio = 'Tarifa Regular';
                    $esBecaCompleta = false; // VARIABLE MÁGICA DE CONTROL

                    if ($beneficioActivo) {
                        // Formateamos para que se vea bonito en la viñeta
                        $textoBeneficio = ucwords(strtolower(str_replace('_', ' ', $beneficioActivo->tipo_beneficio)));

                        // Validamos de forma segura sin importar si en la BD está en mayúsculas o minúsculas
                        $esBecaCompleta = strtolower(trim($beneficioActivo->tipo_beneficio)) === 'beca_completa';
                    }
                @endphp

                <span class="badge text-white mt-2 me-2" style="background-color: #dc3545;">
                    Ciclo Actual: {{ $cicloActual ? $cicloActual->nombre_ciclo . ' (' . ucwords(strtolower(str_replace('_', ' ', $cicloActual->estado))) . ')' : 'No definido' }}
                </span>

                @if($beneficioActivo)
                    <span class="badge text-white mt-2 shadow-sm" style="background-color: #dc3545;">
                        <i class="bi bi-award-fill me-1"></i> Beneficio: {{ strtoupper($textoBeneficio) }}
                    </span>
                @else
                    <span class="badge text-white mt-2 shadow-sm" style="background-color: #dc3545; opacity: 0.8;">
                        Tarifa Regular
                    </span>
                @endif

            </p>
        </div>

        <!-- Tarjetas de Resumen Financiero -->
        <div class="col-md-4">
            <div class="row g-2">
                <div class="col-6">
                    <div class="card border-0 shadow-sm h-100" style="background-color: #dc3545; color: white;">
                        <div class="card-body p-3 text-center">
                            <h6 class="mb-1" style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px;">Saldo pendiente</h6>
                            <h4 class="fw-bold mb-0" id="resumenDeuda">${{ number_format($totalPendiente, 2) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card border-0 shadow-sm h-100" style="background-color: #dc3545; color: white;">
                        <div class="card-body p-3 text-center">
                            <h6 class="mb-1 text-white-50" style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px;">Total pagado</h6>
                            <h4 class="fw-bold mb-0 text-white">${{ number_format($totalPagado, 2) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Menú de Pestañas y Botón de Pago -->
    <div class="card bg-white border-0 shadow-sm rounded-4 mb-4 p-2">
        <div class="d-flex justify-content-between align-items-center flex-wrap">

            <ul class="nav nav-pills mb-0" id="estadoCuentaTabs" role="tablist">
                @if($esBecaCompleta)
                    <!-- ESCENARIO 1: BECA COMPLETA (Muestra una única pestaña activa) -->
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active px-4 py-2 d-flex align-items-center" id="pagos-tab" data-bs-toggle="tab" data-bs-target="#pagos" type="button" role="tab" aria-controls="pagos" aria-selected="true" style="border-radius: 8px;">
                            <i class="bi bi-clock-history me-2"></i> Historial de pagos
                        </button>
                    </li>
                @else
                    <!-- ESCENARIO 2: REGULAR O BECA PARCIAL (Muestra ambas pestañas normales) -->
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active px-4 py-2 d-flex align-items-center" id="deuda-tab" data-bs-toggle="tab" data-bs-target="#deuda" type="button" role="tab" aria-controls="deuda" aria-selected="true" style="border-radius: 8px;">
                            @if($cargos->where('estado_cargo', 'PENDIENTE')->count() > 0)
                                <i class="bi bi-exclamation-circle me-2"></i> Pendientes de pago
                            @else
                                <i class="bi bi-check-circle me-2"></i> Sin deudas
                            @endif
                        </button>
                    </li>
                    <li class="nav-item ms-2" role="presentation">
                        <button class="nav-link text-dark px-4 py-2 d-flex align-items-center" id="pagos-tab" data-bs-toggle="tab" data-bs-target="#pagos" type="button" role="tab" aria-controls="pagos" aria-selected="false" style="border-radius: 8px;">
                            <i class="bi bi-clock-history me-2"></i> Historial de pagos
                        </button>
                    </li>
                @endif
            </ul>

            <!-- El botón de pagar solo se dibuja si NO es beca completa -->
            @if(!$esBecaCompleta)
                <button type="button" class="btn text-white fw-bold d-flex align-items-center px-4 py-2 me-1 mt-2 mt-md-0" style="background-color: #dc3545; opacity: 0.6; border-radius: 8px; transition: all 0.3s ease;" id="btnPagarAranceles" disabled>
                    <i class="bi bi-receipt me-2"></i> Pagar
                    <span class="badge bg-white text-danger rounded-circle ms-2 shadow-sm" id="contadorSeleccionados" style="padding: 0.4em 0.6em; font-size: 0.9em;">0</span>
                </button>
            @endif

        </div>
    </div>

    <!-- Contenido de las Pestañas -->
    <div class="tab-content" id="estadoCuentaTabsContent">

        <!-- PESTAÑA 1: DEUDAS Y PAGO EN LÍNEA (Se oculta por completo para la Beca Completa) -->
        @if(!$esBecaCompleta)
            <div class="tab-pane fade show active" id="deuda" role="tabpanel" tabindex="0">
                <form id="formPagoEnLinea" action="{{ route('adfinanciero.cargos.pagar') }}" method="POST" style="display: none;">
                    @csrf
                </form>

                <div class="row">
                    <div class="col-12 mb-4">
                        <div class="card border-0 shadow-sm rounded-4 bg-white">
                            <div class="card-header bg-white border-bottom p-3">
                                <h6 class="fw-bold mb-0 text-dark">Selecciona los aranceles a pagar</h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="ps-4" style="width: 50px;">
                                                    <i class="bi bi-check2-square text-muted"></i>
                                                </th>
                                                <th>Ciclo</th>
                                                <th>Mes</th>
                                                <th>Descripción</th>
                                                <th>Vencimiento</th>
                                                <th class="text-end pe-4">Monto</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tablaDeudas">
                                            @forelse($cargos->where('estado_cargo', 'PENDIENTE') as $index => $cargo)
                                            <tr>
                                                @php
                                                    $fechaVencimiento = \Carbon\Carbon::parse($cargo->fecha_vencimiento);
                                                    $hoy = \Carbon\Carbon::now();
                                                    $estaVencido = $hoy->gt($fechaVencimiento);

                                                    $nombreConcepto = strtoupper($cargo->concepto->nombre ?? 'CUOTA MENSUAL');
                                                    $esMensualidad = (str_contains($nombreConcepto, 'CUOTA') || str_contains($nombreConcepto, 'MENSUAL'));
                                                    $esMatricula = (str_contains($nombreConcepto, 'MATRÍCULA') || str_contains($nombreConcepto, 'MATRICULA') || str_contains($nombreConcepto, 'INSCRIPCI'));

                                                    $moraAplicada = 0;
                                                    $montoBaseArancel = floatval($cargo->monto_neto_estudiante ?? $cargo->monto_original);

                                                    if ($estaVencido && $montoBaseArancel > 0) {
                                                        if ($esMensualidad) {
                                                            $reglaBD = \Illuminate\Support\Facades\DB::table('reglas_pago')->where('tipo', 'ILIKE', '%Mensualidad%')->first();
                                                            if ($reglaBD && $reglaBD->id_concepto_recargo) {
                                                                $conceptoBD = \Illuminate\Support\Facades\DB::table('concepto_pago')->where('concepto_pago_id', $reglaBD->id_concepto_recargo)->first();
                                                                if ($conceptoBD) $moraAplicada = floatval($conceptoBD->monto_base);
                                                            }
                                                        } elseif ($esMatricula) {
                                                            $reglaBD = \Illuminate\Support\Facades\DB::table('reglas_pago')->where('tipo', 'ILIKE', '%Inscripci%')->first();
                                                            if ($reglaBD && $reglaBD->id_concepto_recargo) {
                                                                $conceptoBD = \Illuminate\Support\Facades\DB::table('concepto_pago')->where('concepto_pago_id', $reglaBD->id_concepto_recargo)->first();
                                                                if ($conceptoBD) $moraAplicada = floatval($conceptoBD->monto_base);
                                                            }
                                                        }
                                                    }

                                                    $totalArancel = $montoBaseArancel + floatval($moraAplicada);
                                                @endphp

                                                <td class="ps-4">
                                                    <input class="form-check-input item-deuda shadow-none" type="checkbox"
                                                           value="{{ $totalArancel }}"
                                                           data-id="{{ $cargo->cargo_id ?? $cargo->id }}"
                                                           data-index="{{ $index }}"
                                                           data-mora="{{ $moraAplicada }}">
                                                </td>
                                                <td>
                                                    <div class="d-flex flex-column align-items-start">
                                                        <span class="badge bg-light text-dark border mb-1">{{ $cargo->ciclo->nombre_ciclo ?? 'N/A' }}</span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="text-capitalize text-dark" style="font-size: 0.95rem;">
                                                        {{ $cargo->mes_arancel ?? 'N/A' }}
                                                    </span>
                                                </td>
                                                <td class="fw-bold text-dark">{{ \Illuminate\Support\Str::before($cargo->concepto->nombre ?? 'N/A', ' DE') }}</td>
                                                <td>
                                                    @if($estaVencido)
                                                        <span style="color: #dc3545; font-weight: 600; display: inline-flex; align-items: center; gap: 5px;">
                                                            {{ $fechaVencimiento->format('d/m/Y') }}
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-exclamation-circle-fill" viewBox="0 0 16 16" style="color: #dc3545;">
                                                              <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM8 4a.905.905 0 0 0-.9.995l.35 3.507a.552.552 0 0 0 1.1 0l.35-3.507A.905.905 0 0 0 8 4zm.002 6a1 1 0 1 0 0 2 1 1 0 0 0 0-2z"/>
                                                            </svg>
                                                        </span>
                                                    @else
                                                        <span class="text-dark">
                                                            {{ $fechaVencimiento->format('d/m/Y') }}
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="text-end pe-4">
                                                    <span class="fw-bold text-dark">${{ number_format($cargo->monto_neto_estudiante ?? $cargo->monto_original, 2) }}</span>
                                                    @if($moraAplicada > 0)
                                                        <br><small class="text-danger fw-bold">+ ${{ number_format($moraAplicada, 2) }} Mora</small>
                                                    @endif
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="6" class="text-center text-muted py-4">No tienes deudas.</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- PESTAÑA 2: HISTORIAL DE PAGOS (Formato Desglosado Kárdex) -->
        <!-- Si tiene beca completa, esta pestaña recibe la clase "show active" para ser la pantalla principal -->
        <div class="tab-pane fade @if($esBecaCompleta) show active @endif" id="pagos" role="tabpanel" tabindex="0">
            <div class="card border-0 shadow-sm rounded-4 bg-white">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">N° Recibo</th>
                                    <th>Ciclo</th>
                                    <th>Mes</th>
                                    <th>Descripción</th>
                                    <th>Fecha de pago</th>
                                    <th class="text-end pe-4">Monto pagado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $kardex = collect();
                                    $ordenMeses = [
                                        'enero' => 1, 'febrero' => 2, 'marzo' => 3, 'abril' => 4,
                                        'mayo' => 5, 'junio' => 6, 'julio' => 7, 'agosto' => 8,
                                        'septiembre' => 9, 'octubre' => 10, 'noviembre' => 11, 'diciembre' => 12
                                    ];

                                    foreach($pagos as $pago) {
                                        foreach($pago->detalles as $detalle) {
                                            $detalle->recibo_padre = $pago->numero_recibo;
                                            $detalle->fecha_padre = $pago->fecha_pago;

                                            $nombreMes = strtolower(trim($detalle->cargo->mes_arancel ?? ''));
                                            $detalle->mes_num = $ordenMeses[$nombreMes] ?? 0;

                                            $detalle->peso_concepto = str_contains(strtoupper($detalle->concepto->nombre ?? ''), 'MATR') ? 0 : 1;
                                            $detalle->anio_num = $detalle->cargo->ciclo->anio ?? 9999;

                                            $kardex->push($detalle);
                                        }
                                    }

                                    $kardex = $kardex->sortBy([
                                        ['anio_num', 'asc'],
                                        ['mes_num', 'asc'],
                                        ['peso_concepto', 'asc']
                                    ]);
                                @endphp

                                @forelse($kardex as $detalle)
                                    <tr>
                                        <td class="ps-4 fw-bold text-dark" style="font-size: 0.9rem;">
                                            {{ $detalle->recibo_padre ?? 'S/N' }}
                                        </td>
                                        <td>
                                            <span class="badge border" style="background-color: #f8f9fa; color: #121212;">
                                                {{ $detalle->cargo->ciclo->nombre_ciclo ?? 'N/A' }} - {{ $detalle->cargo->ciclo->anio ?? '' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="text-capitalize text-dark" style="font-size: 0.95rem;">
                                                {{ $detalle->cargo->mes_arancel ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td class="text-dark">
                                            <span class="fw-bold">{{ \Illuminate\Support\Str::before($detalle->concepto->nombre ?? 'Arancel', ' DE') }}</span>
                                            @if(isset($detalle->recargo_aplicado) && $detalle->recargo_aplicado > 0)
                                                <br><small class="text-danger fw-bold">+ ${{ number_format($detalle->recargo_aplicado, 2) }} Mora</small>
                                            @endif
                                        </td>
                                        <td class="text-muted">{{ \Carbon\Carbon::parse($detalle->fecha_padre)->format('d/m/Y') }}</td>
                                        <td class="text-end pe-4 fw-bold text-nexum-blue">
                                            ${{ number_format($detalle->subtotal, 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-5">
                                            <i class="bi bi-inbox fs-2 mb-3 d-block text-black-50"></i>
                                            No hay historial de recibos registrados.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- EL MODAL Y EL JAVASCRIPT SE OCULTAN PARA BECA COMPLETA -->
    @if(!$esBecaCompleta)

        <!-- MODAL DE CONFIRMACIÓN DE PAGO -->
        <div class="modal fade" id="modalConfirmarPago" tabindex="-1"
             @if(isset($bloquearPantalla) && $bloquearPantalla)
                data-bs-backdrop="static" data-bs-keyboard="false"
             @endif
             aria-labelledby="modalConfirmarPagoLabel" aria-hidden="true">

            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg rounded-4">
                    <div class="modal-header text-white" style="background-color: #dc3545; border-top-left-radius: 1rem; border-top-right-radius: 1rem;">
                        <h5 class="modal-title fw-bold" id="modalConfirmarPagoLabel">
                            <i class="bi bi-shield-lock me-2"></i>Pago Seguro
                        </h5>

                        @if(isset($bloquearPantalla) && $bloquearPantalla)
                            <form action="{{ route('logout') }}" method="POST" class="m-0">
                                @csrf

                            </form>
                        @else
                            <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                        @endif
                    </div>

                    <div class="modal-body p-4 bg-light">
                        <div class="text-center mb-4">
                            <p class="text-muted mb-1">Monto a pagar (<span id="mockupCantidadMeses">0</span> aranceles)</p>
                            <h1 class="display-5 fw-bold text-dark" id="mockupTotalPagar">$0.00</h1>
                        </div>
                        <div class="card border-0 shadow-sm rounded-3">
                            <div class="card-body bg-white">
                                <h6 class="fw-bold mb-3 text-secondary border-bottom pb-2">Detalles de la Tarjeta</h6>
                                <form id="formDatosTarjeta" onsubmit="event.preventDefault(); procesarPagoVisual();">
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold text-muted mb-1">Nombre en la tarjeta</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-person text-muted"></i></span>
                                            <input type="text" class="form-control border-start-0 ps-0 shadow-none" placeholder="EJ. JUAN PEREZ" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold text-muted mb-1">Número de tarjeta</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-credit-card text-muted"></i></span>
                                            <input type="text" class="form-control border-start-0 ps-0 shadow-none" placeholder="0000 0000 0000 0000" maxlength="19" pattern="\d{4}\s?\d{4}\s?\d{4}\s?\d{4}" title="Ingrese 16 dígitos" required>
                                        </div>
                                    </div>
                                    <div class="row">
                                    <div class="col-6 mb-3">
                                        <label class="form-label small fw-bold text-muted mb-1">Vencimiento</label>
                                        <input type="text" id="vencimiento_tarjeta" class="form-control shadow-none text-center" placeholder="MM/AA" maxlength="5" pattern="(0[1-9]|1[0-2])\/[0-9]{2}" title="Formato MM/AA" required>
                                    </div>
                                        <div class="col-6 mb-3">
                                            <label class="form-label small fw-bold text-muted mb-1">CVV <i class="bi bi-info-circle text-muted" title="3 dígitos al reverso"></i></label>
                                            <input type="password" class="form-control shadow-none text-center" placeholder="•••" maxlength="4" pattern="\d{3,4}" title="3 o 4 dígitos de seguridad" required>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn fw-bold text-white w-100 py-2 mt-2" style="background-color: #dc3545;" id="btnConfirmarTransaccion">
                                        <i class="bi bi-lock-fill me-2"></i>Pagar Ahora
                                    </button>
                                </form>
                                <div class="text-center mt-3">
                                    <small class="text-muted"><i class="bi bi-shield-check text-success"></i> Tus datos están encriptados y seguros</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

                <!-- SCRIPT DIRECTO CON LÓGICA DE MODAL Y TARJETA -->
        <script>
            let modalPagoObj = null;

            document.addEventListener('DOMContentLoaded', function () {
                const checkboxes = Array.from(document.querySelectorAll('.item-deuda'));
                const btnPagar = document.getElementById('btnPagarAranceles');
                const contador = document.getElementById('contadorSeleccionados');
                const formPagoBackend = document.getElementById('formPagoEnLinea');

                const modalElement = document.getElementById('modalConfirmarPago');
                if(modalElement) {
                    modalPagoObj = new bootstrap.Modal(modalElement);
                }

                function inicializarCheckboxes() {
                    checkboxes.forEach((cb, index) => {
                        if (index === 0) {
                            cb.disabled = false;
                        } else {
                            cb.disabled = true;
                            cb.checked = false;
                        }
                    });
                    actualizarInterfaz();
                }

                function actualizarLogicaSecuencial(checkboxCambiado) {
                    const indexCambiado = checkboxes.indexOf(checkboxCambiado);
                    const estaMarcado = checkboxCambiado.checked;

                    if (estaMarcado) {
                        if (indexCambiado + 1 < checkboxes.length) {
                            checkboxes[indexCambiado + 1].disabled = false;
                        }
                    } else {
                        for (let i = indexCambiado + 1; i < checkboxes.length; i++) {
                            checkboxes[i].checked = false;
                            checkboxes[i].disabled = true;
                        }
                    }
                    actualizarInterfaz();
                }

                function actualizarInterfaz() {
                    let seleccionados = document.querySelectorAll('.item-deuda:checked');
                    let cantidad = seleccionados.length;

                    if (contador) contador.innerText = cantidad;

                    if (btnPagar) {
                        if (cantidad > 0) {
                            btnPagar.disabled = false;
                            btnPagar.style.opacity = '1';
                        } else {
                            btnPagar.disabled = true;
                            btnPagar.style.opacity = '0.6';
                        }
                    }

                    if (formPagoBackend) {
                        formPagoBackend.querySelectorAll('.input-cargo-oculto').forEach(e => e.remove());
                        seleccionados.forEach(cb => {
                            let input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = 'cargos_ids[]';
                            input.value = cb.getAttribute('data-id');
                            input.className = 'input-cargo-oculto';
                            formPagoBackend.appendChild(input);
                        });
                    }
                }

                checkboxes.forEach(cb => {
                    cb.addEventListener('change', function() {
                        actualizarLogicaSecuencial(this);
                    });
                });

                if (btnPagar) {
                    btnPagar.addEventListener('click', function() {
                        let seleccionados = document.querySelectorAll('.item-deuda:checked');

                        if(seleccionados.length > 0) {
                            let sumaTotal = 0;
                            seleccionados.forEach(cb => {
                                sumaTotal += parseFloat(cb.value);
                            });

                            document.getElementById('mockupTotalPagar').innerText = '$' + sumaTotal.toFixed(2);
                            document.getElementById('mockupCantidadMeses').innerText = seleccionados.length;

                            if(modalPagoObj) modalPagoObj.show();
                        }
                    });
                }

                // Bloqueo de Letras en Tarjeta
                const inputTarjeta = document.querySelector('input[placeholder="0000 0000 0000 0000"]');
                if(inputTarjeta) {
                    inputTarjeta.addEventListener('input', function (e) {
                        let target = e.target;
                        let val = target.value.replace(/\D/g, '').substring(0,16);
                        val = val != '' ? val.match(/.{1,4}/g).join(' ') : '';
                        target.value = val;
                    });
                }

                // Bloqueo de Letras en CVV
                const inputCVV = document.querySelector('input[placeholder="•••"]');
                if(inputCVV) {
                    inputCVV.addEventListener('input', function (e) {
                        e.target.value = e.target.value.replace(/\D/g, '').substring(0,4);
                    });
                }

                // Auto-formato de Fecha
                const inputVencimiento = document.getElementById('vencimiento_tarjeta');
                if(inputVencimiento) {
                    inputVencimiento.addEventListener('input', function (e) {
                        let val = e.target.value.replace(/\D/g, '').substring(0,4);
                        if (val.length > 2) {
                            val = val.substring(0,2) + '/' + val.substring(2,4);
                        }
                        e.target.value = val;
                    });
                }

                inicializarCheckboxes();

                const urlParams = new URLSearchParams(window.location.search);
                const esPagoObligatorio = {{ (isset($bloquearPantalla) && $bloquearPantalla) ? 'true' : 'false' }};
                const accionServidor = "{{ $tipoAutopayServidor ?? '' }}";
                const tipoAutopay = accionServidor || urlParams.get('autopay');

                if (esPagoObligatorio || tipoAutopay) {
                    setTimeout(() => {
                        if (tipoAutopay === 'all') {
                            checkboxes.forEach(cb => {
                                if (!cb.disabled && !cb.checked) cb.click();
                            });
                        } else {
                            if (checkboxes.length > 0 && !checkboxes[0].disabled && !checkboxes[0].checked) checkboxes[0].click();
                            if (checkboxes.length > 1 && !checkboxes[1].disabled && !checkboxes[1].checked) checkboxes[1].click();
                        }
                        setTimeout(() => {
                            if (btnPagar && !btnPagar.disabled) {
                                btnPagar.click();
                                window.history.replaceState({}, document.title, window.location.pathname);
                            }
                        }, 300);
                    }, 400);
                }
            });

            function procesarPagoVisual() {
                const inputVencimiento = document.getElementById('vencimiento_tarjeta');
                const partesFecha = inputVencimiento.value.split('/');

                if(partesFecha.length === 2) {
                    const mesIngresado = parseInt(partesFecha[0], 10);
                    const anioIngresado = parseInt("20" + partesFecha[1], 10);

                    const fechaActual = new Date();
                    const mesActual = fechaActual.getMonth() + 1;
                    const anioActual = fechaActual.getFullYear();

                    if (mesIngresado < 1 || mesIngresado > 12) {
                        if(window.NexumToast) {
                            window.NexumToast.error('El mes ingresado no es válido (debe ser entre 01 y 12).', 'Fecha Inválida');
                        } else {
                            alert('El mes ingresado no es válido.');
                        }
                        inputVencimiento.focus();
                        return;
                    }

                    if (anioIngresado < anioActual || (anioIngresado === anioActual && mesIngresado < mesActual)) {
                        if(window.NexumToast) {
                            window.NexumToast.error('La tarjeta ingresada está vencida. Por favor, utiliza un método de pago vigente.', 'Tarjeta Expirada');
                        } else {
                            alert('La tarjeta ingresada está vencida.');
                        }
                        inputVencimiento.focus();
                        return;
                    }
                }

                const btnConfirmar = document.getElementById('btnConfirmarTransaccion');
                const formPagoBackend = document.getElementById('formPagoEnLinea');

                btnConfirmar.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Procesando...';
                btnConfirmar.disabled = true;

                if(formPagoBackend) {
                    formPagoBackend.submit();
                }
            }
        </script>
    @endif

</div>
@endsection
