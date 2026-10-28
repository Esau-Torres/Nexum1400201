@extends('layouts.app')

@section('content')
<style>
    /* Estilos personalizados para el Punto de Venta (POS) */
    .brand-text { color: #dc3545 !important; }
    .brand-bg { background-color: #dc3545 !important; }
    .brand-btn { background-color: #dc3545 !important; border: none; transition: all 0.3s ease; }
    .brand-btn:hover { background-color: #dc3545 !important; transform: translateY(-2px); box-shadow: 0 8px 15px rgba(255, 117, 117, 0.3); }
    .brand-btn:disabled { background-color: #6c757d !important; transform: none; box-shadow: none; }

    .pos-card { border-radius: 1.25rem; box-shadow: 0 10px 30px rgba(0,0,0,0.05); border: 1px solid rgba(0,0,0,0.05); }

    /* Iluminar inputs con el color de la marca */
    .form-control:focus, .form-select:focus {
        border-color: #dc3545 !important;
        box-shadow: 0 0 0 0.25rem rgba(255, 117, 117, 0.25) !important;
    }

    /* Estilo de ticket para el resumen */
    .ticket-container { background: #fdfdfd; border-radius: 1.25rem; border-top: 6px solid #dc3545; box-shadow: 0 10px 30px rgba(0,0,0,0.08); }
    .ticket-item { border-bottom: 1px dashed #dee2e6; }
    .ticket-item:last-child { border-bottom: none; }

    /* Inputs gigantes para mejor lectura del cajero */
    .input-pos { font-size: 1.5rem !important; font-weight: 700 !important; height: 3.5rem; text-align: right; }
    .input-carnet { font-size: 1.25rem !important; font-weight: 700 !important; text-align: center; letter-spacing: 2px; }
</style>

<div class="container-fluid py-4 px-lg-5">

    <!-- Encabezado de la página -->
    <div class="row mb-4 align-items-center">
        <div class="col-12">
            <div class="d-flex align-items-center">
                <div class="bg-white p-3 rounded-circle shadow-sm me-3 d-flex justify-content-center align-items-center" style="width: 60px; height: 60px;">
                    <i class="fa-solid fa-file-invoice-dollar fs-3 brand-text"></i>
                </div>
                <div>
                    <h2 class="fw-bold text-dark mb-1">Emisión de Recibo</h2>
                    <p class="text-muted mb-0">Verifica los datos y procesa el pago para generar el comprobante.</p>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ route('cajero.pagos.store') }}" method="POST" id="formPago">
        @csrf

        <!-- ================= DATOS OCULTOS (INTACTOS) ================= -->
        <input type="hidden" name="total_liquidado" id="inputTotalOculto" value="{{ $totalLiquidar ?? 0 }}">
        <input type="hidden" name="canal" value="{{ $metodoPagoEscogido ?? 'efectivo' }}">

        @if(isset($arancelesACobrar))
            @foreach($arancelesACobrar as $arancel)
                @if(isset($arancel->id_cargo) && !empty($arancel->id_cargo))
                    <!-- Enviamos el ID en arreglo para deudas -->
                    <input type="hidden" name="cargos_ids[]" value="{{ $arancel->id_cargo }}">
                @else
                    <!-- Enviamos el ID en arreglo para aranceles libres -->
                    <input type="hidden" name="aranceles_ids[]" value="{{ $arancel->concepto_pago_id }}">
                @endif
            @endforeach
        @endif
        <!-- ============================================================= -->

        <!-- BANNER DINÁMICO (Oculto por defecto con d-none) -->
        <div id="bannerEstudianteDirecto" class="card border-0 shadow-sm mb-4 d-none" style="background-color: #f4f7fb; border-radius: 12px; border-left: 6px solid #dc3545 !important;">
            <div class="card-body p-3 d-flex align-items-center">
                <!-- Icono de usuario -->
                <div class="bg-white rounded-circle d-flex align-items-center justify-content-center shadow-sm me-3 flex-shrink-0" style="width: 55px; height: 55px;">
                    <i class="fa-solid fa-user-check fs-3" style="color: #dc3545;"></i>
                </div>

                <div>
                    <!-- Título y Nombre -->
                    <h6 class="text-muted mb-1" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">
                        Verificación de Identidad
                    </h6>
                    <!-- Aquí el JS inyecta el nombre -->
                    <h5 id="nombreAlumnoDinamico" class="fw-bold text-dark mb-2" style="font-size: 1.15rem;"></h5>

                    <!-- Fila de Etiquetas (Carnet, Carrera, Beneficio) -->
                    <div class="d-flex flex-wrap gap-2 mt-1">

                        <!-- Etiqueta Carnet -->
                        <span class="badge px-3 py-2 fw-normal text-white" style="background-color: #6c757d; font-size: 0.85rem;">
                            <i class="fa-regular fa-id-card me-1"></i> CARNET:
                            <!-- Aquí el JS inyecta el carnet -->
                            <strong id="carnetAlumnoDinamico"></strong>
                        </span>

                        <!-- Etiqueta Carrera -->
                        <span class="badge bg-light text-dark border px-3 py-2 fw-normal" style="font-size: 0.85rem;">
                            <i class="fa-solid fa-graduation-cap me-1 text-muted"></i>
                            <!-- Aquí el JS inyecta la carrera -->
                            <span id="carreraAlumnoDinamico"></span>
                        </span>

                        <!-- Etiqueta Beneficio -->
                        <span class="badge bg-light text-dark border px-3 py-2 fw-normal" style="font-size: 0.85rem;">
                            <i class="fa-solid fa-star me-1" style="color: #dc3545;"></i>
                            <!-- Aquí el JS inyecta el beneficio -->
                            <span id="beneficioAlumnoDinamico"></span>
                        </span>

                    </div>
                </div>
            </div>
        </div>

        <!-- NUEVO: TARJETA DE CONFIRMACIÓN DE IDENTIDAD ANTES DE COBRAR -->
        @if(isset($alumno))
        <div class="card border-0 shadow-sm mb-4" style="background-color: #f4f7fb; border-radius: 12px; border-left: 6px solid #dc3545 !important;">
            <div class="card-body p-3 d-flex align-items-center">
                <!-- Icono de usuario -->
                <div class="bg-white rounded-circle d-flex align-items-center justify-content-center shadow-sm me-3 flex-shrink-0" style="width: 55px; height: 55px;">
                    <i class="fa-solid fa-user-check fs-3" style="color: #dc3545;"></i>
                </div>

                <div>
                    <!-- Título y Nombre -->
                    <h6 class="text-muted mb-1" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">
                        Verificación de Identidad
                    </h6>
                    <h5 class="fw-bold text-dark mb-2" style="font-size: 1.15rem;">
                        {{ $alumno->nombres ?? 'Estudiante' }} {{ $alumno->apellidos ?? '' }}
                    </h5>

                    <!-- Fila de Etiquetas (Carnet, Carrera, Beneficio) -->
                    <div class="d-flex flex-wrap gap-2 mt-1">
                        <!-- Etiqueta Carnet (Mantiene el tono gris pizarra original de tu diseño) -->
                        <span class="badge bg-light text-dark border px-3 py-2 fw-normal" style="font-size: 0.85rem;">
                            <i class="fa-regular fa-id-card me-1"></i>
                            CARNET: <strong>{{ strtoupper($alumno->carnet ?? request('id_alumno')) }}</strong>
                        </span>

                        <!-- Etiqueta Carrera (Estilo limpio Nexum) -->
                        <span class="badge bg-light text-dark border px-3 py-2 fw-normal" style="font-size: 0.85rem;">
                            <i class="fa-solid fa-graduation-cap me-1 text-muted"></i>
                            {{ $alumno->carrera ?? 'Sin carrera' }}
                        </span>

                        <!-- Etiqueta Beneficio (Estilo limpio Nexum con icono en rojo institucional) -->
                        <span class="badge bg-light text-dark border px-3 py-2 fw-normal" style="font-size: 0.85rem;">
                            <i class="fa-solid fa-star me-1" style="color: #dc3545;"></i>
                            {{ $alumno->beneficio ?? 'Sin beneficio' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
        @endif
        <!-- FIN NUEVO -->

        <div class="row g-4">

            <!-- COLUMNA IZQUIERDA: Panel de Operaciones -->
            <div class="col-xl-8 col-lg-7">
                <div class="card pos-card bg-white h-100">
                    <div class="card-body p-4 p-md-5">

                        <h4 class="fw-bold text-dark mb-4 pb-3 border-bottom d-flex align-items-center">
                            @if(isset($metodoPagoEscogido) && $metodoPagoEscogido == 'tarjeta')
                                <i class="fa-regular fa-credit-card me-3 text-primary fs-3"></i> Terminal POS - Tarjeta
                            @else
                                <i class="fa-solid fa-money-bill-wave me-3 text-success fs-3"></i> Caja de Cobro - Efectivo
                            @endif
                        </h4>


                        <!-- PEDIR CARNET (Solo para Venta Directa, NO para Deudas) -->
                        <!-- PEDIR CARNET Y VALIDAR (Solo para Venta Directa, NO para Deudas) -->
                        @if(isset($esPagoDeuda) && $esPagoDeuda === false)
                            <div class="bg-light p-4 rounded-4 mb-4 border border-light shadow-sm">
                                <label class="form-label fw-bold text-dark mb-3 fs-6">
                                    <i class="fa-solid fa-id-badge brand-text me-2"></i> Identificación del Estudiante
                                </label>

                                <div class="input-group input-group-lg mb-2 shadow-sm rounded-3">
                                    <input type="text" id="inputCarnetDirecto" name="codigo_estudiante" class="form-control input-carnet border-end-0 shadow-none bg-white" style="text-transform: uppercase;" placeholder="Ej. MV224100040" required autofocus>
                                    <button type="button" id="btnValidarCarnet" class="btn text-white fw-bold px-4 brand-btn">
                                        <i class="fa-solid fa-magnifying-glass me-2"></i> Validar
                                    </button>
                                </div>
                                <div class="text-center mt-2">
                                    <small class="text-muted"><i class="fa-solid fa-circle-info me-1"></i> Este cobro se registrará en el expediente de este carnet.</small>
                                </div>


                            </div>
                        @endif

                        <!-- SECCIÓN EFECTIVO -->
                        @if(!isset($metodoPagoEscogido) || $metodoPagoEscogido == 'efectivo')
                            <div class="row g-4 mt-2">
                                <div class="col-md-6">
                                    <div class="p-3 border rounded-4 bg-white shadow-sm position-relative">
                                        <span class="position-absolute top-0 start-50 translate-middle badge rounded-pill bg-dark px-3 py-2">RECIBIDO</span>
                                        <div class="input-group mt-3">
                                            <span class="input-group-text bg-transparent border-0 fs-3 text-muted">$</span>
                                            <input type="number" step="0.01" min="{{ $totalLiquidar ?? 0 }}" id="montoRecibido" name="monto_recibido" class="form-control border-0 shadow-none input-pos text-success" placeholder="0.00" required @if(!empty($idCargo)) autofocus @endif>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 border rounded-4 bg-light shadow-sm position-relative" style="border-color: #dc3545 !important;">
                                        <span class="position-absolute top-0 start-50 translate-middle badge rounded-pill brand-bg px-3 py-2">VUELTO</span>
                                        <div class="input-group mt-3">
                                            <span class="input-group-text bg-transparent border-0 fs-3 brand-text">$</span>
                                            <input type="text" id="cambioVuelto" class="form-control border-0 shadow-none input-pos bg-transparent brand-text" placeholder="0.00" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- SECCIÓN TARJETA -->
                        @if(isset($metodoPagoEscogido) && $metodoPagoEscogido == 'tarjeta')
                            <div class="row g-4 mt-2">

                                <!-- Número de Tarjeta Completo (Ocupa toda la fila) -->
                                <div class="col-md-12">
                                    <label class="form-label fw-bold text-secondary">Número de Tarjeta</label>
                                    <div class="input-group input-group-lg shadow-sm rounded-3 overflow-hidden">
                                        <span class="input-group-text bg-light text-muted border-0"><i class="fa-solid fa-credit-card"></i></span>
                                        <!-- Se agregó el script formateador de espacios en el oninput y maxlength="19" -->
                                        <input type="text" name="numero_tarjeta" autocomplete="off" value="{{ old('numero_tarjeta') }}" class="form-control border-0 bg-light fw-bold fs-5" placeholder="0000 0000 0000 0000" maxlength="19" required autofocus oninput="this.value = this.value.replace(/\D/g, '').substring(0,16).replace(/(\d{4})(?=\d)/g, '$1 ');">
                                    </div>
                                </div>

                                <!-- Vencimiento (Ocupa la mitad izquierda) -->
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-secondary">Vencimiento</label>
                                    <div class="input-group input-group-lg shadow-sm rounded-3 overflow-hidden {{ $errors->has('fecha_vencimiento') ? 'border border-danger' : 'border-0' }}">
                                        <span class="input-group-text bg-light text-muted border-0"><i class="fa-regular fa-calendar"></i></span>
                                        <input type="text" id="fechaVencimiento" name="fecha_vencimiento" value="{{ old('fecha_vencimiento') }}"
                                            class="form-control border-0 bg-light fw-bold fs-5 text-center"
                                            placeholder="MM/AA" maxlength="5" required
                                            oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/^(\d{2})(\d{1,2})/, '$1/$2').substring(0, 5)">
                                    </div>
                                    <!-- ALERTA ROJA SI ESTÁ VENCIDA -->
                                    @error('fecha_vencimiento')
                                        <small class="text-danger fw-bold mt-1 d-block"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</small>
                                    @enderror
                                </div>

                                <!-- Nuevo Campo CVV (Ocupa la mitad derecha) -->
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-secondary">Código de Seguridad (CVV)</label>
                                    <div class="input-group input-group-lg shadow-sm rounded-3 overflow-hidden {{ $errors->has('cvv') ? 'border border-danger' : 'border-0' }}">
                                        <span class="input-group-text bg-light text-muted border-0"><i class="fa-solid fa-lock"></i></span>
                                        <input type="password" name="cvv" autocomplete="off" class="form-control border-0 bg-light fw-bold fs-5 text-center" placeholder="***" maxlength="4" required oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                    </div>
                                    <!-- ALERTA ROJA SI EL CVV ESTÁ MAL -->
                                    @error('cvv')
                                        <small class="text-danger fw-bold mt-1 d-block"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</small>
                                    @enderror
                                </div>

                            </div>
                        @endif

                    </div>
                </div>
            </div>

            <!-- COLUMNA DERECHA: Resumen (Estilo Ticket) -->
            <div class="col-xl-4 col-lg-5">
                <div class="card ticket-container h-100">
                    <div class="card-body p-4 d-flex flex-column">

                        <div class="text-center mb-4 pb-3 border-bottom">
                            <h5 class="fw-bolder text-dark mb-1"><i class="fa-solid fa-receipt me-2 brand-text"></i> RESUMEN DE COBRO</h5>
                            <small class="text-muted text-uppercase" style="letter-spacing: 1px;">Universidad Modular Abierta</small>
                        </div>

                        <!-- Lista de Conceptos Dinámica -->
                        <div class="mb-4 flex-grow-1">
                            <span class="text-muted d-block mb-3 fw-bold" style="font-size: 0.85rem;">DETALLE DE ARANCELES:</span>

                            @if(isset($arancelesACobrar) && $arancelesACobrar->count() > 0)
                                <ul class="list-unstyled mb-0" id="listaTicket">
                                    @foreach($arancelesACobrar as $arancel)
                                        <li class="ticket-item py-2 d-flex justify-content-between align-items-center">

                                            <!-- LOS INPUTS OCULTOS -->
                                            @if(isset($arancel->id_cargo) && !empty($arancel->id_cargo))
                                                <input type="hidden" name="cargos_ids[]" value="{{ $arancel->id_cargo }}">
                                            @else
                                                <input type="hidden" name="aranceles_ids[]" value="{{ $arancel->concepto_pago_id }}">
                                            @endif

                                            <!-- Nombre del Concepto y Mora (Empuja todo lo demás a la derecha) -->
                                            <div class="me-auto pe-2">
                                                <span class="text-dark fw-bold d-block" style="font-size: 0.85rem; line-height: 1.2;">
                                                    @php
                                                        // Limpiamos "MATRICULA DE PREGRADO: ..." pero conservamos la fecha "(08/2026)"
                                                        $nombreLimpio = \Illuminate\Support\Str::before($arancel->nombre, ':');
                                                        if(str_contains($arancel->nombre, '(')) {
                                                            $nombreLimpio .= ' ' . strstr($arancel->nombre, '(');
                                                        }
                                                    @endphp
                                                    {{ strtoupper($nombreLimpio) }}
                                                </span>

                                                <!-- AQUÍ APARECE LA MORA SI APLICA -->
                                                @if(isset($arancel->tiene_mora) && $arancel->tiene_mora)
                                                    <span class="badge text-white mt-1" style="background-color: #dc3545; font-size: 0.70rem; font-weight: 600;">
                                                        <i class="fa-solid fa-triangle-exclamation"></i> +${{ number_format($arancel->monto_mora, 2) }} Mora
                                                    </span>
                                                @endif
                                            </div>

                                            <!-- Columna de Precios -->
                                            <div class="text-end pe-3 border-end">
                                                @if(isset($arancel->porcentaje_descuento) && $arancel->porcentaje_descuento > 0)
                                                    <span class="text-muted text-decoration-line-through d-block" style="font-size: 0.75rem;">
                                                        <!-- Tachamos el subtotal (base + mora) si hay un descuento -->
                                                        ${{ number_format($arancel->monto_base + ($arancel->monto_mora ?? 0), 2) }}
                                                    </span>
                                                    <span class="badge bg-danger mb-1" style="font-size: 0.65rem;">
                                                        -{{ number_format($arancel->porcentaje_descuento, 0) }}%
                                                    </span>
                                                @endif

                                                <!-- Monto Final Real (Ya trae la mora sumada desde el controlador) -->
                                                <span class="fw-bolder text-dark fs-6 d-block">
                                                    ${{ number_format($arancel->monto_final ?? $arancel->monto_base, 2) }}
                                                </span>
                                            </div>

                                            <!-- Botón Eliminar 'X' -->
                                            <div class="ps-3">
                                                <button type="button" class="btn btn-sm btn-link text-danger p-0 m-0 btn-eliminar-item" data-precio="{{ $arancel->monto_final ?? $arancel->monto_base }}">
                                                    <i class="fa-solid fa-xmark fs-5"></i>
                                                </button>
                                            </div>

                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <div class="py-2 d-flex justify-content-between align-items-center">
                                    <span class="text-dark fw-bold" style="font-size: 0.85rem;">{{ strtoupper($nombreConcepto ?? 'No especificado') }}</span>
                                </div>
                            @endif
                        </div>

                        <!-- Total gigante -->
                        <div class="bg-dark rounded-4 p-3 mb-4 text-center shadow">
                            <span class="text-white-50 fw-bold d-block mb-1" style="font-size: 0.9rem; letter-spacing: 1px;">TOTAL A PAGAR</span>

                            <!-- El $totalLiquidar que viene del controlador ya trae la mora sumada a la perfección -->
                            <span class="fw-black text-white d-block" style="font-size: 2.5rem; line-height: 1;" id="totalLiquidar" data-total="{{ $totalLiquidar ?? 0 }}">
                                ${{ number_format($totalLiquidar ?? 0, 2) }}
                            </span>
                        </div>

                        <button type="submit" id="btnProcesar" class="btn brand-btn btn-lg w-100 text-white fw-bold py-3 fs-5">
                            <i class="fa-solid fa-check-circle me-2"></i> Emitir Recibo
                        </button>

                    </div>
                </div>
            </div>
    </form>
</div>

<!-- Script de la Calculadora, Validaciones y Borrado de Items -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const inputTotalOculto = document.getElementById('inputTotalOculto');
        const divTotalGigante = document.getElementById('totalLiquidar');
        const btnProcesar = document.getElementById('btnProcesar');
        const inputRecibido = document.getElementById('montoRecibido');
        const inputCambio = document.getElementById('cambioVuelto');
        const listaTicket = document.getElementById('listaTicket');

        // Función que recalcula el vuelto y apaga/enciende el botón de emitir
        function evaluarCalculadora() {
            let totalActual = parseFloat(divTotalGigante.getAttribute('data-total'));

            // Si eliminaron todos los items y llegó a $0.00
            if (totalActual <= 0) {
                btnProcesar.disabled = true;
                btnProcesar.innerHTML = '<i class="fa-solid fa-ban me-2"></i> Cobro Inválido ($0.00)';
                if (inputRecibido) {
                    inputRecibido.disabled = true;
                    inputRecibido.value = '';
                }
                if (inputCambio) inputCambio.value = '0.00';
                return;
            }

            // Flujo normal (Validar que el efectivo alcance)
            if (inputRecibido) {
                inputRecibido.disabled = false;
                let recibido = parseFloat(inputRecibido.value);

                if (!isNaN(recibido) && recibido >= totalActual) {
                    let cambio = recibido - totalActual;
                    inputCambio.value = cambio.toFixed(2);
                    btnProcesar.disabled = false;
                    btnProcesar.innerHTML = '<i class="fa-solid fa-check-circle me-2"></i> Emitir Recibo';
                } else {
                    inputCambio.value = '0.00';
                    btnProcesar.disabled = true;
                    btnProcesar.innerHTML = '<i class="fa-solid fa-check-circle me-2"></i> Emitir Recibo';
                }
            } else {
                // Si es tarjeta y el total es mayor a 0, siempre se puede cobrar
                btnProcesar.disabled = false;
                btnProcesar.innerHTML = '<i class="fa-solid fa-check-circle me-2"></i> Emitir Recibo';
            }
        }

        // Evento 1: Escribir billetes en el input de recibido
        if (inputRecibido) {
            inputRecibido.addEventListener('input', evaluarCalculadora);
        }

        // Evento 2: Clic en la "X" para eliminar un arancel
        if (listaTicket) {
            listaTicket.addEventListener('click', function(e) {
                const btnEliminar = e.target.closest('.btn-eliminar-item');

                if (btnEliminar) {
                    // ¿Cuánto valía este arancel?
                    const precioDescontar = parseFloat(btnEliminar.getAttribute('data-precio'));

                    // ¿Cuánto era el total antes de borrar?
                    let totalActual = parseFloat(divTotalGigante.getAttribute('data-total'));

                    // Resta
                    let nuevoTotal = totalActual - precioDescontar;
                    if (nuevoTotal < 0.01) nuevoTotal = 0; // Prevenir errores de decimales negativos

                    // 1. Actualizamos los textos de los totales
                    divTotalGigante.setAttribute('data-total', nuevoTotal.toFixed(2));
                    divTotalGigante.innerHTML = '$' + nuevoTotal.toFixed(2);
                    inputTotalOculto.value = nuevoTotal.toFixed(2);

                    // 2. Destruimos visualmente el Arancel (y sus inputs ocultos)
                    btnEliminar.closest('li').remove();

                    // 3. Volvemos a validar la caja registradora
                    evaluarCalculadora();
                }
            });
        }

        // Ejecutamos por primera vez al entrar a la pantalla
        evaluarCalculadora();
    });
</script>

<!-- Script para dar formato automático MM/AA a la fecha -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const inputFecha = document.getElementById('fechaVencimiento');
        if(inputFecha) {
            inputFecha.addEventListener('input', function(e) {
                // Quitar cualquier cosa que no sea número
                let val = e.target.value.replace(/\D/g, '');
                // Agregar la pleca automáticamente después de los 2 primeros meses
                if (val.length > 2) {
                    val = val.substring(0, 2) + '/' + val.substring(2, 4);
                }
                e.target.value = val;
            });
        }
    });
</script>

<!-- Script AJAX para validar al estudiante sin recargar -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const btnValidar = document.getElementById('btnValidarCarnet');
        const inputCarnet = document.getElementById('inputCarnetDirecto');
        const banner = document.getElementById('bannerEstudianteDirecto');
        const nombreAlumno = document.getElementById('nombreAlumnoDinamico');
        const carnetAlumno = document.getElementById('carnetAlumnoDinamico');

        // Agregamos referencias a los nuevos elementos (asegúrate de que existan en tu HTML)
        const carreraAlumno = document.getElementById('carreraAlumnoDinamico');
        const beneficioAlumno = document.getElementById('beneficioAlumnoDinamico');

        // Solo ejecutar si el botón existe (es decir, si estamos en venta directa)
        if (btnValidar && inputCarnet) {
            btnValidar.addEventListener('click', function() {
                let carnet = inputCarnet.value.trim();

                if(!carnet) {
                    alert('Por favor, ingrese un carnet para validar.');
                    return;
                }

                // Efecto visual de carga
                btnValidar.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i> Buscando';
                btnValidar.disabled = true;

                // Petición al backend
                fetch("{{ url('/cajero/api/verificar-alumno') }}/" + carnet)
                    .then(response => response.json())
                    .then(data => {
                        if(data.success) {
                            // Mostrar banner con datos
                            banner.classList.remove('d-none');

                            // Extraemos los datos de la respuesta de la API
                            // Ajustado a 'nombres' según lo que devuelve tu función verificarAlumnoAPI actualizada
                            let nombreCompleto = data.alumno.nombres || 'Estudiante Registrado';

                            // Asignar los valores a la tarjeta (asegúrate de que los IDs correspondan a los <span> en tu HTML)
                            nombreAlumno.textContent = nombreCompleto;
                            carnetAlumno.textContent = data.alumno.carnet.toUpperCase();

                            // Asignar Carrera y Beneficio si los elementos existen en el HTML
                            if (carreraAlumno) carreraAlumno.textContent = data.alumno.carrera;
                            if (beneficioAlumno) beneficioAlumno.textContent = data.alumno.beneficio;

                            // Cambiar el botón a verde (éxito)
                            btnValidar.innerHTML = '<i class="fa-solid fa-check me-2"></i> Validado';
                            btnValidar.style.backgroundColor = '#198754';
                            btnValidar.style.borderColor = '#198754'; // Añadido para quitar borde rojo si lo hay
                            btnValidar.classList.remove('btn-danger'); // Si usa clase de bootstrap
                            btnValidar.classList.add('text-white');

                        } else {
                            // Ocultar banner si falla
                            banner.classList.add('d-none');
                            alert(data.message);
                            resetBoton();
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Hubo un error de conexión al buscar el carnet. Revise su conexión.');
                        resetBoton();
                    });
            });

            // Si el cajero cambia el input, se oculta el banner para obligarlo a re-validar
            inputCarnet.addEventListener('input', function() {
                if (!banner.classList.contains('d-none')) {
                    banner.classList.add('d-none');
                    resetBoton();
                }
            });

            function resetBoton() {
                btnValidar.innerHTML = '<i class="fa-solid fa-magnifying-glass me-2"></i> Validar';
                btnValidar.style.backgroundColor = ''; // Regresa al color original
                btnValidar.style.borderColor = '';
                btnValidar.classList.remove('text-white'); // Ajusta según tus clases CSS base
                btnValidar.disabled = false;
            }
        }
    });
</script>
@endsection
