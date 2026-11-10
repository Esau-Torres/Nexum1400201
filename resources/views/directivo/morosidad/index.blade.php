@extends('layouts.app')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="container-fluid px-4 py-4" style="background-color: #f4f6f9; min-height: 100vh;">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
            <i class="bi bi-wallet2 text-danger fs-3 me-3"></i>
            <h2 class="fw-bold mb-0 text-dark text-uppercase" style="letter-spacing: 0.5px;"> DASHBOARD MOROSIDAD Y CUENTAS POR COBRAR</h2>
        </div>
        <span class="badge bg-secondary px-3 py-2 rounded-2">Modo Consulta Institucional</span>
    </div>

    <!-- ================= FILTROS ================= -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <form action="{{ route('directivo.morosidad.index') }}" method="GET">
                <!-- Fila 1: Los 4 Filtros Académicos/Tiempo (3 columnas cada uno = 12) -->
                <div class="row g-3 align-items-end">

                    <!-- 1. Ciclo Académico -->
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted mb-1">Ciclo</label>
                        <select name="ciclo_id" id="filtroCiclo" class="form-select border-light-subtle rounded-3">
                            @foreach($ciclos as $c)
                                <option value="{{ $c->ciclo_lectivo_id }}" {{ $c->ciclo_lectivo_id == $cicloId ? 'selected' : '' }}>
                                    Ciclo {{ $c->numero_ciclo }}-{{ $c->anio }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- 2. Facultad -->
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted mb-1">Facultad</label>
                        <select name="facultad_id" id="filtroFacultad" class="form-select border-light-subtle rounded-3">
                            <option value="">Todas</option>
                            @foreach($todasLasFacultades as $f)
                                <option value="{{ $f->facultad_id }}" {{ request('facultad_id') == $f->facultad_id ? 'selected' : '' }}>
                                    {{ $f->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- 3. Carrera -->
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted mb-1">Carrera</label>
                        <select name="carrera_id" id="filtroCarrera" class="form-select border-light-subtle rounded-3">
                            <option value="">Todas las Carreras</option>
                            @foreach($todasLasCarreras as $c)
                                <option value="{{ $c->carrera_id }}" {{ request('carrera_id') == $c->carrera_id ? 'selected' : '' }}>
                                    {{ $c->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- 4. Rango de Fechas (Botón Elegante más ancho) -->
                    <div class="col-md-3">
                        <div class="dropdown w-100">
                            <!-- Botón imitando input -->
                            <button class="btn btn-white border border-light-subtle w-100 text-start d-flex align-items-center justify-content-between rounded-3 px-3" style="height: 58px; background-color: #ffffff;" type="button" data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="outside">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-calendar-event fs-4 text-secondary me-3"></i>
                                    <div class="d-flex flex-column">
                                        <span class="text-muted fw-bold" style="font-size: 0.70rem;">Rango de fechas</span>
                                        <span class="fw-bold text-dark" id="textoRangoFechas" style="font-size: 0.95rem;">Seleccionar...</span>
                                    </div>
                                </div>
                                <i class="bi bi-chevron-down text-secondary"></i>
                            </button>

                            <!-- Menú flotante -->
                            <div class="dropdown-menu p-3 shadow-lg border-0 rounded-4 mt-1" style="min-width: 260px;">
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-muted mb-1">Desde</label>
                                    <input type="date" name="fecha_inicio" id="filtroFechaInicio" class="form-control border-light-subtle rounded-3" value="{{ request('fecha_inicio') }}">
                                </div>
                                <div>
                                    <label class="form-label small fw-bold text-muted mb-1">Hasta</label>
                                    <input type="date" name="fecha_fin" id="filtroFechaFin" class="form-control border-light-subtle rounded-3" value="{{ request('fecha_fin') }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Fila 2: Botones de Acción (Alineados a la derecha) -->
                <div class="row mt-4">
                    <div class="col-12 d-flex justify-content-end gap-2">
                        <a href="{{ route('directivo.morosidad.index') }}" class="btn btn-light border-light-subtle rounded-3 px-4 d-flex align-items-center text-secondary fw-bold" title="Limpiar Filtros">
                            <i class="bi bi-arrow-clockwise me-2"></i> Limpiar
                        </a>
                        <button type="submit" class="btn btn-danger rounded-3 px-4 d-flex align-items-center fw-bold shadow-sm">
                            <i class="bi bi-funnel-fill me-2"></i> Aplicar Filtros
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- ================= TARJETAS DE INDICADORES (KPIs) ================= -->
    <div class="row g-4 mb-4 row-cols-1 row-cols-md-3 row-cols-xl-5">
        <!-- Deuda Total (Tarjeta con borde izquierdo rojo) -->
        <div class="col">
            <div class="card border-0 shadow-sm rounded-4 h-100 border-start border-4 border-danger">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <p class="small fw-bold text-muted mb-0 text-uppercase" style="font-size: 0.7rem;">DEUDA TOTAL PENDIENTE</p>
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background-color: #f8d7da; flex-shrink: 0;">
                            <i class="bi bi-cash-stack text-danger fs-5"></i>
                        </div>
                    </div>
                    <h2 class="fw-bold text-dark mb-2" style="font-size: 1.8rem;">${{ number_format($deudaTotal, 2) }}</h2>
                    <p class="small text-muted mb-0" style="font-size: 0.8rem;">
                        <span class="text-danger fw-bold"><i class="bi bi-arrow-up-short"></i>+{{ $varDeuda }}%</span> vs ciclo anterior
                    </p>
                </div>
            </div>
        </div>

        <!-- Estudiantes Morosos (Tarjeta con borde izquierdo azul) -->
        <div class="col">
            <div class="card border-0 shadow-sm rounded-4 h-100 border-start border-4 border-primary">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <p class="small fw-bold text-muted mb-0 text-uppercase" style="font-size: 0.7rem;">ESTUDIANTES CON CUOTAS PENDIENTES</p>
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background-color: #cce5ff; flex-shrink: 0;">
                            <i class="bi bi-people text-primary fs-5"></i>
                        </div>
                    </div>
                    <h2 class="fw-bold text-dark mb-2" style="font-size: 1.8rem;">{{ number_format($estudiantesMorosos) }}</h2>
                    <p class="small text-muted mb-0" style="font-size: 0.8rem;">
                        <span class="text-success fw-bold"><i class="bi bi-arrow-down-short"></i>{{ $varEstudiantes }}%</span> vs ciclo anterior
                    </p>
                </div>
            </div>
        </div>

        <!-- NUEVA: Cuotas Pendientes (Tarjeta con borde izquierdo morado) -->
        <div class="col">
            <div class="card border-0 shadow-sm rounded-4 h-100 border-start border-4 border-info" style="border-left-color: #6f42c1 !important;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <p class="small fw-bold text-muted mb-0 text-uppercase" style="font-size: 0.7rem;">CUOTAS PENDIENTES</p>
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background-color: #e0cffc; flex-shrink: 0;">
                            <i class="bi bi-receipt text-purple fs-5" style="color: #6f42c1;"></i>
                        </div>
                    </div>
                    <h2 class="fw-bold text-dark mb-2" style="font-size: 1.8rem;">{{ number_format($cuotasPendientes) }}</h2>
                    <p class="small text-muted mb-0" style="font-size: 0.8rem;">
                        <span class="{{ $varCuotas > 0 ? 'text-danger' : 'text-success' }} fw-bold">
                            <i class="bi bi-arrow-{{ $varCuotas > 0 ? 'up' : 'down' }}-short"></i>{{ $varCuotas > 0 ? '+' : '' }}{{ $varCuotas }}%
                        </span> vs ciclo anterior
                    </p>
                </div>
            </div>
        </div>

        <!-- Monto Vencido (Tarjeta con borde izquierdo naranja/warning) -->
        <div class="col">
            <div class="card border-0 shadow-sm rounded-4 h-100 border-start border-4 border-warning">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <p class="small fw-bold text-muted mb-0 text-uppercase" style="font-size: 0.7rem;">MONTO VENCIDO</p>
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background-color: #fff3cd; flex-shrink: 0;">
                            <i class="bi bi-exclamation-triangle text-warning fs-5"></i>
                        </div>
                    </div>
                    <h2 class="fw-bold text-dark mb-2" style="font-size: 1.8rem;">${{ number_format($cuotasVencidas, 2) }}</h2>
                    <p class="small text-muted mb-0" style="font-size: 0.8rem;">
                        <span class="text-danger fw-bold"><i class="bi bi-arrow-up-short"></i>+{{ $varVencidas }}%</span> vs ciclo anterior
                    </p>
                </div>
            </div>
        </div>

        <!-- Índice de Morosidad (Tarjeta con borde izquierdo verde) -->
        <div class="col">
            <div class="card border-0 shadow-sm rounded-4 h-100 border-start border-4 border-success">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <p class="small fw-bold text-muted mb-0 text-uppercase" style="font-size: 0.7rem;">ÍNDICE DE MOROSIDAD</p>
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background-color: #d1e7dd; flex-shrink: 0;">
                            <i class="bi bi-graph-down-arrow text-success fs-5"></i>
                        </div>
                    </div>
                    <h2 class="fw-bold text-dark mb-2" style="font-size: 1.8rem;">{{ $indiceMorosidad }}%</h2>
                    <p class="small text-muted mb-0" style="font-size: 0.8rem;">
                        <span class="text-danger fw-bold"><i class="bi bi-arrow-up-short"></i>+{{ $varIndice }} pp</span> vs ciclo anterior
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- ================= GRÁFICOS SUPERIORES ================= -->
    @php
        $datosF = json_decode($datosFacultad ?? '[]');
        $labelsF = json_decode($labelsFacultad ?? '[]');
        $totalFacultades = is_array($datosF) ? array_sum($datosF) : 0;
    @endphp

    <div class="row g-4 mb-4">

        <!-- 1. GRÁFICO DE EVOLUCIÓN (Ocupa 8 espacios) -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                <!-- Encabezado Rojo Suave -->
                <div class="card-header border-0 pt-3 pb-3 px-4 d-flex flex-wrap gap-2 justify-content-between align-items-center" style="background-color: #e63946;">
                    <h6 class="fw-bold text-white mb-0">
                        <i class="bi bi-graph-up-arrow me-2 text-white"></i> Evolución de la Deuda Institucional
                    </h6>
            <!-- Leyenda con los nombres actualizados -->
                    <div class="d-flex gap-3">
                        <span class="text-white small fw-bold d-flex align-items-center">
                            <span class="rounded-circle d-inline-block me-2" style="width: 14px; height: 14px; background-color: #e63946; border: 2px solid #ffffff;"></span>
                            Deuda Principal
                        </span>
                        <span class="text-white small fw-bold d-flex align-items-center">
                            <span class="rounded-circle d-inline-block me-2" style="width: 14px; height: 14px; background-color: #6c757d; border: 2px solid #ffffff;"></span>
                            Recargos por Mora
                        </span>
                    </div>
                </div>
                <div class="card-body px-4 pb-4 mt-2">
                    <div style="position: relative; height: 320px; width: 100%;">
                        <canvas id="chartEvolucionDeuda"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. DONA DE MOROSIDAD POR FACULTAD (Ocupa 4 espacios) -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                <div class="card-header border-0 pt-3 pb-3 px-4" style="background-color: #e63946;">
                    <h6 class="fw-bold text-white mb-0">
                        <i class="bi bi-pie-chart-fill me-2 text-white"></i> Morosidad por Facultad
                    </h6>
                </div>
                <!-- Usamos justify-content-between para rellenar todo el alto de la tarjeta -->
                <div class="card-body p-4 d-flex flex-column justify-content-between">

                    <!-- Contenedor de la Dona optimizado a 210px -->
                    <div class="d-flex justify-content-center align-items-center position-relative my-auto" style="height: 210px;">
                        <canvas id="chartFacultadesDeuda"></canvas>
                        <div class="position-absolute text-center" style="pointer-events: none;">
                            <h4 class="fw-bold text-dark mb-0" style="font-size: 1.35rem;">${{ number_format($totalFacultades >= 1000 ? $totalFacultades/1000 : $totalFacultades, 1) }}{{ $totalFacultades >= 1000 ? 'k' : '' }}</h4>
                            <span class="text-muted fw-bold" style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;">Total Mora</span>
                        </div>
                    </div>

                    <!-- Leyenda HTML distribuida a lo ancho -->
                    <div id="leyendaFacultades" class="w-100 pt-3 border-top border-light-subtle"></div>
                </div>
            </div>
        </div>

    </div>


    <!-- Estilos para compactar el buscador y paginación automático de DataTable -->
    <style>
        #tablaMorosos_wrapper .row:first-child {
            margin-bottom: 0.5rem !important;
            margin-top: 0.25rem !important;
        }
        #tablaMorosos_wrapper .row:last-child {
            margin-top: 0.25rem !important;
        }
        /* Hacemos el cuadro de búsqueda un poco más sutil */
        #tablaMorosos_filter input {
            padding: 0.25rem 0.5rem;
            font-size: 0.85rem;
        }
    </style>

    <!-- ================= TABLA Y RANKING INFERIOR ================= -->
    <div class="row g-4 mb-4">

        <!-- 1. TABLA ESTUDIANTES PENDIENTES (Izquierda) -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                <div class="card-header border-0 pt-3 pb-3 px-4" style="background-color: #e63946;">
                    <h6 class="fw-bold text-white mb-0 d-flex align-items-center">
                        <i class="bi bi-person-lines-fill text-white me-2 fs-5"></i> Estudiantes con cuotas pendientes
                    </h6>
                </div>

                <!-- Cambiamos pt-4 y pb-4 a pt-2 y pb-2 para eliminar el hueco interno -->
                <div class="card-body px-4 pb-2 pt-2">
                    <table class="table table-hover align-middle mb-0 w-100" style="font-size: 0.9rem;" id="tablaMorosos">
                        <thead style="border-bottom: 2px solid #dee2e6;">
                            <tr>
                                <th class="text-muted fw-bold border-0 pb-2">Estudiante</th>
                                <th class="text-muted fw-bold border-0 pb-2">Código</th>
                                <th class="text-muted fw-bold border-0 pb-2">Facultad</th>
                                <th class="text-muted fw-bold border-0 pb-2 text-center">Cuotas</th>
                                <th class="text-muted fw-bold border-0 pb-2">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($estudiantesMorososLista as $est)
                            <tr>
                                <td class="fw-bold text-dark py-2">{{ $est->nombre }}</td>
                                <td class="text-secondary py-2">{{ $est->codigo }}</td>
                                <td class="text-secondary py-2">{{ $est->facultad }}</td>
                                <td class="text-center py-2">
                                    <span class="badge bg-light text-dark border">{{ $est->cuotas }}</span>
                                </td>
                                <td class="py-2">
                                    <span class="badge bg-{{ $est->color }} bg-opacity-10 text-{{ $est->color }} border border-{{ $est->color }}-subtle rounded-pill px-3 py-1">
                                        {{ $est->estado }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No se encontraron estudiantes pendientes.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 2. RANKING: MAYOR DEUDA POR CARRERA (Derecha) -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                <div class="card-header border-0 pt-3 pb-3 px-4" style="background-color: #e63946;">
                    <h6 class="fw-bold text-white mb-0 d-flex align-items-center">
                        <i class="bi bi-trophy text-white me-2 fs-5"></i> Mayor deuda por carrera
                    </h6>
                </div>

                <div class="card-body px-4 pb-4 pt-3">
                    <div class="d-flex flex-column gap-3">
                        @php
                            $colores = ['bg-danger', 'bg-secondary', 'bg-secondary', 'bg-secondary', 'bg-secondary'];
                        @endphp

                        @forelse($deudaPorCarrera as $index => $item)
                            <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                                <div class="d-flex align-items-center gap-3" style="max-width: 75%;">
                                    <span class="badge {{ $colores[$index] ?? 'bg-secondary' }} rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 28px; height: 28px; font-size: 0.85rem;">
                                        {{ $index + 1 }}
                                    </span>
                                    <span class="text-dark text-truncate fw-medium" title="{{ $item->carrera }}" style="font-size: 0.9rem;">
                                        {{ $item->carrera }}
                                    </span>
                                </div>
                                <span class="fw-bold text-dark flex-shrink-0" style="font-size: 0.9rem;">
                                    ${{ number_format($item->total, 2) }}
                                </span>
                            </div>
                        @empty
                            <p class="text-muted text-center small mt-4">No hay datos de carreras para mostrar.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {

        // Paleta de Colores Limpia (Estilo Dashboard UMA)
        const colorPrimario = '#0d6efd';   // Azul
        const colorSecundario = '#dc3545'; // Rojo
        const colorTerciario = '#ffc107';  // Amarillo
        const colorCuarto = '#198754';     // Verde
        const colorGris = '#6c757d';       // Gris
        const gridColor = '#f0f0f0';

        // 1. Gráfico de Evolución (Deuda Principal vs Recargos)
        const ctxEvo = document.getElementById('chartEvolucionDeuda');
        if (ctxEvo) {
            const labelsEvo = {!! $labelsEvolucion ?? '[]' !!};

            // Convertimos los nuevos arreglos enviados por el controlador a números
            const dataDeuda = {!! $datosDeudaNeta ?? '[]' !!}.map(n => parseFloat(n) || 0);
            const dataRecargos = {!! $datosRecargos ?? '[]' !!}.map(n => parseFloat(n) || 0);

            if(labelsEvo.length > 0) {
                const contexto = ctxEvo.getContext('2d');

                // Crear degradado para la línea ROJA SUAVE (Deuda Principal)
                let gradientRed = contexto.createLinearGradient(0, 0, 0, 350);
                gradientRed.addColorStop(0, 'rgba(230, 57, 70, 0.4)'); // #e63946 transparente
                gradientRed.addColorStop(1, 'rgba(230, 57, 70, 0.0)');

                // Crear degradado para la línea GRIS CORPORATIVA (Recargos por Mora)
                let gradientGray = contexto.createLinearGradient(0, 0, 0, 350);
                gradientGray.addColorStop(0, 'rgba(108, 117, 125, 0.4)'); // Gris elegante
                gradientGray.addColorStop(1, 'rgba(108, 117, 125, 0.0)');

                // === PLUGIN DE LOS PRECIOS FLOTANTES ===
                const pluginPreciosFlotantes = {
                    id: 'preciosFlotantes',
                    afterDatasetsDraw(chart, args, pluginOptions) {
                        const { ctx, data } = chart;
                        ctx.save();
                        ctx.font = 'bold 12px "Segoe UI", sans-serif';
                        ctx.fillStyle = '#1a233a'; // Color del texto
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'bottom';

                        data.datasets.forEach((dataset, i) => {
                            const meta = chart.getDatasetMeta(i);
                            if (!meta.hidden) {
                                meta.data.forEach((element, index) => {
                                    const value = dataset.data[index];
                                    let text = '$' + value;

                                    if (value >= 1000) {
                                        text = '$' + (value / 1000).toFixed(1).replace(/\.0$/, '') + 'k';
                                    }

                                    // Corregido: Restamos 10 a element.y para que el texto flote arriba del punto
                                    ctx.fillText(text, element.x, element.y - 10);
                                });
                            }
                        });
                        ctx.restore();
                    }
                };
                // ==================================================

                new Chart(contexto, {
                    type: 'line',
                    data: {
                        labels: labelsEvo,
                        datasets: [
                            {
                                label: 'Deuda Principal',
                                data: dataDeuda,
                                borderColor: '#e63946', // <-- ROJO SUAVE
                                backgroundColor: gradientRed,
                                borderWidth: 3,
                                pointBackgroundColor: '#e63946',
                                pointBorderColor: '#ffffff',
                                pointBorderWidth: 2,
                                pointRadius: 5,
                                pointHoverRadius: 7,
                                fill: true,
                                tension: 0.1
                            },
                            {
                                label: 'Recargos por Mora',
                                data: dataRecargos,
                                borderColor: '#6c757d', // <-- GRIS CORPORATIVO
                                backgroundColor: gradientGray,
                                borderWidth: 3,
                                pointBackgroundColor: '#6c757d',
                                pointBorderColor: '#ffffff',
                                pointBorderWidth: 2,
                                pointRadius: 5,
                                pointHoverRadius: 7,
                                fill: true,
                                tension: 0.1
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        // Le damos un poco más de espacio arriba para que el texto del precio no se corte
                        layout: { padding: { top: 25 } },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                padding: 12,
                                titleFont: { size: 13 },
                                bodyFont: { size: 14, weight: 'bold' },
                                callbacks: {
                                    label: function(context) {
                                        return ' ' + context.dataset.label + ': $' + context.raw.toLocaleString('en-US', {minimumFractionDigits: 2});
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: { borderDash: [4, 4], color: '#f8f9fa' },
                                ticks: {
                                    callback: function(value) {
                                        if(value >= 1000) return '$' + (value/1000) + 'k';
                                        return '$' + value;
                                    }
                                }
                            },
                            x: {
                                grid: { display: false },
                                ticks: { font: { weight: 'bold' } }
                            }
                        }
                    },
                    // === ACTIVAMOS EL PLUGIN PARA QUE SE DIBUJE ===
                    plugins: [pluginPreciosFlotantes]
                });
            }
        }

        // 2. Gráfico Morosidad por Facultad (Dona optimizada con Leyenda HTML externa)
        const datosFacultades = {!! $datosFacultad !!}.map(num => parseFloat(num) || 0);
        const totalDeudaFacultades = datosFacultades.reduce((a, b) => a + b, 0);

        // Plugin para generar la leyenda limpia en el div HTML externo
        const pluginLeyendaHTML = {
            id: 'leyendaHTML',
            afterUpdate(chart, args, options) {
                const container = document.getElementById('leyendaFacultades');
                if(!container) return;

                container.innerHTML = '';

                const ul = document.createElement('ul');
                ul.className = 'w-100 d-flex flex-column gap-3 mb-0 p-0';
                ul.style.listStyle = 'none';

                chart.data.labels.forEach((label, i) => {
                    const valor = chart.data.datasets[0].data[i];
                    if(valor > 0) {
                        const porcentaje = totalDeudaFacultades > 0 ? ((valor / totalDeudaFacultades) * 100).toFixed(1) : 0;
                        const color = chart.data.datasets[0].backgroundColor[i];

                        const li = document.createElement('li');
                        li.className = 'd-flex align-items-center justify-content-between';

                        // Izquierda: Círculo de color y Nombre de la Facultad
                        const leftDiv = document.createElement('div');
                        leftDiv.className = 'd-flex align-items-center me-2';
                        leftDiv.style.maxWidth = '60%';

                        const box = document.createElement('span');
                        box.className = 'rounded-circle d-inline-block me-2 flex-shrink-0';
                        box.style.background = color;
                        box.style.width = '10px';
                        box.style.height = '10px';

                        const text = document.createElement('span');
                        text.className = 'text-secondary fw-semibold';
                        text.style.fontSize = '0.8rem';
                        text.innerText = label;

                        leftDiv.appendChild(box);
                        leftDiv.appendChild(text);

                        // Derecha: Monto y Porcentaje alineados al borde
                        const rightDiv = document.createElement('span');
                        rightDiv.className = 'fw-bold text-dark text-end flex-shrink-0';
                        rightDiv.style.fontSize = '0.8rem';
                        rightDiv.innerHTML = `$${valor.toLocaleString('en-US', {minimumFractionDigits: 2})} <span class="text-muted fw-normal small">(${porcentaje}%)</span>`;

                        li.appendChild(leftDiv);
                        li.appendChild(rightDiv);
                        ul.appendChild(li);
                    }
                });

                container.appendChild(ul);
            }
        };

        new Chart(document.getElementById('chartFacultadesDeuda'), {
            type: 'doughnut',
            data: {
                labels: {!! $labelsFacultad !!},
                datasets: [{
                    data: datosFacultades,
                    backgroundColor: ['#20c997', '#0d6efd', '#ffc107', '#fd7e14', '#6f42c1'],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: { display: false }, // Apagamos la leyenda interna para ganar espacio
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let valor = context.raw;
                                let porcentaje = totalDeudaFacultades > 0 ? ((valor / totalDeudaFacultades) * 100).toFixed(1) : 0;
                                return ' $' + valor.toLocaleString('en-US', {minimumFractionDigits: 2}) + ' (' + porcentaje + '%)';
                            }
                        }
                    }
                }
            },
            plugins: [pluginLeyendaHTML]
        });

        // 3. Gráfico Deuda por Carrera (Barras Azules limpias)
        new Chart(document.getElementById('chartCarrerasDeuda'), {
            type: 'bar',
            data: {
                labels: {!! $labelsCarrera !!},
                datasets: [{
                    data: {!! $datosCarrera !!},
                    backgroundColor: colorPrimario,
                    borderRadius: 4,
                    barThickness: 25
                }]
            },
            options: {
                indexAxis: 'x', // Cambiado a barras verticales como en tu captura
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: {
                            color: colorGris,
                            font: {size: 10},
                            // Rotar etiquetas si son muy largas
                            maxRotation: 45,
                            minRotation: 45
                        }
                    },
                    y: {
                        grid: { color: gridColor },
                        border: { display: false },
                        ticks: { display: false } // Ocultar eje Y numérico como en la captura
                    }
                },
                animation: {
                    onComplete: function() {
                        var ctx = this.ctx;
                        ctx.font = Chart.helpers.fontString(11, 'normal', Chart.defaults.font.family);
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'bottom';
                        ctx.fillStyle = colorGris;
                        this.data.datasets.forEach(function (dataset, i) {
                            var meta = this.getDatasetMeta(i);
                            meta.data.forEach(function (bar, index) {
                                var data = dataset.data[index];
                                ctx.fillText('$' + data.toLocaleString('en-US', {minimumFractionDigits: 2}), bar.x, bar.y - 5);
                            });
                        }, this);
                    }
                }
            }
        });
    });
