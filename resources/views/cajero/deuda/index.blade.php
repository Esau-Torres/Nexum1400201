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

                    <button type="submit" form="formCobroDeuda" id="btnGenerarRecibo" class="btn text-white fw-bold shadow-sm d-flex align-items-center gap-2 px-3" style="background-color: #ff9b9b; border: none; height: 48px; transition: all 0.3s;" disabled>
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

    <!-- NUEVO: TARJETA DE CONFIRMACIÓN DE IDENTIDAD -->
    @if(isset($alumno))
    <div class="card border-0 shadow-sm rounded-4 mb-4" style="background-color: #f0f7fb; border-left: 5px solid #dc3545 !important;">
        <div class="card-body p-3 d-flex align-items-center">
            <div class="bg-white rounded-circle d-flex align-items-center justify-content-center shadow-sm me-3" style="width: 50px; height: 50px;">
                <i class="fa-solid fa-user-check fs-4" style="color: #dc3545;"></i>
            </div>
            <div>
                <h6 class="text-muted mb-1" style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px;">Confirmar Identidad del Estudiante</h6>
                <h5 class="fw-bold text-dark mb-0">
                    {{ $alumno->nombres ?? 'Leandro Antonio' }} {{ $alumno->apellidos ?? 'Melara Villa' }}
                </h5>
                <span class="text-muted" style="font-size: 0.9rem;">
                    Carnet: <strong class="text-dark">{{ strtoupper($alumno->carnet ?? request('id_alumno')) }}</strong>
                </span>
            </div>
        </div>
    </div>
    @endif
    <!-- FIN NUEVO -->

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold"><i class="fa-solid fa-user-graduate me-2 text-muted"></i> Estado de cuenta: Alumno ID #{{ strtoupper(request('id_alumno')) }}</h5>
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
                                <th>Periodo</th>
                                <th>Concepto a Pagar</th>
                                <th class="text-center">Vencimiento</th>
                                <th class="text-center">Monto original</th>

                                <!-- Columna movida a la derecha -->
                                <th class="text-end pe-4">
                                    <label for="checkAllDeudas" class="me-2 fw-bold text-dark mb-0" style="cursor:pointer;">Seleccionar</label>
                                    <input class="form-check-input shadow-sm border-secondary" type="checkbox" id="checkAllDeudas" style="transform: scale(1.2); cursor:pointer;">
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($cargos as $cargo)
                            <tr class="border-bottom fila-deuda">
                                <td class="ps-4 py-3 fw-bold text-muted">#{{ $cargo->cargo_id }}</td>
                                <td class="py-3 text-dark">{{ str_pad($cargo->mes_arancel, 2, '0', STR_PAD_LEFT) }}/{{ $cargo->anio_arancel }}</td>
                                <td class="py-3 text-uppercase fw-medium" style="font-size: 0.9rem;">{{ $cargo->concepto->nombre ?? 'N/A' }}</td>
                                <td class="py-3 text-center">
                                    @php
                                        $vencido = \Carbon\Carbon::parse($cargo->fecha_vencimiento)->isPast();
                                    @endphp
                                    <span class="{{ $vencido ? 'text-danger fw-bold' : 'text-muted' }}">
                                        {{ \Carbon\Carbon::parse($cargo->fecha_vencimiento)->format('d/m/Y') }}
                                        @if($vencido) <i class="fa-solid fa-circle-exclamation ms-1" title="Vencido"></i> @endif
                                    </span>
                                </td>
                                <td class="py-3 text-center fw-bold fs-5 text-dark">${{ number_format($cargo->monto_original, 2) }}</td>

                                <!-- Checkbox individual a la derecha -->
                                <td class="py-3 text-end pe-4">
                                    <input class="form-check-input check-deuda shadow-sm border-secondary" type="checkbox" name="cargos_ids[]" value="{{ $cargo->cargo_id }}" style="transform: scale(1.2); cursor:pointer;">

                                    <!-- Campos ocultos de apoyo requeridos por el script -->
                                    <input type="hidden" name="id_cargo" value="{{ $cargo->cargo_id }}" disabled class="hidden-single-data">
                                    <input type="hidden" name="monto" value="{{ $cargo->monto_original }}" disabled class="hidden-single-data">
                                    <input type="hidden" name="concepto" value="{{ $cargo->concepto->nombre ?? 'N/A' }}" disabled class="hidden-single-data">
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-5">
                                    <i class="fa-regular fa-face-smile fs-1 d-block mb-3 text-success"></i>
                                    <h5 class="fw-bold">El alumno no tiene deuda pendiente.</h5>
                                    <p class="mb-0">Todos sus cargos están liquidados o anulados.</p>
                                </td>
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

<!-- SCRIPT PARA CONTROLAR LOS CHECKBOXES Y EL BOTÓN -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const checkboxes = document.querySelectorAll('.check-deuda');
        const checkAll = document.getElementById('checkAllDeudas');
        const btnGenerar = document.getElementById('btnGenerarRecibo');
        const contador = document.getElementById('contadorSeleccionados');

        function actualizarBoton() {
            let seleccionados = 0;
            checkboxes.forEach(chk => {
                const hiddenInputs = chk.closest('tr').querySelectorAll('.hidden-single-data');

                if (chk.checked) {
                    seleccionados++;
                    // Activamos la fila visualmente
                    chk.closest('tr').classList.add('table-primary');
                    // Activamos los campos ocultos por si se manda uno solo
                    hiddenInputs.forEach(inp => inp.disabled = false);
                } else {
                    chk.closest('tr').classList.remove('table-primary');
                    hiddenInputs.forEach(inp => inp.disabled = true);
                }
            });

            // Actualizamos el número en el botón
            if(contador) contador.textContent = seleccionados;

            // Encendemos o apagamos el botón principal y le damos el color fuerte
            if (seleccionados > 0) {
                btnGenerar.disabled = false;
                btnGenerar.style.backgroundColor = '#dc3545'; // Rojo fuerte
            } else {
                btnGenerar.disabled = true;
                btnGenerar.style.backgroundColor = '#dc3545'; // Rojo pálido
                if(checkAll) checkAll.checked = false;
            }
        }

        // Evento para cada casilla individual
        checkboxes.forEach(chk => {
            chk.addEventListener('change', actualizarBoton);
        });

        // Evento para seleccionar todas de golpe
        if(checkAll) {
            checkAll.addEventListener('change', function() {
                checkboxes.forEach(chk => {
                    chk.checked = this.checked;
                });
                actualizarBoton();
            });
        }
    });
</script>
@endsection
