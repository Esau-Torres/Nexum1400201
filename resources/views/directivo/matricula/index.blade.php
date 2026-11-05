@extends('layouts.app')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="container-fluid px-4 mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-dark"><i class="bi bi-people-fill text-danger me-2"></i> DASHBOARD MATRÍCULA Y DESERCIÓN</h2>
        <span class="badge bg-secondary">Modo Consulta Académica</span>
    </div>

    <!-- ================= FILTROS DE BÚSQUEDA ================= -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body bg-light rounded-4">
            <form action="{{ route('directivo.matricula.index') }}" method="GET" class="row g-3 align-items-end">
                <!-- Se eliminó el filtro de mes. Ahora Ciclo, Facultad y Carrera toman 4 columnas cada uno -->
                <div class="col-md-4">
                    <label class="form-label fw-bold text-muted small">Ciclo Académico</label>
                    <select name="ciclo_id" id="filtroCiclo" class="form-select shadow-none border-secondary-subtle">
                        @foreach($ciclos as $c)
                            <option value="{{ $c->ciclo_lectivo_id }}" {{ $cicloId == $c->ciclo_lectivo_id ? 'selected' : '' }}>
                                {{ $c->nombre_ciclo }} - {{ $c->anio }} ({{ $c->estado }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold text-muted small">Facultad</label>
                    <select name="facultad_id" id="filtroFacultad" class="form-select shadow-none border-secondary-subtle">
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
                    <select name="carrera_id" id="filtroCarrera" class="form-select shadow-none border-secondary-subtle">
                        <option value="">Todas las Carreras</option>
                        @foreach($todasLasCarreras as $carrera)
                            <option value="{{ $carrera->carrera_id }}" data-facultad="{{ $carrera->id_facultad }}" {{ request('carrera_id') == $carrera->carrera_id ? 'selected' : '' }}>
                                {{ $carrera->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Botones pasaron a su propia fila para mantener limpieza visual -->
                <div class="col-12 d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('directivo.matricula.index') }}" class="btn btn-outline-secondary fw-bold shadow-sm px-4" title="Limpiar Filtros">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Limpiar
                    </a>
                    <button type="submit" class="btn btn-danger fw-bold shadow-sm px-4">
                        <i class="bi bi-filter me-2"></i> Aplicar Filtros
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ================= TARJETAS DE INDICADORES ACADÉMICOS (KPIs) ================= -->
    <div class="row g-4 mb-4">
        <!-- Población Total (Rojo Nexum) -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="border-left: 5px solid #dc3545 !important;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <p class="text-muted fw-bold text-uppercase mb-1" style="font-size: 0.8rem;">Matrícula Total</p>
                            <h2 class="fw-bold text-dark mb-0">{{ number_format($totalMatriculados) }}</h2>
                        </div>
                        <div class="bg-danger bg-opacity-10 p-3 rounded-circle">
                            <i class="bi bi-person-badge text-danger fs-3"></i>
                        </div>
                    </div>
                    <div class="mt-2 text-{{ $variacionMatricula >= 0 ? 'success' : 'danger' }} small fw-bold">
                        <i class="bi bi-arrow-{{ $variacionMatricula >= 0 ? 'up' : 'down' }}"></i>
                        {{ $variacionMatricula >= 0 ? '+' : '' }}{{ number_format($variacionMatricula) }}
                        ({{ $variacionMatricula >= 0 ? '+' : '' }}{{ number_format($porcVariacion, 1) }}%)
                        <span class="text-muted fw-normal ms-1">vs ciclo anterior</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Nuevo Ingreso (Gris Oscuro Institucional) -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="border-left: 5px solid #212529 !important;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <p class="text-muted fw-bold text-uppercase mb-1" style="font-size: 0.8rem;">Nuevo Ingreso</p>
                            <h2 class="fw-bold text-dark mb-0">{{ number_format($nuevoIngreso) }}</h2>
                        </div>
                        <div class="bg-dark bg-opacity-10 p-3 rounded-circle">
                            <i class="bi bi-person-plus text-dark fs-3"></i>
                        </div>
                    </div>
                    <div class="mt-2 text-muted small fw-bold">
                        Primer registro académico registrado
                    </div>
                </div>
            </div>
        </div>

        <!-- Tasa de Retención (Se mantiene verde por UX universal) -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="border-left: 5px solid #198754 !important;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <p class="text-muted fw-bold text-uppercase mb-1" style="font-size: 0.8rem;">Tasa de Retención</p>
                            <h2 class="fw-bold text-dark mb-0">{{ number_format($tasaRetencion, 1) }}%</h2>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded-circle">
                            <i class="bi bi-arrow-repeat text-success fs-3"></i>
                        </div>
                    </div>
                    <div class="mt-2 text-success small fw-bold">
                        {{ number_format($continuidad) }} alumnos continuaron sus estudios
                    </div>
                </div>
            </div>
        </div>

        <!-- No Reinscritos / Riesgo (Se mantiene rojo de advertencia) -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="border-left: 5px solid #dc3545 !important;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <p class="text-muted fw-bold text-uppercase mb-1" style="font-size: 0.8rem;">No Reinscritos (Riesgo)</p>
                            <h2 class="fw-bold text-dark mb-0">{{ number_format($noReinscritos) }}</h2>
                        </div>
                        <div class="bg-danger bg-opacity-10 p-3 rounded-circle">
                            <i class="bi bi-exclamation-triangle text-danger fs-3"></i>
                        </div>
                    </div>
                    <div class="mt-2 text-danger small fw-bold">
                        Tasa de no reinscripción: {{ number_format($tasaNoReinscripcion, 1) }}%
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ================= GRÁFICOS Y TABLA DE CARRERAS ================= -->
    <div class="row g-4 mb-4">
        <!-- Evolución Histórica -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-graph-up-arrow text-danger me-2"></i> Evolución Histórica de Matrícula</h6>
                </div>
                <div class="card-body">
                    <div style="position: relative; height: 350px; width: 100%;">
                        <canvas id="chartEvolucionAcademica"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- TOP Carreras -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-trophy text-secondary me-2"></i> Mayor Matrícula (Carreras)</h6>
                </div>
                <div class="card-body overflow-auto" style="max-height: 350px;">
                    <ul class="list-group list-group-flush">
                        @forelse($matriculaPorCarrera->take(5) as $index => $item)
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div>
                                    <span class="badge {{ $index == 0 ? 'bg-danger' : 'bg-secondary' }} rounded-pill me-2">{{ $index + 1 }}</span>
                                    <span class="text-dark small">{{ \Illuminate\Support\Str::limit($item->carrera, 25) }}</span>
                                </div>
                                <span class="fw-bold text-dark">{{ number_format($item->total) }} est.</span>
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
        <!-- Comparativa por Carrera -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body">
                    <h6 class="fw-bold text-dark mb-3"><i class="bi bi-bar-chart-fill text-danger me-2"></i> Matrícula por Carrera</h6>
                    <div style="position: relative; height: 350px; width: 100%;">
                        <canvas id="chartCarrerasAcademico"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Participación por Facultad -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body d-flex flex-column">
                    <h6 class="fw-bold text-dark mb-3 w-100"><i class="bi bi-pie-chart-fill text-dark me-2"></i> Distribución por Facultad</h6>
                    <div style="position: relative; height: 350px; width: 100%;">
                        <canvas id="chartFacultadesAcademico"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {

    // 1. FILTRAR CARRERAS POR FACULTAD (La lógica de meses fue eliminada)
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

    // 2. GRÁFICOS CHART.JS ACADÉMICOS (Con nueva paleta de colores Nexum: Rojos y Grises)

    // Gráfico de Evolución
    const labelsEvo = {!! $labelsEvolucion !!};
    const datosEvo = {!! $datosEvolucion !!};
    if(labelsEvo.length > 0) {
        new Chart(document.getElementById('chartEvolucionAcademica'), {
            type: 'bar',
            data: {
                labels: labelsEvo,
                datasets: [{
                    label: 'Estudiantes',
                    data: datosEvo,
                    backgroundColor: 'rgba(220, 53, 69, 0.85)', // Rojo Bootstrap (Danger)
                    borderRadius: 5,
                    maxBarThickness: 150
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true },
                    x: {
                        ticks: {
                            callback: function(val, index) {
                                return [labelsEvo[index], datosEvo[index] + ' alumnos'];
                            }
                        }
                    }
                }
            }
        });
    }

    // Gráfico de Carreras
    const labelsCarrera = {!! $labelsCarrera !!};
    const datosCarrera = {!! $datosCarrera !!};
    if(labelsCarrera.length > 0) {
        new Chart(document.getElementById('chartCarrerasAcademico'), {
            type: 'bar',
            data: {
                labels: labelsCarrera,
                datasets: [{
                    label: 'Estudiantes',
                    data: datosCarrera,
                    backgroundColor: '#dc3545', // Rojo
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true },
                    x: {
                        ticks: {
                            callback: function(val, index) {
                                let nombre = labelsCarrera[index];
                                let nombreCorto = nombre.length > 15 ? nombre.substring(0, 15) + '...' : nombre;
                                return [nombreCorto, datosCarrera[index] + ' est.'];
                            }
                        }
                    }
                }
            }
        });
    }

    // Gráfico de Facultades (Dona con escala de grises y rojo corporativo)
    const labelsFacultad = {!! $labelsFacultad !!};
    const datosFacultad = {!! $datosFacultad !!};

    if(labelsFacultad.length > 0) {
        const totalFacultades = datosFacultad.reduce((acc, val) => acc + parseFloat(val), 0);

        const etiquetasFacultadConMonto = labelsFacultad.map((nombre, index) => {
            let total = parseFloat(datosFacultad[index]);
            let porcentaje = totalFacultades > 0 ? ((total / totalFacultades) * 100).toFixed(1) : 0;
            let prefijo = "";
            if (labelsFacultad.length > 1) {
                if (index === 0) prefijo = "⬆️ Mayor: ";
                else if (index === labelsFacultad.length - 1) prefijo = "⬇️ Menor: "; // Sintaxis corregida aquí
            }
            return `${prefijo}${nombre} - ${total} (${porcentaje}%)`;
        });

        new Chart(document.getElementById('chartFacultadesAcademico'), {
            type: 'doughnut',
            data: {
                labels: etiquetasFacultadConMonto,
                datasets: [{
                    data: datosFacultad,
                    // Paleta Nexum: Rojo principal, Gris oscuro, Gris medio, etc.
                    backgroundColor: ['#dc3545', '#212529', '#6c757d', '#495057', '#ced4da', '#f8f9fa'],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 12, padding: 12, font: { size: 11 } }
                    }
                }
            }
        });
    }

});
</script>
@endsection