</script>

<script>
    // ==========================================
    // 1. BUSCADOR EN TIEMPO REAL PARA LA TABLA
    // ==========================================
    document.getElementById('buscadorEstudiantes').addEventListener('keyup', function() {
        // Obtenemos lo que el usuario escribe, en minúsculas y sin acentos
        let filtro = this.value.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
        let filas = document.querySelectorAll('#tablaMorosos tbody .fila-estudiante');

        filas.forEach(function(fila) {
            // Obtenemos los textos de la tabla, en minúsculas y sin acentos
            let nombre = fila.querySelector('.nombre-estudiante').textContent.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
            let codigo = fila.querySelector('.codigo-estudiante').textContent.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");

            // Comparamos si coincide
            if (nombre.includes(filtro) || codigo.includes(filtro)) {
                fila.style.display = '';
            } else {
                fila.style.display = 'none';
            }
        });
    });

    // ==========================================
    // 2. LÓGICA DE FILTROS EN CASCADA (Facultad -> Carrera)
    // ==========================================
    const selectFacultad = document.getElementById('filtroFacultad');
    const selectCarrera = document.getElementById('filtroCarrera');

    if (selectFacultad && selectCarrera) {
        // Convertimos la colección de Laravel a un arreglo de JavaScript
        const todasLasCarreras = @json($todasLasCarreras);

        // Guardamos la carrera que estaba seleccionada (por si se recarga la página)
        const carreraSeleccionadaId = "{{ request('carrera_id') }}";

        function actualizarCarreras() {
            const facultadId = selectFacultad.value;

            // Limpiamos las opciones actuales del selector de carreras
            selectCarrera.innerHTML = '<option value="">Todas las Carreras</option>';

            // Filtramos el arreglo de carreras para que solo queden las de la facultad seleccionada
            const carrerasFiltradas = facultadId
                ? todasLasCarreras.filter(c => c.id_facultad == facultadId)
                : todasLasCarreras;

            // Llenamos el selector nuevamente con las carreras filtradas
            carrerasFiltradas.forEach(c => {
                const option = document.createElement('option');
                option.value = c.carrera_id;
                option.textContent = c.nombre;

                // Mantenemos seleccionada la carrera si coincide con el filtro activo
                if (c.carrera_id == carreraSeleccionadaId && (facultadId == "" || c.id_facultad == facultadId)) {
                    option.selected = true;
                }

                selectCarrera.appendChild(option);
            });
        }

        // Ejecutar el filtro cada vez que el usuario cambie la facultad
        selectFacultad.addEventListener('change', actualizarCarreras);

        // Ejecutarlo también al cargar la página para que aplique desde el principio
        actualizarCarreras();
    }

    // ==========================================
    // 3. LÓGICA DE FILTROS EN CASCADA (Ciclo -> Rango de Fechas)
    // ==========================================
    const selectCiclo = document.getElementById('filtroCiclo');
    const inputFechaInicio = document.getElementById('filtroFechaInicio');
    const inputFechaFin = document.getElementById('filtroFechaFin');
    const textoRangoFechas = document.getElementById('textoRangoFechas');

    if (selectCiclo && inputFechaInicio && inputFechaFin && textoRangoFechas) {

        // Función para formatear fechas de "YYYY-MM-DD" a "DD/MM/YYYY"
        function formatearFechaLatina(fechaISO) {
            if (!fechaISO) return '';
            const partes = fechaISO.split('-');
            if (partes.length !== 3) return '';
            return `${partes[2]}/${partes[1]}/${partes[0]}`;
        }

        // Función para actualizar el texto visual del botón
        function actualizarTextoBoton() {
            if (inputFechaInicio.value && inputFechaFin.value) {
                textoRangoFechas.textContent = `${formatearFechaLatina(inputFechaInicio.value)} - ${formatearFechaLatina(inputFechaFin.value)}`;
                textoRangoFechas.classList.remove('text-muted');
            } else if (inputFechaInicio.value) {
                textoRangoFechas.textContent = `Desde ${formatearFechaLatina(inputFechaInicio.value)}`;
                textoRangoFechas.classList.remove('text-muted');
            } else {
                textoRangoFechas.textContent = "Todos los meses";
                textoRangoFechas.classList.add('text-muted');
            }
        }

        // Función para limitar el calendario según el ciclo
        function actualizarLimitesFechas() {
            const textoCiclo = selectCiclo.options[selectCiclo.selectedIndex].text;
            const anioMatch = textoCiclo.match(/\d{4}/);
            const anio = anioMatch ? anioMatch[0] : new Date().getFullYear();

            // Asignar límites según el ciclo seleccionado
            if (textoCiclo.includes('01-')) {
                inputFechaInicio.min = `${anio}-01-01`; inputFechaInicio.max = `${anio}-06-30`;
                inputFechaFin.min = `${anio}-01-01`; inputFechaFin.max = `${anio}-06-30`;
            } else if (textoCiclo.includes('02-')) {
                inputFechaInicio.min = `${anio}-07-01`; inputFechaInicio.max = `${anio}-12-31`;
                inputFechaFin.min = `${anio}-07-01`; inputFechaFin.max = `${anio}-12-31`;
            } else {
                inputFechaInicio.min = `${anio}-01-01`; inputFechaInicio.max = `${anio}-12-31`;
                inputFechaFin.min = `${anio}-01-01`; inputFechaFin.max = `${anio}-12-31`;
            }

            // Limpiar valores si quedan fuera de rango por cambio de ciclo
            if (inputFechaInicio.value && (inputFechaInicio.value < inputFechaInicio.min || inputFechaInicio.value > inputFechaInicio.max)) {
                inputFechaInicio.value = "";
            }
            if (inputFechaFin.value && (inputFechaFin.value < inputFechaFin.min || inputFechaFin.value > inputFechaFin.max)) {
                inputFechaFin.value = "";
            }

            actualizarTextoBoton();
        }

        // Escuchadores de eventos
        inputFechaInicio.addEventListener('change', function() {
            // Evitar que la fecha fin sea menor a la de inicio
            inputFechaFin.min = this.value || selectCiclo.options[selectCiclo.selectedIndex].text.includes('01-') ? `${new Date().getFullYear()}-01-01` : `${new Date().getFullYear()}-07-01`;
            actualizarTextoBoton();
        });

        inputFechaFin.addEventListener('change', actualizarTextoBoton);

        selectCiclo.addEventListener('change', actualizarLimitesFechas);

        // Ejecutar al cargar la página
        actualizarLimitesFechas();
    }


</script>
@endsection
