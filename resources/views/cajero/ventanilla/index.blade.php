@extends('layouts.app') <!-- Cambia esto por tu layout principal si tiene otro nombre -->

@section('content')
<div class="container-fluid py-4">

    <!-- Encabezado de Ventanilla -->
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="fw-bold text-dark"><i class="fa-solid fa-cash-register me-2"></i> Consultas en ventanilla</h2>
            <p class="text-muted">Selecciona los aranceles a cobrar y presiona el botón para procesar el recibo.</p>
        </div>
    </div>

    <!-- INICIO DEL FORMULARIO -->
    <!-- IMPORTANTE: Cambia 'cajero.emision_recibos' por el nombre real de tu ruta -->
    <form action="{{ url('cajero/cajero/pagos/nuevo') }}" method="GET" id="formCobroAranceles">

        <!-- Barra de Búsqueda Rápida y Botón de Emisión -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body bg-light rounded d-flex justify-content-between align-items-center flex-wrap gap-3">

                <!-- Buscador más pequeño -->
                <div class="input-group" style="max-width: 450px;">
                    <span class="input-group-text bg-white border-end-0 text-muted">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </span>
                    <input type="text" id="buscadorAranceles" class="form-control border-start-0 ps-0" placeholder="Buscar (Ej. Reposición, MAT-001)..." autofocus>
                </div>

                <!-- NUEVO: Selector de Método de Pago -->
                <div class="ms-auto me-3">
                    <select name="metodo_pago" class="form-select fw-bold border-secondary" style="cursor: pointer;">
                        <option value="efectivo">💵 Efectivo</option>
                        <option value="tarjeta">💳 Tarjeta (POS)</option>
                    </select>
                </div>

                <!-- Nuevo Botón para ir a Pagar -->
                <button type="submit" id="btnEmitirRecibo" class="btn text-white fw-bold shadow-sm px-4" style="background-color: #ff7575; border-radius: 8px;" disabled>
                    <i class="fa-solid fa-file-invoice-dollar me-2"></i> Generar recibo
                    <span class="badge bg-white text-danger ms-1" id="contadorSeleccionados">0</span>
                </button>

            </div>
        </div>

        <!-- Tabla de Aranceles -->
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 60vh; overflow-y: auto;">
                    <table class="table table-hover align-middle mb-0" id="tablaAranceles">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th scope="col" class="ps-4 py-3">Código</th>
                                <th scope="col" class="py-3">Descripción del arancel</th>
                                <th scope="col" class="py-3 text-end">Precio</th>
                                <th scope="col" class="text-center py-3">Seleccionar</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($conceptos as $concepto)
                            <tr class="fila-arancel" data-busqueda="{{ strtolower($concepto->codigo_concepto . ' ' . $concepto->nombre . ' ' . $concepto->monto_base) }}">
                                <td class="ps-4 fw-bold text-secondary">{{ $concepto->codigo_concepto }}</td>
                                <td class="fw-bold">{{ strtoupper($concepto->nombre) }}</td>

                                <td class="text-end fw-bold" style="color: #000000; font-size: 1.1rem;">
                                    ${{ number_format($concepto->monto_base, 2) }}
                                </td>

                                <td class="text-center">
                                    <!-- Checkbox múltiple -->
                                    <!-- El name="aranceles[]" envía un arreglo con todos los IDs marcados al controlador -->
                                    <input class="form-check-input check-arancel border-secondary"
                                           type="checkbox"
                                           name="aranceles[]"
                                           value="{{ $concepto->concepto_pago_id }}"
                                           style="width: 1.5rem; height: 1.5rem; cursor: pointer;">
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Mensaje cuando la búsqueda no encuentre nada -->
                <div id="mensajeSinResultados" class="text-center py-5 d-none">
                    <i class="fa-solid fa-folder-open fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No se encontró ningún arancel con ese nombre o código.</h5>
                </div>
            </div>
        </div>
    </form>
    <!-- FIN DEL FORMULARIO -->

</div>

<!-- Scripts -->
<script>
    document.addEventListener("DOMContentLoaded", function() {

        // 1. Lógica del buscador en tiempo real
        const buscador = document.getElementById("buscadorAranceles");
        const filas = document.querySelectorAll(".fila-arancel");
        const mensajeVacio = document.getElementById("mensajeSinResultados");

        buscador.addEventListener("keyup", function() {
            let textoBusqueda = this.value.toLowerCase();
            let resultadosVisibles = 0;

            filas.forEach(function(fila) {
                if (fila.getAttribute("data-busqueda").includes(textoBusqueda)) {
                    fila.style.display = "";
                    resultadosVisibles++;
                } else {
                    fila.style.display = "none";
                }
            });

            if (resultadosVisibles === 0) {
                mensajeVacio.classList.remove("d-none");
            } else {
                mensajeVacio.classList.add("d-none");
            }
        });

        // 2. Lógica del contador y habilitación del botón
        const checkboxes = document.querySelectorAll('.check-arancel');
        const btnEmitir = document.getElementById('btnEmitirRecibo');
        const contadorSpan = document.getElementById('contadorSeleccionados');

        checkboxes.forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                // Cuenta cuántos checkboxes están marcados
                let marcados = document.querySelectorAll('.check-arancel:checked').length;

                // Actualiza el número en el botón
                contadorSpan.textContent = marcados;

                // Habilita el botón solo si hay 1 o más seleccionados
                if(marcados > 0) {
                    btnEmitir.disabled = false;
                } else {
                    btnEmitir.disabled = true;
                }
            });
        });

    });
</script>
@endsection
