@extends('layouts.app')

@section('title', 'Dashboard — Proyección de Secciones')

@section('content')
<div class="container-fluid py-4">

    {{-- Header --}}
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-md-between gap-3 p-3 mb-4 rounded-4 card">
        <div class="d-flex align-items-center">
            <div class="px-3 py-2 rounded-3 bg-danger bg-opacity-10" style="width:48px;height:48px;">
                <i class="bi bi-graph-up-arrow fs-4 text-danger"></i>
            </div>
            <div class="px-3">
                <h1 class="h4 fw-bold mb-0">DASHBOARD DE PROYECIÓN</h1>
                <p class="text-muted mb-0 small">Secciones sugeridas para el próximo ciclo lectivo</p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('coordinador-facultad.secciones.index') }}" class="btn btn-outline-secondary px-3 py-2">
                <i class="bi bi-journal-bookmark me-1"></i> Secciones
            </a>
            <a href="{{ route('home') }}" class="btn bg-danger-subtle text-danger border border-danger px-3 py-2">
                <i class="fa-solid fa-home me-1"></i> Inicio
            </a>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('coordinador-facultad.dashboard') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label small fw-medium">Carrera *</label>
                        <select name="id_carrera" class="form-select" required onchange="this.form.submit()">
                            @foreach($carreras as $c)
                                <option value="{{ $c->carrera_id }}"
                                    {{ $idCarrera == $c->carrera_id ? 'selected' : '' }}>
                                    {{ $c->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-medium">Ciclo lectivo de referencia *</label>
                        <select name="id_ciclo_lectivo" class="form-select" required onchange="this.form.submit()">
                            @foreach($ciclosLectivos as $c)
                                <option value="{{ $c->ciclo_lectivo_id }}"
                                    {{ $idCicloActual == $c->ciclo_lectivo_id ? 'selected' : '' }}>
                                    {{ $c->nombre_ciclo }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text small">
                            Se analizan los alumnos que cursaron materias en este ciclo.
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check form-switch mt-4">
                            <input type="hidden" name="incluir_recursantes" value="0">
                            <input class="form-check-input" type="checkbox"
                                   name="incluir_recursantes" value="1"
                                   id="incluir_recursantes"
                                   {{ $incluirRecursantes ? 'checked' : '' }}
                                   onchange="this.form.submit()">
                            <label class="form-check-label small fw-medium" for="incluir_recursantes">
                                Incluir alumnos recursantes
                            </label>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if($proyeccion)

        {{-- Resumen --}}
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="text-muted small">Alumnos activos</span>
                            <i class="bi bi-people-fill text-primary fs-5"></i>
                        </div>
                        <h2 class="h3 fw-bold mb-0">{{ $proyeccion['resumen']['alumnos_activos'] }}</h2>
                        <small class="text-muted">en la carrera seleccionada</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="text-muted small">Avanzan al próximo ciclo</span>
                            <i class="bi bi-arrow-up-circle-fill text-success fs-5"></i>
                        </div>
                        <h2 class="h3 fw-bold mb-0 text-success">{{ $proyeccion['resumen']['alumnos_avanzan'] }}</h2>
                        <small class="text-muted">requieren sección</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="text-muted small">Egresan este ciclo</span>
                            <i class="bi bi-mortarboard-fill text-warning fs-5"></i>
                        </div>
                        <h2 class="h3 fw-bold mb-0 text-warning">{{ $proyeccion['resumen']['alumnos_egresan'] }}</h2>
                        <small class="text-muted">no requieren sección</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="text-muted small">Secciones sugeridas</span>
                            <i class="bi bi-journal-bookmark-fill text-danger fs-5"></i>
                        </div>
                        <h2 class="h3 fw-bold mb-0 text-danger">{{ $proyeccion['resumen']['secciones_totales'] ?? 0 }}</h2>
                        <small class="text-muted">a crear en el próximo ciclo</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Proyección por ciclo --}}
        @forelse($proyeccion['proyeccion'] as $bloqueCiclo)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold mb-0">
                            <i class="bi bi-layers-fill text-danger me-1"></i>
                            Ciclo {{ $bloqueCiclo['ciclo'] }} del pensum
                        </h6>
                        <span class="badge bg-secondary">
                            {{ count($bloqueCiclo['materias']) }} materia(s) obligatoria(s)
                        </span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Código</th>
                                    <th>Materia</th>
                                    <th>Alumnos</th>
                                    <th>Secciones sugeridas</th>
                                    <th class="text-end pe-4">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($bloqueCiclo['materias'] as $materia)
                                    <tr>
                                        <td class="ps-4 font-monospace small">{{ $materia['codigo'] }}</td>
                                        <td class="fw-semibold">{{ $materia['nombre'] }}</td>
                                        <td>{{ $materia['alumnos'] }}</td>
                                        <td>
                                            <span class="badge bg-danger">{{ $materia['secciones_sugeridas'] }} sección(es)</span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <a href="{{ route('coordinador-facultad.secciones.index') }}?id_materia={{ $materia['id_materia'] }}"
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-plus-circle me-1"></i> Crear sección
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @empty
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="bi bi-info-circle fs-1 text-muted d-block mb-3"></i>
                    <p class="text-muted mb-0">
                        No hay proyecciones para el filtro seleccionado.
                        <br>
                        <small>Verificá que la carrera tenga pensum activo y alumnos con materias cursadas en el ciclo de referencia.</small>
                    </p>
                </div>
            </div>
        @endforelse

    @else
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle-fill me-1"></i>
            Seleccioná una carrera y un ciclo lectivo para ver la proyección.
        </div>
    @endif

    {{-- Nota metodológica --}}
    <div class="alert alert-info small border-0 mt-4">
        <i class="bi bi-info-circle-fill me-1"></i>
        <strong>Cómo se calcula:</strong>
        Se toma el ciclo actual de cada alumno según sus materias <code>CURSANDO</code>
        {{ $incluirRecursantes ? 'y <code>RECURSANDO</code>' : '' }} en el ciclo lectivo seleccionado.
        El ciclo proyectado es el siguiente. Se sugiere
        <strong>1 sección por cada {{ $proyeccion['resumen']['capacidad_aula_maxima'] ?? 40 }} alumnos</strong>
        (basado en la capacidad del aula más grande de tipo AULA disponible).
        Las materias optativas no se incluyen.
    </div>
</div>
@endsection