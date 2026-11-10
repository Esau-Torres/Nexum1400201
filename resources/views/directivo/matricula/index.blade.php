@extends('layouts.app')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="container-fluid px-4 mt-4 mb-5">
    <!-- ================= ENCABEZADO ================= -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
        <div class="d-flex align-items-center">
            <h3 class="fw-bolder mb-0 text-uppercase d-flex align-items-center" style="color: #2c3e50; letter-spacing: 0.5px;">
                <i class="bi bi-person-badge text-danger me-3"></i> DASHBOARD DE ANÁLISIS DE POBLACIÓN
            </h3>
        </div>
        <div class="mt-3 mt-md-0">
            <span class="badge bg-secondary text-white px-3 py-2 rounded-2 fw-semibold">
                Modo Consulta Institucional
            </span>
        </div>
    </div>

    <!-- ================= FILTROS DE BÚSQUEDA ================= -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <form action="{{ route('directivo.matricula.index') }}" method="GET">
                <div class="row g-3 align-items-end">

                    <!-- Ciclo Académico -->
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted mb-1">Ciclo Académico</label>
                        <select name="ciclo_id" id="filtroCiclo" class="form-select border-light-subtle rounded-3">
                            @foreach($ciclos as $c)
                                <option value="{{ $c->ciclo_lectivo_id }}" {{ $cicloId == $c->ciclo_lectivo_id ? 'selected' : '' }}>
                                    Ciclo {{ $c->numero_ciclo }}-{{ $c->anio }} ({{ $c->estado }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Facultad -->
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted mb-1">Facultad</label>
                        <select name="facultad_id" id="filtroFacultad" class="form-select border-light-subtle rounded-3">
                            <option value="">Todas las Facultades</option>
                            @foreach($todasLasFacultades as $facultad)
                                <option value="{{ $facultad->facultad_id }}" {{ request('facultad_id') == $facultad->facultad_id ? 'selected' : '' }}>
                                    {{ $facultad->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Carrera -->
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted mb-1">Carrera</label>
                        <select name="carrera_id" id="filtroCarrera" class="form-select border-light-subtle rounded-3">
                            <option value="">Todas las Carreras</option>
                            @foreach($todasLasCarreras as $carrera)
                                <option value="{{ $carrera->carrera_id }}" data-facultad="{{ $carrera->id_facultad }}" {{ request('carrera_id') == $carrera->carrera_id ? 'selected' : '' }}>
                                    {{ $carrera->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Botones de Acción -->
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-danger w-100 rounded-3 d-flex align-items-center justify-content-center">
                            <i class="bi bi-filter me-2"></i> Filtrar
                        </button>
                        <a href="{{ route('directivo.matricula.index') }}" class="btn btn-secondary rounded-3 px-3" title="Limpiar Filtros">
                            <i class="bi bi-arrow-clockwise"></i>
                        </a>
                    </div>

                </div>
            </form>
        </div>
    </div>

    <!-- ================= TARJETAS DE KPI MATRÍCULA ================= -->
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-5 g-4 mb-4">

        <!-- 1. Matrícula Total -->
        <div class="col">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="border-left: 4px solid #0d6efd !important;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <p class="text-muted text-uppercase fw-bold mb-0 pe-2" style="font-size: 0.75rem;">MATRÍCULA TOTAL</p>
                        <div class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0" style="width: 40px; height: 40px; background-color: rgba(13, 110, 253, 0.1);">
                            <i class="bi bi-people-fill text-primary fs-5"></i>
                        </div>
                    </div>
                    <h2 class="fw-bolder text-dark mb-2" style="font-size: 2rem;">{{ $totalMatriculados ?? 0 }}</h2>
                    <p class="mb-0 small">
                        <span class="text-success fw-semibold"><i class="bi bi-arrow-up-right"></i> {{ $varMatricula ?? '0.0%' }}</span>
                        <span class="text-muted">vs ciclo anterior</span>
                    </p>
                </div>
            </div>
        </div>

        <!-- 2. Nuevo Ingreso -->
        <div class="col">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="border-left: 4px solid #198754 !important;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <p class="text-muted text-uppercase fw-bold mb-0 pe-2" style="font-size: 0.75rem;">NUEVO INGRESO</p>
                        <div class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0" style="width: 40px; height: 40px; background-color: rgba(25, 135, 84, 0.1);">
                            <i class="bi bi-person-plus-fill text-success fs-5"></i>
                        </div>
                    </div>
                    <h2 class="fw-bolder text-dark mb-2" style="font-size: 2rem;">{{ $nuevoIngreso ?? 0 }}</h2>
                    <p class="mb-0 small">
                        <span class="text-muted"><i class="bi bi-info-circle me-1"></i>Estudiantes de primer registro</span>
                    </p>
                </div>
            </div>
        </div>

        <!-- 3. Alumnos que continúan (Antiguo Ingreso) -->
        <div class="col">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="border-left: 4px solid #6f42c1 !important;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <p class="text-muted text-uppercase fw-bold mb-0 pe-2" style="font-size: 0.75rem;">ALUMNOS CONTINUOS</p>
                        <div class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0" style="width: 40px; height: 40px; background-color: rgba(111, 66, 193, 0.1);">
                            <i class="bi bi-arrow-repeat fs-5" style="color: #6f42c1;"></i>
                        </div>
                    </div>
                    <!-- Aquí ponemos directamente la cantidad en grande -->
                    <h2 class="fw-bolder text-dark mb-2" style="font-size: 2rem;">{{ $alumnosRetenidos ?? 0 }}</h2>
                    <p class="mb-0 small">
                        <span class="text-muted"><i class="bi bi-info-circle me-1"></i>Antiguo ingreso</span>
                    </p>
                </div>
            </div>
        </div>

        <!-- 4. Estudiantes Activos -->
        <div class="col">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="border-left: 4px solid #0dcaf0 !important;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <p class="text-muted text-uppercase fw-bold mb-0 pe-2" style="font-size: 0.75rem;">ESTUDIANTES ACTIVOS</p>
                        <div class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0" style="width: 40px; height: 40px; background-color: rgba(13, 202, 240, 0.1);">
                            <i class="bi bi-person-check-fill text-info fs-5"></i>
                        </div>
                    </div>
                    <h2 class="fw-bolder text-dark mb-2" style="font-size: 2rem;">{{ $estudiantesActivos ?? 0 }}</h2>
                    <p class="mb-0 small">
                        <span class="text-muted"><i class="bi bi-info-circle me-1"></i>Estado de carrera regular</span>
                    </p>
                </div>
            </div>
        </div>

        <!-- 5. Estudiantes Inactivos -->
        <div class="col">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="border-left: 4px solid #dc3545 !important;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <p class="text-danger text-uppercase fw-bold mb-0 pe-2" style="font-size: 0.75rem;">ESTUDIANTES INACTIVOS</p>
                        <div class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0" style="width: 40px; height: 40px; background-color: rgba(220, 53, 69, 0.1);">
                            <i class="bi bi-person-x-fill text-danger fs-5"></i>
                        </div>
                    </div>
                    <h2 class="fw-bolder text-danger mb-2" style="font-size: 2rem;">{{ $estudiantesInactivos ?? 0 }}</h2>
                    <p class="mb-0 small">
                        <span class="text-muted"><i class="bi bi-exclamation-triangle me-1"></i>Deserción o pausa académica</span>
                    </p>
                </div>
            </div>
        </div>

    </div>

    <!-- ================= PRIMERA FILA DE GRÁFICOS ================= -->
    <div class="row g-4 mb-4">
        <!-- Evolución Histórica (Gráfico de Barras) -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                <div class="card-header bg-white border-0 pt-4 pb-2 px-4 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-dark mb-0">
                        <i class="bi bi-bar-chart-fill text-primary me-2"></i> Evolución de Matrícula Histórica
                    </h6>
                </div>
                <div class="card-body px-4 pb-4 pt-3">
                    <div style="position: relative; height: 300px; width: 100%;">
                        <canvas id="chartEvolucionAcademica"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Distribución por Facultad (Dona con Leyenda Personalizada abajo) -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 pt-4 pb-2 px-4">
                    <h6 class="fw-bold text-dark mb-0">Composición por Facultad</h6>
                </div>
                <div class="card-body px-4 pb-4 d-flex flex-column justify-content-between">
                    <!-- Contenedor del Gráfico con el Total Adentro -->
                    <div style="position: relative; height: 220px; width: 100%;" class="d-flex align-items-center justify-content-center">
                        <canvas id="chartFacultadesAcademico"></canvas>
                        <!-- Texto central dentro de la dona -->
                        <div class="position-absolute text-center" style="pointer-events: none;">
                            <h3 class="fw-bolder text-dark mb-0" id="totalAlumnosDona" style="font-size: 1.5rem;">0</h3>
                            <span class="text-muted text-uppercase" style="font-size: 0.65rem; letter-spacing: 0.5px;">TOTAL ALUMNOS</span>
                        </div>
                    </div>

                    <!-- Leyenda personalizada abajo con cantidad y porcentaje -->
                    <div class="mt-4 pt-2 border-top" id="customLegendFacultades">
                        <!-- Se generará dinámicamente o puedes estructurarlo -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ================= SEGUNDA FILA DE GRÁFICOS ================= -->
    <div class="row g-4">
        <!-- Comparativa por Carrera (Barras Verticales Amplias - Ocupando todo el espacio) -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 pt-4 pb-2 px-4 d-flex align-items-center justify-content-between">
                    <h6 class="fw-bold text-dark mb-0">
                        <i class="bi bi-bar-chart-fill text-danger me-2"></i>Volumen Estudiantil por Carrera
                    </h6>
                </div>
                <div class="card-body px-4 pb-4">
                    <div style="position: relative; height: 350px; width: 100%;">
                        <canvas id="chartCarrerasAcademico"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Composición Poblacional con Resumen Ejecutivo -->
        <!-- Composición Poblacional (Expandida ocupando todo el espacio) -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 pt-4 pb-2 px-4">
                    <h6 class="fw-bold text-dark mb-0">
                        <i class="bi bi-pie-chart-fill text-danger me-2"></i>Composición Poblacional
                    </h6>
                </div>
                <!-- Usamos d-flex flex-column h-100 para estirar el contenido de arriba a abajo -->
                <div class="card-body px-4 pb-4 d-flex flex-column justify-content-between h-100">
                    <!-- Contenedor del Gráfico con mayor altura para aprovechar el espacio -->
                    <div style="position: relative; height: 260px; width: 100%;" class="d-flex align-items-center justify-content-center my-auto">
                        <canvas id="chartComposicionPoblacional"></canvas>
                    </div>

                    <!-- Mini Resumen / Métricas Clave en la parte inferior -->
                    @php
                        $totalCompCalc = ($nuevoIngreso + $alumnosRetenidos) > 0 ? ($nuevoIngreso + $alumnosRetenidos) : 1;
                        $porcNuevo = round(($nuevoIngreso / $totalCompCalc) * 100);
                        $porcCont = round(($alumnosRetenidos / $totalCompCalc) * 100);
                    @endphp

                    <div class="mt-3 pt-3 border-top">
                        <div class="row text-center g-2">
                            <div class="col-6 border-end">
                                <span class="text-muted small d-block">Nuevo Ingreso</span>
                                <span class="fw-bold text-success fs-5">{{ $nuevoIngreso }}</span>
                                <span class="badge bg-success bg-opacity-10 text-success ms-1" style="font-size: 0.7rem;">{{ $porcNuevo }}%</span>
                            </div>
                            <div class="col-6">
                                <span class="text-muted small d-block">Continuos</span>
                                <span class="fw-bold text-primary fs-5">{{ $alumnosRetenidos }}</span>
                                <span class="badge bg-primary bg-opacity-10 text-primary ms-1" style="font-size: 0.7rem;">{{ $porcCont }}%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {

    // 1. FILTRAR CARRERAS POR FACULTAD (Cascada automática)
    const filtroFacultad = document.getElementById('filtroFacultad');
    const filtroCarrera = document.getElementById('filtroCarrera');

    function actualizarCarreras() {
        if (!filtroFacultad || !filtroCarrera) return;
        const facultadSeleccionada = filtroFacultad.value;
        const opcionesCarrera = filtroCarrera.querySelectorAll('option');

        opcionesCarrera.forEach(option => {
            if (option.value === "") { option.style.display = 'block'; return; }
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

    // ================= CONFIGURACIÓN GLOBAL CHART.JS =================
    Chart.defaults.font.family = "'Segoe UI', 'Helvetica Neue', 'Arial', sans-serif";
    Chart.defaults.color = '#6c757d';

    // 2. GRÁFICO: Evolución Histórica (Con cantidad de estudiantes abajo)
    const labelsEvo = {!! $labelsEvolucion ?? '[]' !!};
    const datosEvo = {!! $datosEvolucion ?? '[]' !!}.map(n => parseInt(n) || 0);

    if(labelsEvo.length > 0) {
        const ctxEvo = document.getElementById('chartEvolucionAcademica').getContext('2d');

        new Chart(ctxEvo, {
            type: 'bar',
            data: {
                labels: labelsEvo,
                datasets: [{
                    label: 'Estudiantes Matriculados',
                    data: datosEvo,
                    backgroundColor: '#e63946', // Rojo institucional UMA
                    borderRadius: 6,
                    barPercentage: 0.5,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: { padding: { top: 15 } },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        bodyFont: { size: 14, weight: 'bold' },
                        callbacks: {
                            label: function(context) {
                                return ' ' + context.raw + ' estudiantes';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { borderDash: [4, 4], color: '#e9ecef' },
                        ticks: {
                            precision: 0,
                            font: { weight: '500' }
                        }
                    },
                    x: {
                        grid: { display: false },
                        ticks: {
                            font: { weight: '600', size: 11 },
                            color: '#495057',
                            callback: function(val, index) {
                                let cicloNombre = labelsEvo[index];
                                let totalEstudiantes = datosEvo[index];

                                // Retorna el nombre del ciclo arriba y los estudiantes abajo
                                return [cicloNombre, `${totalEstudiantes} estudiantes`];
                            }
                        }
                    }
                }
            }
        });
    }

    // ================= GRÁFICO: Volumen Estudiantil por Carrera (Barras Verticales) =================
    const ctxCarreras = document.getElementById('chartCarrerasAcademico');
    if (ctxCarreras) {
        const labelsCarrera = {!! $labelsCarrera ?? '[]' !!};
        const datosCarrera = {!! $datosCarrera ?? '[]' !!}.map(n => parseInt(n) || 0);

        if (labelsCarrera.length > 0) {
            new Chart(ctxCarreras, {
                type: 'bar', // Barras verticales principales
                data: {
                    labels: labelsCarrera,
                    datasets: [{
                        label: 'Estudiantes',
                        data: datosCarrera,
                        backgroundColor: '#e63946', // Rojo institucional UMA
                        borderRadius: 8, // Puntas superiores redondeadas modernas
                        barPercentage: 0.6,
                        borderSkipped: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: 12,
                            bodyFont: { size: 14, weight: 'bold' },
                            callbacks: {
                                label: function(context) {
                                    return ' ' + context.raw + ' estudiantes';
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: {
                                font: { weight: '600', size: 11 },
                                color: '#495057',
                                callback: function(val, index) {
                                    let nombre = labelsCarrera[index];
                                    let total = datosCarrera[index];

                                    // Recortamos el nombre si es muy largo
                                    let nombreCortado = nombre.length > 18 ? nombre.substring(0, 18) + '...' : nombre;

                                    // Retorna el nombre en la primera línea y la cantidad de estudiantes en la segunda
                                    return [nombreCortado, `${total} estudiantes`];
                                }
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: { borderDash: [4, 4], color: '#f8f9fa' },
                            ticks: {
                                precision: 0,
                                font: { weight: '500' }
                            }
                        }
                    }
                }
            });
        }
    }

    // 4. GRÁFICO: Composición por Facultad (Dona Ejecutiva con Total Central)
    const labelsFacultad = {!! $labelsFacultad ?? '[]' !!};
    const datosFacultad = {!! $datosFacultad ?? '[]' !!}.map(n => parseInt(n) || 0);

    if(labelsFacultad.length > 0) {
        const totalFacultades = datosFacultad.reduce((acc, val) => acc + val, 0);

        // Actualizamos el número central de la dona en el HTML
        const totalEl = document.getElementById('totalAlumnosDona');
        if (totalEl) totalEl.innerText = totalFacultades;

        const backgroundColors = ['#dc3545', '#0d6efd', '#20c997', '#ffc107', '#6f42c1', '#0dcaf0'];

        new Chart(document.getElementById('chartFacultadesAcademico'), {
            type: 'doughnut',
            data: {
                labels: labelsFacultad,
                datasets: [{
                    data: datosFacultad,
                    backgroundColor: backgroundColors,
                    borderWidth: 0,
                    cutout: '75%', // Anillo delgado y moderno
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }, // Ocultamos la leyenda lateral de Chart.js
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        bodyFont: { size: 14, weight: 'bold' },
                        callbacks: {
                            label: function(context) {
                                const val = context.raw;
                                const porcentaje = totalFacultades > 0 ? ((val / totalFacultades) * 100).toFixed(1) : 0;
                                return ` ${val} estudiantes (${porcentaje}%)`;
                            }
                        }
                    }
                }
            }
        });

        // Construcción de la leyenda personalizada inferior con cantidad y porcentaje
        const legendContainer = document.getElementById('customLegendFacultades');
        if (legendContainer) {
            let htmlList = '<div class="d-flex flex-column gap-2">';
            labelsFacultad.forEach((label, index) => {
                const valor = datosFacultad[index] || 0;
                const porcentaje = totalFacultades > 0 ? ((valor / totalFacultades) * 100).toFixed(1) : 0;
                const color = backgroundColors[index % backgroundColors.length];

                htmlList += `
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center pe-2">
                            <span class="rounded-circle me-2 flex-shrink-0" style="width: 10px; height: 10px; background-color: ${color};"></span>
                            <span class="text-dark small text-truncate" style="max-width: 180px;" title="${label}">${label}</span>
                        </div>
                        <div class="text-end flex-shrink-0">
                            <span class="fw-bold text-dark small">${valor}</span>
                            <span class="text-muted small ms-1">(${porcentaje}%)</span>
                        </div>
                    </div>
                `;
            });
            htmlList += '</div>';
            legendContainer.innerHTML = htmlList;
        }
    }

    // GRÁFICO: Composición Poblacional (Nuevo Ingreso vs Continuos)
    const ctxComposicion = document.getElementById('chartComposicionPoblacional');
    if (ctxComposicion) {
        const labelsComp = {!! $labelsComposicionJson ?? '[]' !!};
        const datosComp = {!! $datosComposicionJson ?? '[]' !!}.map(n => parseInt(n) || 0);
        const totalComp = datosComp.reduce((a, b) => a + b, 0);

        if (labelsComp.length > 0) {
            new Chart(ctxComposicion, {
                type: 'bar',
                data: {
                    labels: labelsComp,
                    datasets: [{
                        label: 'Estudiantes',
                        data: datosComp,
                        backgroundColor: ['#198754', '#0d6efd'], // Verde para nuevos, Azul para continuos
                        borderRadius: 6,
                        barPercentage: 0.5,
                        borderSkipped: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: 12,
                            bodyFont: { size: 14, weight: 'bold' },
                            callbacks: {
                                label: function(context) {
                                    const val = context.raw;
                                    const porcentaje = totalComp > 0 ? ((val / totalComp) * 100).toFixed(1) : 0;
                                    return ` ${val} estudiantes (${porcentaje}%)`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { borderDash: [4, 4], color: '#f8f9fa' },
                            ticks: { precision: 0, font: { weight: '500' } }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { font: { weight: '600', size: 11 } }
                        }
                    }
                }
            });
        }
    }
});
</script>
@endsection
