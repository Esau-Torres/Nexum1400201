@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">

    @php
        // Determinar si se puede crear un nuevo ciclo (todos cerrados)
        $puedeCrearCiclo = !$ciclos->contains(fn($c) =>
            in_array($c->estado, [
                \App\Enums\EstadoCiclo::CONFIGURACION,
                \App\Enums\EstadoCiclo::MATRICULA_ABIERTA,
                \App\Enums\EstadoCiclo::ACTIVO,
            ])
        );
    @endphp

    {{-- HEADER --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">
                <i class="bi bi-calendar3 text-danger me-2"></i>Gestión de Ciclos Lectivos
            </h1>
            <p class="text-muted mb-0 small">Administración de los periodos académicos de la UMA.</p>
        </div>

        @if ($puedeCrearCiclo)
            <button type="button" class="btn btn-danger d-flex align-items-center gap-2"
                    data-bs-toggle="modal" data-bs-target="#createCicloModal">
                <i class="bi bi-plus-circle-fill"></i>
                Nuevo Ciclo Lectivo
            </button>
        @else
            <button type="button" class="btn btn-secondary d-flex align-items-center gap-2" disabled
                    title="Debe cerrar el ciclo en curso antes de crear uno nuevo.">
                <i class="bi bi-lock-fill"></i>
                Nuevo Ciclo (Bloqueado)
            </button>
        @endif
    </div>

    {{-- BARRA DE BÚSQUEDA Y FILTROS --}}
    <div class="card stat-card stat-card-light border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('admin-academico.ciclolectivo.ciclo') }}" id="filterForm">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label for="search" class="form-label small fw-semibold text-secondary">
                            <i class="bi bi-search me-1"></i>Buscar
                        </label>
                        <input type="text" class="form-control" id="search" name="search"
                               value="{{ request('search') }}"
                               placeholder="Nombre del ciclo o año...">
                    </div>

                    <div class="col-md-2">
                        <label for="estado" class="form-label small fw-semibold text-secondary">Estado</label>
                        <select name="estado" id="estado" class="form-select">
                            <option value="">Todos</option>
                            @foreach ($estados as $estado)
                                <option value="{{ $estado->value }}" @selected(request('estado') === $estado->value)>
                                    {{ $estado->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label for="numero_ciclo" class="form-label small fw-semibold text-secondary">Ciclo</label>
                        <select name="numero_ciclo" id="numero_ciclo" class="form-select">
                            <option value="">Todos</option>
                            @foreach ($tiposCiclo as $tipo)
                                <option value="{{ $tipo->value }}" @selected(request('numero_ciclo') === $tipo->value)>
                                    {{ $tipo->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label for="anio" class="form-label small fw-semibold text-secondary">Año</label>
                        <select name="anio" id="anio" class="form-select">
                            <option value="">Todos</option>
                            @foreach ($anios as $anio)
                                <option value="{{ $anio }}" @selected(request('anio') == $anio)>{{ $anio }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-danger flex-grow-1">
                            <i class="bi bi-funnel-fill me-1"></i>Filtrar
                        </button>
                        <a href="{{ route('admin-academico.ciclolectivo.ciclo') }}"
                           class="btn btn-outline-secondary" title="Limpiar filtros">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if (request()->anyFilled(['search', 'estado', 'numero_ciclo', 'anio']))
        <div class="alert alert-light border d-flex align-items-center gap-2 mb-3">
            <i class="bi bi-info-circle text-danger"></i>
            <span class="small">Mostrando <strong>{{ $ciclos->total() }}</strong> resultado(s) con los filtros aplicados.</span>
        </div>
    @endif

    {{-- GRID DE CICLOS --}}
    <div class="row g-4">
        @forelse ($ciclos as $ciclo)
            @php
                $esCerradoTerminal = $ciclo->estado === \App\Enums\EstadoCiclo::CERRADO
                                    && ($ciclo->fecha_fin->isPast() || $ciclo->fecha_fin->isToday());
                $esCerradoReabrible = $ciclo->estado === \App\Enums\EstadoCiclo::CERRADO && !$esCerradoTerminal;
            @endphp

            <div class="col-md-6 col-lg-4">
                <div class="card stat-card stat-card-light h-100">
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="stat-icon-circle">
                                <i class="bi bi-calendar3"></i>
                            </div>
                            <span class="badge rounded-pill
                                @if($ciclo->estado === \App\Enums\EstadoCiclo::ACTIVO) text-bg-success
                                @elseif($ciclo->estado === \App\Enums\EstadoCiclo::CERRADO) text-bg-secondary
                                @elseif($ciclo->estado === \App\Enums\EstadoCiclo::MATRICULA_ABIERTA) text-bg-warning
                                @else text-bg-info @endif">
                                {{ $ciclo->estado->label() }}
                            </span>
                        </div>

                        <h4 class="fw-bold text-dark mb-1">{{ $ciclo->nombre_ciclo }}</h4>
                        <p class="text-muted small mb-3">
                            <i class="bi bi-hash me-1"></i>Ciclo {{ $ciclo->numero_ciclo->value }} · {{ $ciclo->anio }}
                        </p>

                        <div class="d-flex flex-column gap-2 mb-4 text-secondary small">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-play-circle me-2 text-success"></i>
                                <span><strong>Inicio:</strong> {{ $ciclo->fecha_inicio->format('d/m/Y') }}</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <i class="bi bi-stop-circle me-2 text-danger"></i>
                                <span><strong>Fin:</strong> {{ $ciclo->fecha_fin->format('d/m/Y') }}</span>
                            </div>
                        </div>

                        @if ($esCerradoReabrible)
                            <div class="alert alert-warning small py-2 px-3 mb-3">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                Este ciclo fue cerrado pero su fecha de fin aún no ha llegado. Puede reabrirlo.
                            </div>
                        @endif

                        <div class="mt-auto d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-outline-danger flex-grow-1"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editCicloModal-{{ $ciclo->ciclo_lectivo_id }}">
                                <i class="bi bi-pencil-square me-1"></i>Editar
                            </button>

                            @if ($ciclo->estado !== \App\Enums\EstadoCiclo::CERRADO)
                                <form action="{{ route('admin-academico.ciclolectivo.changeStatus', $ciclo) }}"
                                      method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    @if($ciclo->estado === \App\Enums\EstadoCiclo::CONFIGURACION)
                                        <input type="hidden" name="nuevo_estado" value="{{ \App\Enums\EstadoCiclo::MATRICULA_ABIERTA->value }}">
                                        <button type="submit" class="btn btn-sm btn-outline-warning" title="Abrir Matrícula">
                                            <i class="bi bi-unlock-fill"></i>
                                        </button>
                                    @elseif($ciclo->estado === \App\Enums\EstadoCiclo::MATRICULA_ABIERTA)
                                        <input type="hidden" name="nuevo_estado" value="{{ \App\Enums\EstadoCiclo::ACTIVO->value }}">
                                        <button type="submit" class="btn btn-sm btn-outline-success" title="Iniciar Clases">
                                            <i class="bi bi-play-fill"></i>
                                        </button>
                                    @elseif($ciclo->estado === \App\Enums\EstadoCiclo::ACTIVO)
                                        <button type="button" class="btn btn-sm btn-outline-danger" title="Cerrar Ciclo"
                                                data-bs-toggle="modal"
                                                data-bs-target="#closeCicloModal-{{ $ciclo->ciclo_lectivo_id }}">
                                            <i class="bi bi-lock-fill"></i>
                                        </button>
                                    @endif
                                </form>
                            @elseif ($esCerradoReabrible)
                                <button type="button" class="btn btn-sm btn-outline-warning" title="Reabrir Ciclo"
                                        data-bs-toggle="modal"
                                        data-bs-target="#reopenCicloModal-{{ $ciclo->ciclo_lectivo_id }}">
                                    <i class="bi bi-arrow-counterclockwise"></i>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- ============================================= --}}
            {{-- MODAL EDITAR CICLO --}}
            {{-- ============================================= --}}
            <div class="modal fade" id="editCicloModal-{{ $ciclo->ciclo_lectivo_id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow">
                        <form action="{{ route('admin-academico.ciclolectivo.update', $ciclo) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="modal-header border-0 pb-0">
                                <h5 class="modal-title fw-bold">
                                    <i class="bi bi-pencil-square text-danger me-2"></i>Editar Ciclo
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body pt-2">
                                {{-- Datos visuales (no editables) --}}
                                <div class="p-3 rounded-3 mb-3" style="background-color: var(--color-special);">
                                    <div class="row text-center">
                                        <div class="col-6 border-end">
                                            <div class="stat-label-small">Año</div>
                                            <div class="fw-bold fs-5">{{ $ciclo->anio }}</div>
                                        </div>
                                        <div class="col-6">
                                            <div class="stat-label-small">Nº Ciclo</div>
                                            <div class="fw-bold fs-5">{{ $ciclo->numero_ciclo->label() }}</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-semibold">
                                        Nombre del Ciclo <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" name="nombre_ciclo"
                                           value="{{ old('nombre_ciclo', $ciclo->nombre_ciclo) }}"
                                           maxlength="50" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-semibold">
                                        Número de Ciclo <span class="text-danger">*</span>
                                    </label>
                                    <select name="numero_ciclo" class="form-select" required>
                                        @foreach ($tiposCiclo as $tipo)
                                            <option value="{{ $tipo->value }}" @selected($ciclo->numero_ciclo === $tipo)>
                                                {{ $tipo->label() }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-6">
                                        <label class="form-label small fw-semibold">
                                            Fecha Inicio <span class="text-danger">*</span>
                                        </label>
                                        <input type="date" class="form-control" name="fecha_inicio"
                                               value="{{ old('fecha_inicio', $ciclo->fecha_inicio->format('Y-m-d')) }}" required>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small fw-semibold">
                                            Fecha Fin <span class="text-danger">*</span>
                                        </label>
                                        <input type="date" class="form-control" name="fecha_fin"
                                               value="{{ old('fecha_fin', $ciclo->fecha_fin->format('Y-m-d')) }}" required>
                                    </div>
                                </div>

                                <div class="mb-2">
                                    <label class="form-label small fw-semibold">
                                        Estado <span class="text-danger">*</span>
                                    </label>
                                    <select name="estado" class="form-select" required>
                                        @foreach ($estados as $estado)
                                            <option value="{{ $estado->value }}" @selected($ciclo->estado === $estado)>
                                                {{ $estado->label() }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="form-text small">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Solo se puede marcar como <strong>Cerrado</strong> si la fecha de fin ya pasó.
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer border-0 pt-0">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-danger">
                                    <i class="bi bi-check-circle me-1"></i>Guardar Cambios
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- ============================================= --}}
            {{-- MODAL CERRAR CICLO --}}
            {{-- ============================================= --}}
            @if ($ciclo->estado === \App\Enums\EstadoCiclo::ACTIVO)
                <div class="modal fade" id="closeCicloModal-{{ $ciclo->ciclo_lectivo_id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 shadow">
                            <div class="modal-header border-0 pb-0">
                                <h5 class="modal-title fw-bold text-danger">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Confirmar Cierre
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <p class="mb-2">
                                    ¿Está seguro de cerrar el ciclo <strong>{{ $ciclo->nombre_ciclo }}</strong>?
                                </p>
                                <div class="alert alert-warning small mb-0">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Esta acción <strong>congelará todas las actas</strong> de calificaciones.
                                </div>
                            </div>
                            <div class="modal-footer border-0 pt-0">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                                <form action="{{ route('admin-academico.ciclolectivo.changeStatus', $ciclo) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="nuevo_estado" value="{{ \App\Enums\EstadoCiclo::CERRADO->value }}">
                                    <button type="submit" class="btn btn-danger">
                                        <i class="bi bi-lock-fill me-1"></i>Sí, Cerrar Ciclo
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- ============================================= --}}
            {{-- MODAL REABRIR CICLO --}}
            {{-- ============================================= --}}
            @if ($esCerradoReabrible)
                <div class="modal fade" id="reopenCicloModal-{{ $ciclo->ciclo_lectivo_id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 shadow">
                            <div class="modal-header border-0 pb-0">
                                <h5 class="modal-title fw-bold text-warning">
                                    <i class="bi bi-arrow-counterclockwise me-2"></i>Reabrir Ciclo
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <p>El ciclo <strong>{{ $ciclo->nombre_ciclo }}</strong> fue cerrado anticipadamente.</p>
                                <p class="small text-muted">Seleccione el estado al que desea regresarlo:</p>
                                <form action="{{ route('admin-academico.ciclolectivo.changeStatus', $ciclo) }}"
                                      method="POST" id="reopenForm-{{ $ciclo->ciclo_lectivo_id }}">
                                    @csrf
                                    @method('PATCH')
                                    <select name="nuevo_estado" class="form-select mb-3" required>
                                        <option value="{{ \App\Enums\EstadoCiclo::CONFIGURACION->value }}">Configuración</option>
                                        <option value="{{ \App\Enums\EstadoCiclo::MATRICULA_ABIERTA->value }}">Matrícula Abierta</option>
                                        <option value="{{ \App\Enums\EstadoCiclo::ACTIVO->value }}">Activo</option>
                                    </select>
                                </form>
                            </div>
                            <div class="modal-footer border-0 pt-0">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" form="reopenForm-{{ $ciclo->ciclo_lectivo_id }}" class="btn btn-warning">
                                    <i class="bi bi-check-circle me-1"></i>Reabrir Ciclo
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @empty
            {{-- ESTADO VACÍO --}}
            <div class="col-12">
                <div class="card stat-card stat-card-light border-0">
                    <div class="card-body text-center py-5">
                        <div class="stat-icon-circle mx-auto mb-3" style="width: 72px; height: 72px; font-size: 2rem;">
                            <i class="bi bi-calendar-x"></i>
                        </div>
                        <h4 class="fw-bold text-dark mb-2">
                            @if(request()->anyFilled(['search', 'estado', 'numero_ciclo', 'anio']))
                                No se encontraron ciclos con esos filtros
                            @else
                                No hay ciclos lectivos registrados
                            @endif
                        </h4>
                        <p class="text-muted mb-4">
                            @if(request()->anyFilled(['search', 'estado', 'numero_ciclo', 'anio']))
                                Intente ajustar los filtros o limpiar la búsqueda.
                            @else
                                Comience creando un nuevo ciclo para el año académico.
                            @endif
                        </p>
                        @if(request()->anyFilled(['search', 'estado', 'numero_ciclo', 'anio']))
                            <a href="{{ route('admin-academico.ciclolectivo.ciclo') }}" class="btn btn-outline-danger">
                                <i class="bi bi-x-lg me-1"></i>Limpiar Filtros
                            </a>
                        @elseif ($puedeCrearCiclo)
                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#createCicloModal">
                                <i class="bi bi-plus-circle me-1"></i>Crear Primer Ciclo
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    {{-- PAGINACIÓN --}}
    @if($ciclos->hasPages())
        <div class="mt-4 d-flex justify-content-center">
            {{ $ciclos->links() }}
        </div>
    @endif

    {{-- ============================================= --}}
    {{-- MODAL CREAR CICLO --}}
    {{-- ============================================= --}}
    @if ($puedeCrearCiclo)
        <div class="modal fade" id="createCicloModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <form action="{{ route('admin-academico.ciclolectivo.store') }}" method="POST">
                        @csrf
                        <div class="modal-header border-0 pb-0">
                            <h5 class="modal-title fw-bold">
                                <i class="bi bi-plus-circle-fill text-danger me-2"></i>Nuevo Ciclo Lectivo
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body pt-2">

                            <div class="row g-3 mb-3">
                                <div class="col-6">
                                    <label for="create_anio" class="form-label small fw-semibold">
                                        Año <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" class="form-control" id="create_anio" name="anio"
                                           min="2020" max="2100"
                                           value="{{ old('anio', date('Y')) }}" required>
                                </div>
                                <div class="col-6">
                                    <label for="create_numero_ciclo" class="form-label small fw-semibold">
                                        Número de Ciclo <span class="text-danger">*</span>
                                    </label>
                                    <select name="numero_ciclo" id="create_numero_ciclo" class="form-select" required>
                                        <option value="">Seleccione...</option>
                                        @foreach ($tiposCiclo as $tipo)
                                            <option value="{{ $tipo->value }}" @selected(old('numero_ciclo') === $tipo->value)>
                                                {{ $tipo->label() }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="create_nombre_ciclo" class="form-label small fw-semibold">
                                    Nombre del Ciclo <span class="text-muted">(opcional)</span>
                                </label>
                                <input type="text" class="form-control" id="create_nombre_ciclo" name="nombre_ciclo"
                                       value="{{ old('nombre_ciclo') }}"
                                       placeholder="Ej: CICLO 01-2026"
                                       maxlength="50">
                                <div class="form-text small">
                                    Si lo deja vacío, se generará automáticamente.
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-6">
                                    <label for="create_fecha_inicio" class="form-label small fw-semibold">
                                        Fecha Inicio <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" class="form-control" id="create_fecha_inicio"
                                           name="fecha_inicio" value="{{ old('fecha_inicio') }}" required>
                                </div>
                                <div class="col-6">
                                    <label for="create_fecha_fin" class="form-label small fw-semibold">
                                        Fecha Fin <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" class="form-control" id="create_fecha_fin"
                                           name="fecha_fin" value="{{ old('fecha_fin') }}" required>
                                </div>
                            </div>

                            <div class="alert alert-info small mt-3 mb-0">
                                <i class="bi bi-info-circle me-1"></i>
                                El ciclo se creará en estado <strong>Configuración</strong>.
                            </div>
                        </div>
                        <div class="modal-footer border-0 pt-0">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-danger">
                                <i class="bi bi-check-circle me-1"></i>Crear Ciclo
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection