@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0">Atención en ventanilla</h2>
            <p class="text-muted">Consulta y liquidación de deuda pendiente de alumnos.</p>
        </div>
    </div>

    <!-- Barra Superior: Búsqueda Rápida y Acciones de Cobro -->
    <div class="card shadow-sm border-0 mb-4 bg-white">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-end gap-3">

                <!-- IZQUIERDA: Buscador de Alumno -->
                <form method="GET" action="{{ route('cajero.deuda.index') }}" class="w-100" style="max-width: 550px;">
                    <label class="form-label text-dark fw-bold mb-2">Búsqueda rápida</label>
                    <div class="input-group input-group-lg shadow-sm">
                        <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-id-card text-muted"></i></span>
                        <input type="text" name="id_alumno" class="form-control border-start-0 text-lowercase @error('id_alumno') is-invalid @enderror" placeholder="Ingrese el carnet" value="{{ old('id_alumno', request('id_alumno')) }}" required autofocus>
                        <button type="submit" class="btn text-white px-4 fw-bold" style="background-color: #dc3545;">
                            <i class="fa-solid fa-magnifying-glass me-2"></i> Buscar deuda
                        </button>
                    </div>
                    @error('id_alumno')
                        <div class="text-danger mt-2 fw-bold" style="font-size: 0.9rem;">
                            <i class="fa-solid fa-circle-xmark me-1"></i> {{ $message }}
                        </div>
                    @enderror
                </form>

                <!-- DERECHA: Selector de Método y Botón Generar Recibo -->
                @if(request()->has('id_alumno') && count($cargos) > 0)
                <div class="d-flex align-items-center gap-2 pb-1">
                    <!-- El atributo form="formCobroDeuda" enlaza este select con el formulario de la tabla -->
                    <select id="metodoPagoSelect" form="formCobroDeuda" name="metodo_pago" class="form-select fw-bold shadow-sm" style="min-width: 170px; height: 48px; border: 1px solid #4a90e2; color: #0d6efd; cursor: pointer;">
                        <option value="efectivo">💵 Efectivo</option>
                        <option value="tarjeta">💳 Tarjeta (POS)</option>
                    </select>

                    <button type="submit" form="formCobroDeuda" id="btnGenerarRecibo" class="btn text-white fw-bold shadow-sm d-flex align-items-center gap-2 px-3" style="background-color: #dc3545; border: none; height: 48px; transition: all 0.3s;" disabled>
                        <i class="fa-solid fa-file-invoice-dollar fs-5"></i> Pagar
                        <span class="badge bg-white rounded-pill ms-1 fs-6" style="color: #dc3545;" id="contadorSeleccionados">0</span>
                    </button>
                </div>
                @endif

            </div>
        </div>
    </div>

    <!-- Resultados de Deuda Pendiente -->
    @if(request()->has('id_alumno'))

    <!-- FIN NUEVO -->

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold"><i class="fa-solid fa-user-graduate me-2 text-muted"></i> Estado de cuenta</h5>
            <span class="badge bg-warning bg-opacity-10 text-warning border border-warning px-3 py-2">Solo Cargos PENDIENTES</span>
        </div>
        <div class="card-body p-0">
            <!-- Formulario que envuelve la tabla para enviar las deudas seleccionadas -->
            <form id="formCobroDeuda" action="{{ route('cajero.pagos.create') }}" method="GET">
                <div class="table-responsive" style="min-height: 300px;">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">N° Cargo</th>
                                <th class="text-center">Ciclo</th>
                                <th>Periodo</th>
                                <th>Concepto a Pagar</th>
                                <th class="text-center">Vencimiento</th>
                                <th class="text-center">Monto</th>

                                <!-- Columna movida a la derecha -->
                                <th class="text-end pe-4">
                                    <label for="checkAllDeudas" class="me-2 fw-bold text-dark mb-0" style="cursor:pointer;">Seleccionar</label>
                                    <input class="form-check-input shadow-sm border-secondary" type="checkbox" id="checkAllDeudas" style="transform: scale(1.2); cursor:pointer;">
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($cargos as $cargo)
                            <tr class="border-bottom fila-deuda align-middle">
                                <!-- ID Cargo -->
                                <td class="ps-4 py-3 fw-bold text-muted text-nowrap">#{{ $cargo->cargo_id }}</td>

                                <!-- Ciclo (text-nowrap evita que se baje de línea) -->
                                <td class="py-3 text-dark text-nowrap">
                                    {{ $cargo->ciclo->nombre ?? 'Ciclo ' . $cargo->id_ciclo_lectivo }}
                                </td>

                                <!-- Periodo -->
                                <td class="py-3 text-dark text-nowrap">
                                    {{ str_pad($cargo->mes_arancel, 2, '0', STR_PAD_LEFT) }}/{{ $cargo->anio_arancel }}
                                </td>

                                <!-- Concepto -->
                                <td class="py-3 text-uppercase fw-medium" style="font-size: 0.85rem;">
                                    {{ \Illuminate\Support\Str::before($cargo->concepto->nombre ?? 'N/A', ':') }}
                                </td>

                                <!-- Vencimiento (text-nowrap mantiene la fecha y el icono unidos) -->
                                <td class="py-3 text-center text-nowrap">
                                    @php
                                        $vencido = \Carbon\Carbon::parse($cargo->fecha_vencimiento)->isPast();
                                    @endphp
                                    <span class="{{ $vencido ? 'text-danger fw-bold' : 'text-muted' }}">
                                        {{ \Carbon\Carbon::parse($cargo->fecha_vencimiento)->format('d/m/Y') }}
                                        @if($vencido) <i class="fa-solid fa-circle-exclamation ms-1" title="Vencido"></i> @endif
                                    </span>
                                </td>

                                <!-- Monto Rediseñado (Total en grande, desglose en pequeño) -->
                                <td class="py-3 pe-3 text-end">
                                    @php
                                        // Definimos el monto base a mostrar (con o sin beneficio)
                                        $montoBaseMostrar = $cargo->monto_neto_estudiante ?? $cargo->monto_original;
                                    @endphp

                                    @if(isset($cargo->tiene_mora) && $cargo->tiene_mora)
                                        <div class="d-flex flex-column align-items-end justify-content-center">
                                            <!-- Total a cobrar resaltado en rojo -->
                                            <span class="text-danger fw-bold fs-5">${{ number_format($cargo->total_con_mora, 2) }}</span>
                                            <!-- Desglose sutil abajo -->
                                            <span class="text-muted" style="font-size: 0.75rem;">
                                                <s class="me-1">${{ number_format($montoBaseMostrar, 2) }}</s>
                                                <span class="text-danger"><i class="fa-solid fa-arrow-trend-up ms-1"></i> +${{ number_format($cargo->monto_mora, 2) }} mora</span>
                                            </span>
                                        </div>
                                    @else
                                        <!-- Monto normal si no hay mora, reflejando el beneficio si existe -->
                                        <span class="text-dark fw-bold fs-5">${{ number_format($montoBaseMostrar, 2) }}</span>
                                    @endif
                                </td>

                                <!-- Checkbox -->
                                <td class="py-3 text-center">
                                    <input class="form-check-input check-deuda shadow-sm border-secondary" type="checkbox" name="cargos_ids[]" value="{{ $cargo->cargo_id }}" style="transform: scale(1.2); cursor:pointer;">

                                    <!-- Campos ocultos de apoyo requeridos por el script -->
                                    <input type="hidden" name="id_cargo" value="{{ $cargo->cargo_id }}" disabled class="hidden-single-data">
                                    <!-- CAMBIO VITAL: El monto oculto ahora usa el monto_neto_estudiante como base -->
                                    <input type="hidden" name="monto" value="{{ (isset($cargo->tiene_mora) && $cargo->tiene_mora) ? $cargo->total_con_mora : ($cargo->monto_neto_estudiante ?? $cargo->monto_original) }}" disabled class="hidden-single-data">
                                    <input type="hidden" name="concepto" value="{{ $cargo->concepto->nombre ?? 'N/A' }}" disabled class="hidden-single-data">
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">No se encontraron deudas pendientes.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </form>
        </div>
    </div>
    @else
    <!-- Pantalla Inicial del Cajero (Sin búsqueda activa) -->
    <div class="text-center py-5 mt-4">
        <div class="d-inline-flex align-items-center justify-content-center bg-light rounded-circle mb-3 shadow-sm" style="width: 100px; height: 100px;">
            <i class="fa-solid fa-cash-register fs-1 text-muted"></i>
        </div>
        <h4 class="fw-bold text-dark">Terminal de Caja</h4>
        <p class="text-muted">Ingrese el código del alumno en el buscador superior para consultar e iniciar el cobro.</p>
    </div>
    @endif
</div>


<!-- SCRIPT PARA CONTROLAR LOS CHECKBOXES Y EL BOTÓN (CON SELECCIÓN EN CASCADA) -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const checkboxes = document.querySelectorAll('.check-deuda');
        const checkAll = document.getElementById('checkAllDeudas');
        const btnGenerar = document.getElementById('btnGenerarRecibo');
        const contador = document.getElementById('contadorSeleccionados');

        // FUNCIÓN NUEVA: Bloquea los meses recientes si los antiguos no están pagados
        function validarSecuencia() {
            let habilitarSiguiente = true; // El primer cargo siempre estará habilitado al inicio

            checkboxes.forEach(function(chk) {
                if (!habilitarSiguiente) {
                    // Si el mes anterior no está marcado, forzamos a desmarcar y bloquear este
                    chk.checked = false;
                    chk.disabled = true;
                    chk.style.opacity = '0.4';
                    chk.style.cursor = 'not-allowed';
                } else {
                    // Si el mes anterior sí está marcado, habilitamos este para que pueda seleccionarse
                    chk.disabled = false;
                    chk.style.opacity = '1';
                    chk.style.cursor = 'pointer';
                }

                // La variable toma el valor de este checkbox para decidir qué hacer con el siguiente
                habilitarSiguiente = chk.checked;
            });
        }

        function actualizarBoton() {
            // 1. Ejecutamos la regla de cascada primero
            validarSecuencia();

            // 2. Ejecutamos tu código visual y de conteo
            let seleccionados = 0;
            checkboxes.forEach(chk => {
                const hiddenInputs = chk.closest('tr').querySelectorAll('.hidden-single-data');

                if (chk.checked) {
                    seleccionados++;
                    // Activamos la fila visualmente
                    chk.closest('tr').classList.add('table-primary');
                    // Activamos los campos ocultos
                    hiddenInputs.forEach(inp => inp.disabled = false);
                } else {
                    chk.closest('tr').classList.remove('table-primary');
                    hiddenInputs.forEach(inp => inp.disabled = true);
                }
            });

            // Actualizamos el número en el botón
            if(contador) contador.textContent = seleccionados;

            // Encendemos o apagamos el botón principal
            if (seleccionados > 0) {
                btnGenerar.disabled = false;
                btnGenerar.style.backgroundColor = '#dc3545'; // Rojo fuerte
                btnGenerar.style.opacity = '1';
            } else {
                btnGenerar.disabled = true;
                btnGenerar.style.backgroundColor = '#dc3545';
                btnGenerar.style.opacity = '0.6'; // Rojo pálido (efecto apagado)
                if(checkAll) checkAll.checked = false;
            }
        }

        // Evento para cada casilla individual
        checkboxes.forEach(chk => {
            chk.addEventListener('change', actualizarBoton);
        });

        // Evento para seleccionar todas de golpe (Se adapta a la cascada automáticamente)
        if(checkAll) {
            checkAll.addEventListener('change', function() {
                checkboxes.forEach(chk => {
                    // Nota: Solo marcamos si la casilla no está bloqueada
                    // Pero al usar checkAll, como es de arriba hacia abajo, se irán habilitando en cadena
                    chk.checked = this.checked;
                });
                actualizarBoton();
            });
        }

        // Ejecutar validación inicial para que los de abajo aparezcan bloqueados desde que entras
        actualizarBoton();
    });
</script>
@endsection
