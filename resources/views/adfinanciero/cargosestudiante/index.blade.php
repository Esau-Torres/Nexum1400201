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
            <h2 class="fw-bold text-dark mb-1"><i class="bi bi-wallet2 me-2" style="color: #ff7575;"></i> Mi estado de cuenta</h2>
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
            <form id="formPagoEnLinea" action="#" method="POST" style="display: none;">
                @csrf
                <!-- Los inputs ocultos se generarán aquí automáticamente con JS -->
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
                                                <input class="form-check-input shadow-none" type="checkbox" id="selectAll">
                                            </th>
                                            <th>Ciclo</th>
                                            <th>Descripción</th>
                                            <th>Vencimiento</th>
                                            <th class="text-end pe-4">Monto</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tablaDeudas">
                                        @forelse($cargos->where('estado_cargo', 'PENDIENTE') as $cargo)
                                        <tr>
                                            <td class="ps-4">
                                                <input class="form-check-input item-deuda shadow-none" type="checkbox" value="{{ $cargo->monto_original }}" data-id="{{ $cargo->cargo_id ?? $cargo->id }}">
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column align-items-start">
                                                    <span class="badge bg-light text-dark border mb-1">{{ $cargo->ciclo->nombre_ciclo ?? 'N/A' }}</span>
                                                    <span class="text-capitalize text-dark" style="font-size: 0.95rem;">{{ \Carbon\Carbon::parse($cargo->fecha_emision)->locale('es')->translatedFormat('F') }}</span>
                                                </div>
                                            </td>
                                            <td class="fw-bold text-dark">{{ $cargo->concepto->nombre ?? 'N/A' }}</td>
                                            <td class="text-dark"><small>{{ \Carbon\Carbon::parse($cargo->fecha_vencimiento)->format('d/m/Y') }}</small></td>
                                            <td class="text-end pe-4 fw-bold text-dark">${{ number_format($cargo->monto_original, 2) }}</td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">No hay aranceles pendientes de pago.</td>
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

        <!-- PESTAÑA 2: HISTORIAL DE PAGOS -->
        <div class="tab-pane fade" id="pagos" role="tabpanel" tabindex="0">
            <div class="card border-0 shadow-sm rounded-4 bg-white">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">N° Recibo</th>
                                    <th>Fecha de pago</th>
                                    <th>Ciclo</th>
                                    <th>Descripción</th>
                                    <th class="text-end pe-4">Total pagado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pagos as $pago)
                                <tr>
                                    <td class="ps-4 fw-bold text-dark">{{ $pago->numero_recibo ?? 'S/N' }}</td>
                                    <td class="text-muted">{{ \Carbon\Carbon::parse($pago->fecha_pago)->format('d/m/Y') }}</td>
                                    <td>
                                        <div class="d-flex flex-column align-items-start">
                                            <span class="badge border mb-1" style="background-color: #f8f9fa; color: #121212;">
                                                <!-- Extraemos el ciclo directamente del primer concepto pagado para evitar errores -->
                                                {{ $pago->ciclo->nombre_ciclo ?? $pago->detalles->first()?->cargo?->ciclo?->nombre_ciclo ?? 'CICLO 02-2026' }}
                                            </span>
                                            <span class="text-capitalize text-dark" style="font-size: 0.95rem;">
                                                {{ \Carbon\Carbon::parse($pago->fecha_pago)->locale('es')->translatedFormat('F') }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="text-dark">
                                        @foreach($pago->detalles as $detalle)
                                            <div class="mb-1"><small>• {{ $detalle->concepto->nombre ?? 'Arancel' }}</small></div>
                                        @endforeach
                                    </td>
                                    <td class="text-end pe-4 fw-bold text-nexum-blue">${{ number_format($pago->total, 2) }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">No hay historial de recibos registrados.</td>
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

<!-- SCRIPT DIRECTO (Para garantizar ejecución) -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.item-deuda');
        const btnPagar = document.getElementById('btnPagarAranceles');
        const contador = document.getElementById('contadorSeleccionados');
        const formPago = document.getElementById('formPagoEnLinea');

        function actualizarInterfaz() {
            let seleccionados = document.querySelectorAll('.item-deuda:checked');
            let cantidad = seleccionados.length;

            // Actualizar número visual
            if (contador) contador.innerText = cantidad;

            // Apagar o encender botón
            if (btnPagar) {
                if (cantidad > 0) {
                    btnPagar.disabled = false;
                    btnPagar.style.opacity = '1';
                } else {
                    btnPagar.disabled = true;
                    btnPagar.style.opacity = '0.6';
                }
            }

            // Actualizar formulario oculto para backend
            if (formPago) {
                // Limpiar anteriores
                formPago.querySelectorAll('.input-cargo-oculto').forEach(e => e.remove());

                // Agregar los nuevos seleccionados
                seleccionados.forEach(cb => {
                    let input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'cargos_ids[]';
                    input.value = cb.getAttribute('data-id');
                    input.className = 'input-cargo-oculto';
                    formPago.appendChild(input);
                });
            }
        }

        // Listener para Checkbox individual
        checkboxes.forEach(cb => {
            cb.addEventListener('change', actualizarInterfaz);
        });

        // Listener para Seleccionar Todos
        if (selectAll) {
            selectAll.addEventListener('change', function () {
                checkboxes.forEach(cb => cb.checked = selectAll.checked);
                actualizarInterfaz();
            });
        }

        // Listener para Enviar Formulario (cuando se programe la ruta)
        if (btnPagar) {
            btnPagar.addEventListener('click', function() {
                if(formPago && formPago.querySelectorAll('.input-cargo-oculto').length > 0) {
                    // formPago.submit(); // Descomenta esta línea cuando tu ruta POST esté lista
                    console.log("Datos listos para enviarse.");
                }
            });
        }
    });
</script>
@endsection
