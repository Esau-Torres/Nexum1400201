@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">

    <!-- Encabezado -->
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="fw-bold text-dark">
                <i class="fa-solid fa-tags me-2" style="color: #ff7575;"></i> Promociones
            </h2>
            <p class="text-muted">Validación de descuentos aplicables según fecha y estado de solvencia (Solo Lectura).</p>
        </div>
    </div>

    <!-- Barra de Búsqueda Rápida -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body bg-light rounded">
            <div class="input-group input-group-lg">
                <span class="input-group-text bg-white border-end-0 text-muted">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </span>
                <input type="text" id="buscadorPromos" class="form-control border-start-0 ps-0" placeholder="Buscar promociones" autofocus>
            </div>
        </div>
    </div>

    <!-- Tabla de Promociones -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive" style="max-height: 60vh; overflow-y: auto;">
                <table class="table table-hover align-middle mb-0" id="tablaPromos">
                    <thead class="table-light sticky-top">
                        <tr>
                            <th scope="col" class="ps-4 py-3">Nombre de promoción</th>
                            <th scope="col" class="ps-4 py-3">Concepto aplicable</th>
                            <th scope="col" class="py-3 text-center">Descuento</th>
                            <th scope="col" class="py-3 text-center">Vigencia</th>
                            <th scope="col" class="py-3 text-center">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($promociones as $promocion)
                        <!-- La búsqueda ahora incluye el nombre de la promoción y el arancel -->
                        <tr class="fila-promo" data-busqueda="{{ strtolower(($promocion->nombre ?? '') . ' ' . ($promocion->concepto->nombre ?? '')) }}">

                            <!-- 1. Nombre de la Promoción (Ej. Feria Novembrina 2026) -->
                            <td class="ps-4 fw-bold text-dark" style="font-size: 1.05rem;">
                                {{ $promocion->nombre }}
                            </td>

                            <!-- 2. Concepto Aplicable -->
                            <td class="ps-4 fw-semibold text-secondary" style="font-size: 0.95rem;">
                                {{ strtoupper($promocion->concepto->nombre ?? 'ARANCEL') }}
                            </td>

                            <!-- 3. Descuento -->
                            <td class="text-center fw-bold" style="color: #ff7575; font-size: 1.1rem;">
                                {{ number_format($promocion->porcentaje_descuento, 2) }}%
                            </td>

                            <!-- 4. Fechas de Vigencia -->
                            <td class="text-center">
                                <span class="d-block fw-medium text-dark">{{ \Carbon\Carbon::parse($promocion->fecha_inicio)->format('d/m/Y') }}</span>
                                <span class="d-block text-muted" style="font-size: 0.85rem;">al {{ \Carbon\Carbon::parse($promocion->fecha_fin)->format('d/m/Y') }}</span>
                            </td>

                            <!-- 5. Estado dinámico basado en la fecha -->
                            <td class="text-center">
                                @php
                                    // Comparamos las fechas en el backend para mostrar el estado correcto
                                    $hoy = now();
                                    $inicio = \Carbon\Carbon::parse($promocion->fecha_inicio);
                                    $fin = \Carbon\Carbon::parse($promocion->fecha_fin);
                                @endphp

                                @if($hoy->between($inicio, $fin))
                                    <span class="badge bg-success rounded-pill px-3 py-2">Vigente</span>
                                @elseif($hoy->lt($inicio))
                                    <span class="badge rounded-pill px-3 py-2" style="background-color: #6c757d; color: white;">Próximamente</span>
                                @else
                                    <span class="badge bg-secondary rounded-pill px-3 py-2">Expirada</span>
                                @endif
                            </td>

                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Mensaje cuando no hay resultados en la búsqueda -->
            <div id="mensajeSinResultados" class="text-center py-5 d-none">
                <i class="fa-solid fa-tags fa-3x text-muted mb-3 opacity-50"></i>
                <h5 class="text-muted">No se encontró ninguna promoción con ese término.</h5>
            </div>

            <!-- Mensaje cuando la base de datos está vacía -->
            @if($promociones->isEmpty())
            <div class="text-center py-5">
                <i class="fa-solid fa-folder-open fa-3x text-muted mb-3 opacity-25"></i>
                <h5 class="text-muted">No hay promociones registradas en el sistema.</h5>
            </div>
            @endif
        </div>
    </div>

</div>

<!-- Script de búsqueda en tiempo real -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const buscador = document.getElementById("buscadorPromos");
        const filas = document.querySelectorAll(".fila-promo");
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
    });
</script>
@endsection
