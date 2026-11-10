@extends('layouts.app')

@section('title', 'Dashboard — Proyección de Secciones')

@section('content')
<div class="container-fluid py-4">

    {{-- ============================================================
         HEADER
         ============================================================ --}}
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-md-between gap-3 p-3 mb-4 rounded-4 card">
        <div class="d-flex align-items-center">
            <div class="px-3 py-2 rounded-3 bg-danger bg-opacity-10" style="width:48px;height:48px;">
                <i class="bi bi-graph-up-arrow fs-4 text-danger"></i>
            </div>
            <div class="px-3">
                <h1 class="h4 fw-bold mb-0">Dashboard de Proyección</h1>
                <p class="text-muted mb-0 small">
                    Secciones que deberías abrir en el próximo ciclo lectivo
                </p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('coordinador-facultad.secciones.index') }}"
               class="btn btn-outline-secondary px-3 py-2">
                <i class="bi bi-journal-bookmark me-1"></i> Secciones
            </a>
            <a href="{{ route('home') }}"
               class="btn bg-danger-subtle text-danger border border-danger px-3 py-2">
                <i class="fa-solid fa-home me-1"></i> Inicio
            </a>
        </div>
    </div>

    {{-- ============================================================
         FILTROS
         ============================================================ --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('coordinador-facultad.dashboard') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label small fw-medium text-muted">
                            <i class="bi bi-mortarboard me-1"></i> Carrera
                        </label>
                        <select name="id_carrera" class="form-select" required
                                onchange="this.form.submit()">
                            @foreach($carreras as $c)
                                <option value="{{ $c->carrera_id }}"
                                    {{ $idCarrera == $c->carrera_id ? 'selected' : '' }}>
                                    {{ $c->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label small fw-medium text-muted">
                            <i class="bi bi-calendar3 me-1"></i> Ciclo lectivo de referencia
                        </label>
                        <select name="id_ciclo_lectivo" class="form-select" required
                                onchange="this.form.submit()">
                            @foreach($ciclosLectivos as $c)
                                <option value="{{ $c->ciclo_lectivo_id }}"
                                    {{ $idCicloActual == $c->ciclo_lectivo_id ? 'selected' : '' }}>
                                    {{ $c->nombre_ciclo }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('coordinador-facultad.dashboard') }}"
                           class="btn btn-outline-secondary w-100"
                           title="Restablecer filtros">
                            <i class="bi bi-arrow-clockwise me-1"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ============================================================
         BANNER CONTEXTUAL
         ============================================================ --}}
    @if($proyeccion)
        <div class="alert alert-light border d-flex align-items-center gap-2 mb-3">
            <i class="bi bi-funnel-fill text-danger"></i>
            <div class="text-medium">
                Analizando
                <strong>{{ $carreras->firstWhere('carrera_id', $idCarrera)?->nombre ?? '—' }}</strong>
                · ciclo <strong>{{ $ciclosLectivos->firstWhere('ciclo_lectivo_id', $idCicloActual)?->nombre_ciclo ?? '—' }}</strong>
            </div>
        </div>
    @endif

    @if($proyeccion)

        {{-- ============================================================
             ESTADOS VACÍOS
             ============================================================ --}}
        @if(($proyeccion['resumen']['alumnos_activos'] ?? 0) === 0)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body text-center py-5">
                    <i class="bi bi-inbox fs-1 text-muted d-block mb-3"></i>
                    <h5 class="fw-bold mb-2">No hay alumnos activos para proyectar</h5>
                    <p class="text-muted mb-3 text-medium" >
                        La carrera seleccionada no tiene alumnos con estado <code>ACTIVO</code>,
                        o ninguno tiene materias cursadas en el ciclo
                        <strong>{{ $ciclosLectivos->firstWhere('ciclo_lectivo_id', $idCicloActual)?->nombre_ciclo ?? '' }}</strong>.
                    </p>
                    <div class="text-start small text-muted d-inline-block text-medium">
                        <strong>Verificá:</strong>
                        <ul class="mb-0 ps-3 mt-1">
                            <li>Que el ciclo lectivo tenga inscripciones registradas.</li>
                            <li>Que la carrera tenga pensum en estado <code>ACTIVO</code>.</li>
                            <li>Que las secciones estén vinculadas al pensum activo.</li>
                        </ul>
                    </div>
                </div>
            </div>
        @else

            {{-- ============================================================
                 RESUMEN EJECUTIVO — KPIs en una fila
                 ============================================================ --}}
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <i class="bi bi-people-fill text-primary"></i>
                                <span class="text-muted small">Alumnos activos</span>
                            </div>
                            <h2 class="h3 fw-bold mb-0">{{ $proyeccion['resumen']['alumnos_activos'] }}</h2>
                            <small class="text-muted">en la carrera</small>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <i class="bi bi-arrow-up-circle-fill text-success"></i>
                                <span class="text-muted small">Avanzan</span>
                            </div>
                            <h2 class="h3 fw-bold mb-0 text-success">
                                {{ $proyeccion['resumen']['alumnos_avanzan'] }}
                            </h2>
                            <small class="text-muted">al próximo ciclo</small>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <i class="bi bi-dash-circle-fill text-secondary"></i>
                                <span class="text-muted small">Sin actividad</span>
                            </div>
                            <h2 class="h3 fw-bold mb-0 text-secondary">
                                {{ $proyeccion['resumen']['alumnos_sin_actividad'] ?? 0 }}
                            </h2>
                            <small class="text-muted">en este ciclo</small>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <i class="bi bi-mortarboard-fill text-warning"></i>
                                <span class="text-muted small">Egresan</span>
                            </div>
                            <h2 class="h3 fw-bold mb-0 text-warning">
                                {{ $proyeccion['resumen']['alumnos_egresan'] }}
                            </h2>
                            <small class="text-muted">no requieren sección</small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ============================================================
                 PLAN DE ACCIÓN
                 ============================================================ --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <h5 class="fw-bold mb-0">
                                <i class="bi bi-list-check text-danger me-1"></i>
                                Plan de acción
                            </h5>
                            <small class="text-muted">
                                Secciones a crear para el próximo ciclo lectivo
                            </small>
                        </div>
                        <div class="d-flex gap-2">
                            <span class="badge bg-danger  align-self-center text-medium">
                                {{ $proyeccion['resumen']['secciones_totales'] ?? 0 }} sección(es)
                            </span>
                            @if(($proyeccion['resumen']['secciones_totales'] ?? 0) > 0)
                                <a href="{{ route('coordinador-facultad.dashboard.pdf', [
                                        'id_carrera'     => $idCarrera,
                                        'id_ciclo_lectivo' => $idCicloActual,
                                    ]) }}"
                                   class="btn btn-danger btn-sm fs-6">
                                    <i class="bi bi-file-earmark-pdf me-1"></i> Descargar PDF
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">

                    @forelse($proyeccion['proyeccion'] as $bloqueCiclo)
                        <div class="p-4 {{ !$loop->last ? 'border-bottom' : '' }}">

                            {{-- Encabezado del ciclo --}}
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center fw-bold"
                                     style="width: 40px; height: 40px;">
                                    {{ $bloqueCiclo['ciclo'] }}
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-0">Ciclo {{ $bloqueCiclo['ciclo'] }} del pensum</h6>
                                    <small class="text-muted">
                                        {{ count($bloqueCiclo['materias']) }} materia(s) a abrir
                                    </small>
                                </div>
                            </div>

                            {{-- Tabla de materias (sin columna Acción) --}}
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-3" style="width: 15%;">Código</th>
                                            <th>Materia</th>
                                            <th class="text-center" style="width: 15%;">Alumnos</th>
                                            <th class="text-center pe-3" style="width: 20%;">Secciones</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-medium">
                                        @foreach($bloqueCiclo['materias'] as $materia)
                                            <tr>
                                                <td class="ps-3 font-monospace small text-muted">
                                                    {{ $materia['codigo'] }}
                                                </td>
                                                <td class="fw-semibold">{{ $materia['nombre'] }}</td>
                                                <td class="text-center">
                                                    <span class="badge bg-light text-dark border">
                                                        {{ $materia['alumnos'] }} alumnos
                                                    </span>
                                                </td>
                                                <td class="text-center pe-3">
                                                    <span class="badge bg-danger">
                                                        {{ $materia['secciones_sugeridas'] }} sección(es)
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5">
                            <i class="bi bi-journal-x fs-1 text-muted d-block mb-3"></i>
                            <h6 class="fw-bold mb-2">Sin materias para proyectar</h6>
                            <p class="text-muted mb-0 small">
                                Los alumnos avanzan de ciclo, pero no hay
                                <strong>materias obligatorias</strong>
                                configuradas en el pensum activo para esos ciclos.
                            </p>
                        </div>
                    @endforelse

                </div>
            </div>

        @endif

    @else
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle-fill me-1"></i>
            Seleccioná una carrera y un ciclo lectivo para ver la proyección.
        </div>
    @endif

    {{-- ============================================================
         NOTA METODOLÓGICA
         ============================================================ --}}
    @if($proyeccion && ($proyeccion['resumen']['alumnos_activos'] ?? 0) > 0)
        <div class="alert alert-info small border-0 mt-4">
            <i class="bi bi-info-circle-fill me-1"></i>
            <strong>Cómo se calcula la proyección:</strong>
            <ul class="mb-0 ps-3 mt-2">
                <li>
                    Se toma el ciclo actual de cada alumno según sus materias
                    <code>CURSANDO</code>, <code>APROBADA</code>, <code>REPROBADA</code>,
                    <code>RECURSANDO</code> y <code>RETIRADA</code> en el ciclo seleccionado.
                </li>
                <li>El ciclo proyectado es el <strong>siguiente</strong> al actual.</li>
                <li>
                    Se sugiere <strong>1 sección por cada
                    {{ $proyeccion['resumen']['capacidad_aula_maxima'] ?? 40 }} alumnos</strong>
                    (según el aula más grande de tipo AULA disponible).
                </li>
                <li>Las materias optativas no se incluyen en la proyección.</li>
                <li>
                    Los alumnos sin actividad en el ciclo seleccionado no se proyectan
                    (aparecen en la columna "Sin actividad").
                </li>
            </ul>
        </div>
    @endif
</div>
@endsection