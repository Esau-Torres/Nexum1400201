@extends('layouts.app')

@section('content')
<!-- Incluimos Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="container-fluid px-4 mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-dark"><i class="bi bi-graph-up-arrow text-danger me-2"></i> DASHBOARD EJECUTIVO DE INGRESOS</h2>
        <span class="badge bg-secondary">Modo Consulta Institucional</span>
    </div>

    <!-- ================= FILTROS DE BÚSQUEDA ================= -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body bg-light rounded-4">
            <form action="{{ route('directivo.dashboard.index') }}" method="GET" class="row g-3">
                <!-- Fila 1: Ciclo y Mes -->
                <div class="col-md-6">
                    <label class="form-label fw-bold text-muted small">Ciclo Académico</label>
                    <select name="ciclo_id" id="filtroCiclo" class="form-select shadow-none">
                        @foreach($ciclos as $c)
                            <option value="{{ $c->ciclo_lectivo_id }}" {{ $cicloId == $c->ciclo_lectivo_id ? 'selected' : '' }}>
                                {{ $c->nombre_ciclo }} - {{ $c->anio }} ({{ $c->estado }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold text-muted small">Mes</label>
                    <select name="mes" id="filtroMes" class="form-select shadow-none">
                        <option value="">Todos los meses</option>
                    </select>
                </div>

                <!-- Fila 2: Facultad, Carrera y Botones -->
                <div class="col-md-4">
                    <label class="form-label fw-bold text-muted small">Facultad</label>
                    <select name="facultad_id" id="filtroFacultad" class="form-select shadow-none">
                        <option value="">Todas las Facultades</option>
                        @foreach($todasLasFacultades as $facultad)
                            <option value="{{ $facultad->facultad_id }}" {{ request('facultad_id') == $facultad->facultad_id ? 'selected' : '' }}>
                                {{ $facultad->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold text-muted small">Carrera</label>
                    <select name="carrera_id" id="filtroCarrera" class="form-select shadow-none">
                        <option value="">Todas las Carreras</option>
                        @foreach($todasLasCarreras as $carrera)
                            <!-- AQUÍ ESTÁ EL TRUCO: data-facultad -->
                            <option value="{{ $carrera->carrera_id }}" data-facultad="{{ $carrera->id_facultad }}" {{ request('carrera_id') == $carrera->carrera_id ? 'selected' : '' }}>
                                {{ $carrera->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-danger w-100 fw-bold shadow-sm">
                        <i class="bi bi-filter me-2"></i> Aplicar Filtros
                    </button>
                    <!-- Botón para Limpiar (redirige al dashboard sin parámetros GET) -->
                    <a href="{{ route('directivo.dashboard.index') }}" class="btn btn-secondary w-50 fw-bold shadow-sm" title="Limpiar Filtros">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- ================= TARJETAS DE INDICADORES (KPIs) ================= -->
    <div class="row g-4 mb-4">
        <!-- Ingreso Total -->
        <div class="col-xl-4 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="border-left: 5px solid #dc3545 !important;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <p class="text-muted fw-bold text-uppercase mb-1" style="font-size: 0.8rem;">Recaudación Total (Cuotas)</p>
                            <h2 class="fw-bold text-dark mb-0">${{ number_format($totalIngresos, 2) }}</h2>
                        </div>
                        <div class="bg-danger bg-opacity-10 p-3 rounded-circle">
                            <i class="bi bi-cash-coin text-danger fs-3"></i>
                        </div>
                    </div>
                    <!-- Comparativa Porcentual y Monetaria -->
                    <div class="mt-2 text-{{ $difIngresos >= 0 ? 'success' : 'danger' }} small fw-bold">
                        <i class="bi bi-arrow-{{ $difIngresos >= 0 ? 'up' : 'down' }}"></i>
                        {{ $difIngresos >= 0 ? '+' : '' }}${{ number_format($difIngresos, 2) }}
                        ({{ $difIngresos >= 0 ? '+' : '' }}{{ number_format($porcIngresos, 1) }}%)
                        <span class="text-muted fw-normal ms-1">vs ciclo anterior</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transacciones -->
        <div class="col-xl-4 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="border-left: 5px solid #0d6efd !important;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <p class="text-muted fw-bold text-uppercase mb-1" style="font-size: 0.8rem;">Transacciones de Pago</p>
                            <h2 class="fw-bold text-dark mb-0">{{ number_format($totalTransacciones) }}</h2>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded-circle">
                            <i class="bi bi-receipt text-primary fs-3"></i>
                        </div>
                    </div>
                    <!-- Comparativa -->
                    <div class="mt-2 text-{{ $difTransacciones >= 0 ? 'success' : 'danger' }} small fw-bold">
                        <i class="bi bi-arrow-{{ $difTransacciones >= 0 ? 'up' : 'down' }}"></i>
                        {{ $difTransacciones >= 0 ? '+' : '' }}{{ number_format($difTransacciones) }} tr.
                        ({{ $difTransacciones >= 0 ? '+' : '' }}{{ number_format($porcTransacciones, 1) }}%)
                        <span class="text-muted fw-normal ms-1">vs ciclo anterior</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ticket Promedio -->
        <div class="col-xl-4 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="border-left: 5px solid #198754 !important;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <p class="text-muted fw-bold text-uppercase mb-1" style="font-size: 0.8rem;">Ingreso Promedio por Pago</p>
                            <h2 class="fw-bold text-dark mb-0">${{ number_format($ingresoPromedio, 2) }}</h2>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded-circle">
                            <i class="bi bi-graph-up text-success fs-3"></i>
                        </div>
                    </div>
                    <!-- Comparativa -->
                    <div class="mt-2 text-{{ $difPromedio >= 0 ? 'success' : 'danger' }} small fw-bold">
                        <i class="bi bi-arrow-{{ $difPromedio >= 0 ? 'up' : 'down' }}"></i>
                        {{ $difPromedio >= 0 ? '+' : '' }}${{ number_format($difPromedio, 2) }}
                        ({{ $difPromedio >= 0 ? '+' : '' }}{{ number_format($porcPromedio, 1) }}%)
                        <span class="text-muted fw-normal ms-1">vs ciclo anterior</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ================= GRÁFICOS Y TABLAS ================= -->
    <div class="row g-4 mb-4">
        <!-- Gráfico de Evolución -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-activity text-danger me-2"></i> Evolución de Ingresos</h6>
                </div>
                <div class="card-body">
                    @if(count(json_decode($labelsEvolucion)) > 0)
                        <canvas id="chartEvolucion" height="100"></canvas>
                    @else
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                            No hay datos financieros registrados para los filtros seleccionados.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- TOP Carreras -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-trophy text-warning me-2"></i> Mayor Recaudación (Carreras)</h6>
                </div>
                <div class="card-body overflow-auto" style="max-height: 350px;">
                    <ul class="list-group list-group-flush">
                        @forelse($ingresosPorCarrera->take(5) as $index => $ingreso)
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div>
                                    <span class="badge {{ $index == 0 ? 'bg-danger' : 'bg-secondary' }} rounded-pill me-2">{{ $index + 1 }}</span>
                                    <span class="text-dark small">{{ \Illuminate\Support\Str::limit($ingreso->carrera, 25) }}</span>
                                </div>
                                <span class="fw-bold text-dark">${{ number_format($ingreso->total, 2) }}</span>
                            </li>
                        @empty
                            <li class="list-group-item text-center text-muted border-0 pt-4">Sin registros.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- SECCIÓN COMPARATIVA DE FACULTADES Y CARRERAS -->
    <div class="row g-4 mb-4">
        <!-- Gráfico Comparativo Carreras (Barras) -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body">
                    <h6 class="fw-bold text-dark mb-3"><i class="bi bi-bar-chart-fill text-primary me-2"></i> Comparativa por Carrera</h6>
                    <!-- Contenedor con altura fija -->
                    <div style="position: relative; height: 350px; width: 100%;">
                        <canvas id="chartCarreras"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráfico Recaudación por Facultad (Dona) -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body d-flex flex-column">
                    <h6 class="fw-bold text-dark mb-3 w-100"><i class="bi bi-pie-chart-fill text-success me-2"></i> Recaudación por Facultad</h6>
                    <!-- Contenedor con altura fija -->
                    <div style="position: relative; height: 350px; width: 100%;">
                        <canvas id="chartFacultades"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {

    // ==============================================================
    // 1. LÓGICA DINÁMICA DE MESES EN ESPAÑOL SEGÚN EL CICLO
    // ==============================================================
    const filtroCiclo = document.getElementById('filtroCiclo');
    const filtroMes = document.getElementById('filtroMes');
    const mesSeleccionadoBackend = "{{ $mesSeleccionado ?? '' }}";

    const nombresMeses = {
        1: 'Enero', 2: 'Febrero', 3: 'Marzo', 4: 'Abril', 5: 'Mayo', 6: 'Junio',
        7: 'Julio', 8: 'Agosto', 9: 'Septiembre', 10: 'Octubre', 11: 'Noviembre', 12: 'Diciembre'
    };

    function actualizarMeses() {
        if (!filtroCiclo || !filtroMes) return;

        const cicloTexto = filtroCiclo.options[filtroCiclo.selectedIndex].text.toUpperCase();
        const nombreCicloPuro = cicloTexto.split('-')[0].trim();

        filtroMes.innerHTML = '<option value="">Todos los meses del ciclo</option>';

        let esCiclo2 = nombreCicloPuro.includes('02') || nombreCicloPuro.includes(' 2') || nombreCicloPuro.includes('II');

        let mesInicio = esCiclo2 ? 7 : 1;
        let mesFin = esCiclo2 ? 12 : 6;

        for (let i = mesInicio; i <= mesFin; i++) {
            let option = document.createElement('option');
            option.value = i;
            option.textContent = nombresMeses[i];

            if (mesSeleccionadoBackend == i) {
                option.selected = true;
            }
            filtroMes.appendChild(option);
        }
    }

    if(filtroCiclo) {
        actualizarMeses();
        filtroCiclo.addEventListener('change', actualizarMeses);
    }

    // ==============================================================
    // 1.5 LÓGICA DINÁMICA: FILTRAR CARRERAS POR FACULTAD
    // ==============================================================
    const filtroFacultad = document.getElementById('filtroFacultad');
    const filtroCarrera = document.getElementById('filtroCarrera');

    function actualizarCarreras() {
        if (!filtroFacultad || !filtroCarrera) return;

        const facultadSeleccionada = filtroFacultad.value;
        const opcionesCarrera = filtroCarrera.querySelectorAll('option');

        opcionesCarrera.forEach(option => {
            if (option.value === "") {
                option.style.display = 'block';
                return;
            }

            const idFacultadDeLaCarrera = option.getAttribute('data-facultad');

            if (facultadSeleccionada === "" || facultadSeleccionada === idFacultadDeLaCarrera) {
                option.style.display = 'block';
            } else {
                option.style.display = 'none';
            }
        });

        const opcionSeleccionada = filtroCarrera.options[filtroCarrera.selectedIndex];
        if (opcionSeleccionada && opcionSeleccionada.style.display === 'none') {
            filtroCarrera.value = "";
        }
    }

    if (filtroFacultad) {
        actualizarCarreras();
        filtroFacultad.addEventListener('change', actualizarCarreras);
    }

    // ==============================================================
    // 2. GRÁFICOS CHART.JS
    // ==============================================================

    // 1. Gráfico de Evolución
    const labelsEvo = {!! $labelsEvolucion !!};
    const datosEvo = {!! $datosEvolucion !!};
    if(labelsEvo.length > 0) {
        new Chart(document.getElementById('chartEvolucion'), {
            type: 'bar',
            data: {
                labels: labelsEvo,
                datasets: [{
                    label: 'Ingresos Mensuales ($)',
                    data: datosEvo,
                    backgroundColor: 'rgba(220, 53, 69, 0.85)',
                    borderRadius: 5,
                    maxBarThickness: 150
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true },
                    x: {
                        ticks: {
                            callback: function(val, index) {
                                let mesAnio = labelsEvo[index];
                                let montoFormateado = parseFloat(datosEvo[index]).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                                return [mesAnio, '$' + montoFormateado];
                            }
                        }
                    }
                }
            }
        });
    }

    // 2. Gráfico de Carreras
    const labelsCarrera = {!! $labelsCarrera !!};
    const datosCarrera = {!! $datosCarrera !!};
    if(labelsCarrera.length > 0) {
        new Chart(document.getElementById('chartCarreras'), {
            type: 'bar',
            data: {
                labels: labelsCarrera,
                datasets: [{
                    label: 'Ingresos ($)',
                    data: datosCarrera,
                    backgroundColor: '#0d6efd',
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false, // <-- Evita que se estire
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true },
                    x: {
                        ticks: {
                            callback: function(val, index) {
                                let nombre = labelsCarrera[index];
                                let nombreCorto = nombre.length > 15 ? nombre.substring(0, 15) + '...' : nombre;
                                let montoFormateado = parseFloat(datosCarrera[index]).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                                return [nombreCorto, '$' + montoFormateado];
                            }
                        }
                    }
                }
            }
        });
    }

    // 3. Gráfico de Facultades
    const labelsFacultad = {!! $labelsFacultad !!};
    const datosFacultad = {!! $datosFacultad !!};

    if(labelsFacultad.length > 0) {
        const totalFacultades = datosFacultad.reduce((acc, val) => acc + parseFloat(val), 0);

        const etiquetasFacultadConMonto = labelsFacultad.map((nombre, index) => {
            let monto = parseFloat(datosFacultad[index]);
            let porcentaje = totalFacultades > 0 ? ((monto / totalFacultades) * 100).toFixed(1) : 0;
            let montoFormateado = monto.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            let prefijo = "";
            if (labelsFacultad.length > 1) {
                if (index === 0) prefijo = "⬆️ Mayor: ";
                else if (index === labelsFacultad.length - 1) prefijo = "⬇️ Menor: ";
            }

            return `${prefijo}${nombre} - $${montoFormateado} (${porcentaje}%)`;
        });

        new Chart(document.getElementById('chartFacultades'), {
            type: 'doughnut',
            data: {
                labels: etiquetasFacultadConMonto,
                datasets: [{
                    data: datosFacultad,
                    backgroundColor: ['#198754', '#0d6efd', '#ffc107', '#dc3545', '#6f42c1', '#fd7e14'],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false, // <-- Evita que se estire
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 12,
                            padding: 12,
                            font: { size: 11 }
                        }
                    }
                }
            }
        });
    }

});
</script>

@endsection
