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
            <h2 class="fw-bold text-dark mb-1"><i class="bi bi-wallet2 me-2" style="color: #ff7575;"></i> MI ESTADO DE CUENTA</h2>
            <p class="text-muted mb-0">
                <span class="fw-bold text-dark">{{ Auth::user()->name ?? 'Estudiante' }}</span> | {{ Auth::user()->alumno->carrera->nombre ?? 'Carrera no definida' }} <br>
                <span class="badge text-white mt-2" style="background-color: #dc3545;">Ciclo Activo: 02-2026</span>
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
                <li class="nav-item" role="presentation">
                    <button class="nav-link active px-4 py-2 d-flex align-items-center" id="deuda-tab" data-bs-toggle="tab" data-bs-target="#deuda" type="button" role="tab" aria-controls="deuda" aria-selected="true" style="border-radius: 8px;">
                        <i class="bi bi-exclamation-circle me-2"></i> Deudas pendientes
                    </button>
                </li>
                <li class="nav-item ms-2" role="presentation">
                    <button class="nav-link text-dark px-4 py-2 d-flex align-items-center" id="pagos-tab" data-bs-toggle="tab" data-bs-target="#pagos" type="button" role="tab" aria-controls="pagos" aria-selected="false" style="border-radius: 8px;">
                        <i class="bi bi-clock-history me-2"></i> Deudas pagadas
                    </button>
                </li>
            </ul>

            <!-- Botón de Pago -->
            <button type="button" class="btn text-white fw-bold d-flex align-items-center px-4 py-2 me-1 mt-2 mt-md-0" style="background-color: #dc3545; opacity: 0.6; border-radius: 8px; transition: all 0.3s ease;" id="btnPagarAranceles" disabled>
                <i class="bi bi-receipt me-2"></i> Pagar
                <span class="badge bg-white text-danger rounded-circle ms-2 shadow-sm" id="contadorSeleccionados" style="padding: 0.4em 0.6em; font-size: 0.9em;">0</span>
            </button>
        </div>
    </div>


    <!-- Contenido de las Pestañas -->
    <div class="tab-content" id="estadoCuentaTabsContent">

        <!-- PESTAÑA 1: DEUDAS Y PAGO EN LÍNEA -->
        <div class="tab-pane fade show active" id="deuda" role="tabpanel" tabindex="0">
            <!-- Formulario oculto para enviar los pagos -->
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
                                {{-- LÓGICA DE ALERTA Y MORA (MENSUALIDAD Y MATRÍCULA) --}}
                                            @php
                                                $fechaVencimiento = \Carbon\Carbon::parse($cargo->fecha_vencimiento);
                                                $hoy = \Carbon\Carbon::now();

                                                // 1. ¿Está vencido el arancel?
                                                $estaVencido = $hoy->gt($fechaVencimiento);

                                                // 2. Identificar el tipo de arancel
                                                $nombreConcepto = strtoupper($cargo->concepto->nombre ?? 'CUOTA MENSUAL');
                                                $esMensualidad = (str_contains($nombreConcepto, 'CUOTA') || str_contains($nombreConcepto, 'MENSUAL'));
                                                // Buscamos variaciones de matrícula o inscripción
                                                $esMatricula = (str_contains($nombreConcepto, 'MATRÍCULA') || str_contains($nombreConcepto, 'MATRICULA') || str_contains($nombreConcepto, 'INSCRIPCI'));

                                                // 3. Aplicar Mora según el tipo
                                                $moraAplicada = 0;

                                                if ($estaVencido) {
                                                    if ($esMensualidad) {
                                                        // Multa por Mensualidad
                                                        $reglaBD = \Illuminate\Support\Facades\DB::table('reglas_pago')->where('tipo', 'ILIKE', '%Mensualidad%')->first();
                                                        if ($reglaBD && $reglaBD->id_concepto_recargo) {
                                                            $conceptoBD = \Illuminate\Support\Facades\DB::table('concepto_pago')->where('concepto_pago_id', $reglaBD->id_concepto_recargo)->first();
                                                            if ($conceptoBD) $moraAplicada = floatval($conceptoBD->monto_base);
                                                        }
                                                    } elseif ($esMatricula) {
                                                        // Multa por Inscripción Extraordinaria
                                                        $reglaBD = \Illuminate\Support\Facades\DB::table('reglas_pago')->where('tipo', 'ILIKE', '%Inscripci%')->first();
                                                        if ($reglaBD && $reglaBD->id_concepto_recargo) {
                                                            $conceptoBD = \Illuminate\Support\Facades\DB::table('concepto_pago')->where('concepto_pago_id', $reglaBD->id_concepto_recargo)->first();
                                                            if ($conceptoBD) $moraAplicada = floatval($conceptoBD->monto_base);
                                                        }
                                                    }
                                                }

                                                // 4. Calcular Total
                                                $totalArancel = floatval($cargo->monto_original) + floatval($moraAplicada);
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
                                                <span class="fw-bold text-dark">${{ number_format($cargo->monto_original, 2) }}</span>
                                                @if($moraAplicada > 0)
                                                    <br><small class="text-danger fw-bold">+ ${{ number_format($moraAplicada, 2) }} Mora</small>
                                                @endif
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">No hay aranceles pendientes de pago.</td>
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

        <!-- PESTAÑA 2: HISTORIAL DE PAGOS (Restaurada) -->
        <div class="tab-pane fade" id="pagos" role="tabpanel" tabindex="0">
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
                                    <th class="text-end pe-4">Total pagado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pagos as $pago)
                                <tr>
                                    <td class="ps-4 fw-bold text-dark">{{ $pago->numero_recibo ?? 'S/N' }}</td>
                                    <td>
                                        <span class="badge border" style="background-color: #f8f9fa; color: #121212;">
                                            {{ $pago->ciclo->nombre_ciclo ?? $pago->detalles->first()?->cargo?->ciclo?->nombre_ciclo ?? 'CICLO 02-2026' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-capitalize text-dark" style="font-size: 0.95rem;">
                                            @php
                                                $mesEscrito = $pago->detalles->first()?->cargo?->mes_arancel;
                                                if (empty($mesEscrito) && $pago->fecha_pago) {
                                                    $mesEscrito = \Carbon\Carbon::parse($pago->fecha_pago)->locale('es')->translatedFormat('F');
                                                }
                                            @endphp
                                            {{ $mesEscrito ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="text-dark">
                                        @foreach($pago->detalles as $detalle)
                                            <div class="mb-1 fw-bold">{{ \Illuminate\Support\Str::before($detalle->concepto->nombre ?? 'Arancel', ' DE') }}</div>
                                        @endforeach
                                    </td>
                                    <td class="text-muted">{{ \Carbon\Carbon::parse($pago->fecha_pago)->format('d/m/Y') }}</td>
                                    <td class="text-end pe-4 fw-bold text-nexum-blue">${{ number_format($pago->total, 2) }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">No hay historial de recibos registrados.</td>
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

<!-- ========================================== -->
<!-- MODAL DE CONFIRMACIÓN DE PAGO -->
<!-- ========================================== -->
<div class="modal fade" id="modalConfirmarPago" tabindex="-1" aria-labelledby="modalConfirmarPagoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header text-white" style="background-color: #dc3545; border-top-left-radius: 1rem; border-top-right-radius: 1rem;">
                <h5 class="modal-title fw-bold" id="modalConfirmarPagoLabel">
                    <i class="bi bi-shield-lock me-2"></i>Pago Seguro
                </h5>
                <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
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
    // Variables globales para el modal
    let modalPagoObj = null;

    document.addEventListener('DOMContentLoaded', function () {
        const checkboxes = Array.from(document.querySelectorAll('.item-deuda'));
        const btnPagar = document.getElementById('btnPagarAranceles');
        const contador = document.getElementById('contadorSeleccionados');
        const formPagoBackend = document.getElementById('formPagoEnLinea');

        // Instanciamos el modal
        const modalElement = document.getElementById('modalConfirmarPago');
        if(modalElement) {
            modalPagoObj = new bootstrap.Modal(modalElement);
        }

        // Configuración inicial: Deshabilitar todos excepto el primero
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
            // SOLUCIÓN: Usamos indexOf para obtener la posición REAL en la tabla HTML
            // ignorando los índices desordenados que pueda mandar Laravel.
            const indexCambiado = checkboxes.indexOf(checkboxCambiado);
            const estaMarcado = checkboxCambiado.checked;

            if (estaMarcado) {
                // Si marcó uno, habilitamos EXCLUSIVAMENTE el siguiente en la tabla (si existe)
                if (indexCambiado + 1 < checkboxes.length) {
                    checkboxes[indexCambiado + 1].disabled = false;
                }
            } else {
                // Si DESMARCÓ uno, desmarcamos y bloqueamos TODOS los que están debajo en la tabla
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

        // Evento que ABRIRÁ EL MODAL
        if (btnPagar) {
            btnPagar.addEventListener('click', function() {
                let seleccionados = document.querySelectorAll('.item-deuda:checked');

                if(seleccionados.length > 0) {
                    let sumaTotal = 0;

                    console.log("--- INICIANDO CÁLCULO DE PAGO ---");

                    seleccionados.forEach(cb => {
                        let valorCb = parseFloat(cb.value);
                        let moraCb = parseFloat(cb.getAttribute('data-mora') || 0);

                        console.log("Arancel ID:", cb.getAttribute('data-id'), " | Valor total:", valorCb, " | Incluye Mora de:", moraCb);

                        sumaTotal += valorCb;
                    });

                    console.log("SUMA TOTAL FINAL:", sumaTotal);

                    document.getElementById('mockupTotalPagar').innerText = '$' + sumaTotal.toFixed(2);
                    document.getElementById('mockupCantidadMeses').innerText = seleccionados.length;

                    if(modalPagoObj) modalPagoObj.show();
                }
            });
        }

        // 1. Bloqueo de Letras en Número de Tarjeta y Auto-formato
        const inputTarjeta = document.querySelector('input[placeholder="0000 0000 0000 0000"]');
        if(inputTarjeta) {
            inputTarjeta.addEventListener('input', function (e) {
                let target = e.target;
                let val = target.value.replace(/\D/g, '').substring(0,16); // \D elimina todo lo que no sea número
                val = val != '' ? val.match(/.{1,4}/g).join(' ') : '';
                target.value = val;
            });
        }

        // 2. Bloqueo de Letras en CVV (SOLUCIÓN A TU PREGUNTA)
        const inputCVV = document.querySelector('input[placeholder="•••"]');
        if(inputCVV) {
            inputCVV.addEventListener('input', function (e) {
                let target = e.target;
                // Borra cualquier letra o símbolo al instante, solo deja números
                target.value = target.value.replace(/\D/g, '').substring(0,4);
            });
        }

        // 3. Auto-formato de Fecha (agrega la barra automáticamente)
        const inputVencimiento = document.querySelector('input[placeholder="MM/AA"]');
        if(inputVencimiento) {
            inputVencimiento.addEventListener('input', function (e) {
                let target = e.target;
                let val = target.value.replace(/\D/g, '').substring(0,4);
                if (val.length > 2) {
                    val = val.substring(0,2) + '/' + val.substring(2,4);
                }
                target.value = val;
            });
        }

        inicializarCheckboxes();
    });

    // Función que se llama cuando el formulario de la tarjeta se envía
    function procesarPagoVisual() {
        const inputVencimiento = document.querySelector('input[placeholder="MM/AA"]');
        const partesFecha = inputVencimiento.value.split('/');

        // SOLUCIÓN A TU PREGUNTA: Validación de Fecha vs Fecha Actual
        if(partesFecha.length === 2) {
            const mesIngresado = parseInt(partesFecha[0], 10);
            // Convertimos el "26" a "2026"
            const anioIngresado = parseInt("20" + partesFecha[1], 10);

            // Obtenemos la fecha real de hoy
            const fechaActual = new Date();
            const mesActual = fechaActual.getMonth() + 1; // Se suma 1 porque enero es 0
            const anioActual = fechaActual.getFullYear();

            // Evaluamos si el año es menor al actual, o si es el mismo año pero el mes ya pasó
            if (anioIngresado < anioActual || (anioIngresado === anioActual && mesIngresado < mesActual)) {

                // Disparamos la alerta nativa roja del navegador en ese input específico
                inputVencimiento.setCustomValidity('La tarjeta ya expiró. Ingresa una tarjeta vigente.');
                inputVencimiento.reportValidity();

                // Borramos la alerta en cuanto el usuario intente corregirlo
                inputVencimiento.addEventListener('input', function() {
                    inputVencimiento.setCustomValidity('');
                }, {once: true});

                return; // 🛑 Detenemos el proceso de pago aquí
            }
        }

        // Si la tarjeta no está vencida, procedemos normalmente:
        const btnConfirmar = document.getElementById('btnConfirmarTransaccion');
        const formPagoBackend = document.getElementById('formPagoEnLinea');

        btnConfirmar.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Procesando...';
        btnConfirmar.disabled = true;

        if(formPagoBackend) {
            formPagoBackend.submit();
        }
    }
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const inputVencimiento = document.getElementById('vencimiento_tarjeta');

    if (inputVencimiento) {
        // 1. Auto-completar la pleca (/) mientras el usuario escribe
        inputVencimiento.addEventListener('input', function (e) {
            let valor = e.target.value.replace(/\D/g, '');
            if (valor.length > 2) {
                valor = valor.substring(0, 2) + '/' + valor.substring(2, 4);
            }
            e.target.value = valor;
        });

        // 2. Interceptar el formulario al darle "Pagar Ahora"
        const formulario = inputVencimiento.closest('form');

        if (formulario) {
            formulario.addEventListener('submit', function(event) {
                let fecha = inputVencimiento.value;

                if (fecha.length === 5) {
                    let partes = fecha.split('/');
                    let mesExp = parseInt(partes[0], 10);
                    let anioExp = parseInt("20" + partes[1], 10);

                    let hoy = new Date();
                    let mesActual = hoy.getMonth() + 1;
                    let anioActual = hoy.getFullYear();

                    // Rechazar si el mes es inválido o si la tarjeta ya venció
                    if (mesExp < 1 || mesExp > 12) {
                        event.preventDefault();
                        window.NexumToast.error('El mes ingresado no es válido (debe ser entre 01 y 12).', 'Fecha Inválida');
                        inputVencimiento.focus();
                    } else if (anioExp < anioActual || (anioExp === anioActual && mesExp < mesActual)) {
                        event.preventDefault(); // Detiene el pago hacia el controlador

                        // Llama a tu Toast personalizado en lugar del alert feo
                        window.NexumToast.error('La tarjeta ingresada está vencida. Por favor, utiliza un método de pago vigente.', 'Tarjeta Expirada');

                        inputVencimiento.focus();
                    }
                }
            });
        }
    }
});
</script>
@endsection
