@extends('layouts.app')

@section('title', 'Infraestructura')

@section('content')
<div class="container-fluid py-4">

    {{-- Header --}}
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-md-between gap-3 p-3 mb-4 rounded-4 card">
        <div class="d-flex align-items-center">
            <div class="px-3 py-2 rounded-3 bg-danger bg-opacity-10" style="width:48px;height:48px;">
                <i class="bi bi-building-gear fs-4 text-danger"></i>
            </div>
            <div class="px-3">
                <h1 class="h4 fw-bold mb-0">INFRAESTRUCTURA ACADEMICA</h1>
                <p class="text-muted mb-0 small">Gestión de edificios y aulas</p>
            </div>
        </div>
        <a href="{{ route('home') }}" class="btn bg-danger-subtle text-danger border border-danger px-3 py-2">
            <i class="fa-solid fa-home me-1"></i> Inicio
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-warning d-flex align-items-start gap-2 border-0 shadow-sm mb-4">
            <i class="bi bi-info-circle-fill"></i>
            <div>
                <strong class="d-block mb-1">Revisá los datos ingresados</strong>
                <ul class="mb-0 ps-3 small">
                    @foreach($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    {{-- Tabs --}}
    <ul class="nav nav-tabs mb-4" role="tablist">
        <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-edificios">
                <i class="bi bi-building me-1"></i> Edificios
                <span class="badge bg-secondary ms-1">{{ $edificios->count() }}</span>
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-aulas">
                <i class="bi bi-door-open me-1"></i> Aulas
                <span class="badge bg-secondary ms-1">{{ $aulas->count() }}</span>
            </button>
        </li>
    </ul>

    <div class="tab-content">

        {{-- ============================================================
             TAB EDIFICIOS
             ============================================================ --}}
        <div class="tab-pane fade show active" id="tab-edificios">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h6 fw-bold mb-0">
                    <i class="bi bi-building text-danger me-1"></i> Listado de Edificios
                </h2>
                <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalCrearEdificio">
                    <i class="bi bi-plus-circle me-1"></i> Nuevo edificio
                </button>
            </div>

            <div class="row g-4">
                @forelse($edificios as $edificio)
                    <div class="col-md-6 col-xl-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <h6 class="fw-bold mb-1">{{ $edificio->nombre }}</h6>
                                        <small class="text-muted d-block">
                                            <i class="bi bi-layers me-1"></i>
                                            {{ $edificio->niveles }} nivel(es)
                                        </small>
                                        <small class="text-muted d-block">
                                            <i class="bi bi-door-open me-1"></i>
                                            {{ $edificio->aulas->count() }} aula(s)
                                        </small>
                                    </div>
                                    <span class="badge {{ $edificio->activo ? 'text-bg-success' : 'text-bg-secondary' }}">
                                        {{ $edificio->activo ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </div>

                                @if($edificio->descripcion)
                                    <p class="small text-muted mb-3">{{ $edificio->descripcion }}</p>
                                @endif

                                <div class="d-flex gap-2">
                                    <button class="btn btn-sm btn-outline-primary flex-fill"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalEditarEdificio{{ $edificio->edificio_id }}">
                                        <i class="bi bi-pencil me-1"></i> Editar
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalEliminarEdificio{{ $edificio->edificio_id }}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center py-5">
                                <i class="bi bi-building fs-1 text-muted d-block mb-3"></i>
                                <p class="text-muted mb-0">No hay edificios registrados.</p>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- ============================================================
             TAB AULAS
             ============================================================ --}}
        <div class="tab-pane fade" id="tab-aulas">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h6 fw-bold mb-0">
                    <i class="bi bi-door-open text-danger me-1"></i> Listado de Aulas
                </h2>
                <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalCrearAula">
                    <i class="bi bi-plus-circle me-1"></i> Nueva aula
                </button>
            </div>

            <div class="card border-0 shadow-sm">
                <div>
                    <table class="table table-hover align-middle mb-0" id="aulatable">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Edificio</th>
                                <th>Nivel</th>
                                <th>Aula</th>
                                <th>Tipo</th>
                                <th>Capacidad</th>
                                <th>Estado</th>
                                <th class="text-end pe-4">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($aulas as $aula)
                                <tr>
                                    <td class="ps-4">{{ $aula->edificio->nombre ?? '—' }}</td>
                                    <td>{{ $aula->nivel }}</td>
                                    <td class="fw-semibold">{{ $aula->nombre_aula }}</td>
                                    <td>
                                        <span class="badge {{ $aula->tipo->badge() }}">
                                            <i class="bi {{ $aula->tipo->icon() }} me-1"></i>
                                            {{ $aula->tipo->label() }}
                                        </span>
                                    </td>
                                    <td>{{ $aula->capacidad }} est.</td>
                                    <td>
                                        <span class="badge {{ $aula->activo ? 'text-bg-success' : 'text-bg-secondary' }}">
                                            {{ $aula->activo ? 'Activa' : 'Inactiva' }}
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-sm btn-outline-primary me-1"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalEditarAula{{ $aula->aula_id }}">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalEliminarAula{{ $aula->aula_id }}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-5">
                                        <i class="bi bi-door-closed fs-2 d-block mb-2"></i>
                                        No hay aulas registradas.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ================================================================
     MODAL: CREAR EDIFICIO
     ================================================================ --}}
<div class="modal fade" id="modalCrearEdificio" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-white border-bottom">
                <h5 class="modal-title fw-bold">Nuevo Edificio</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('coordinador-facultad.edificios.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-medium">
                            Nombre <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="nombre" class="form-control"
                               placeholder="Ej: Edificio A" required maxlength="100">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-medium">
                            Niveles <span class="text-danger">*</span>
                        </label>
                        <input type="number" name="niveles" class="form-control"
                               value="1" min="1" max="50" required>
                        <div class="form-text small">Cantidad de pisos o niveles del edificio.</div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-medium">Descripción</label>
                        <textarea name="descripcion" rows="2" class="form-control"
                                  placeholder="Ubicación, notas, etc." maxlength="500"></textarea>
                    </div>
                    
                </div>
                <div class="modal-footer bg-light border-top-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-check-circle me-1"></i> Crear edificio
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ================================================================
     MODAL: CREAR AULA
     ================================================================ --}}
<div class="modal fade" id="modalCrearAula" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-white border-bottom">
                <h5 class="modal-title fw-bold">Nueva Aula</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('coordinador-facultad.aulas.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-medium">
                            Edificio <span class="text-danger">*</span>
                        </label>
                        <select name="id_edificio" class="form-select" required>
                            <option value="">Seleccione...</option>
                            @foreach($edificios as $ed)
                                @if($ed->activo == true)
                                    <option value="{{ $ed->edificio_id }}">{{ $ed->nombre }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-medium">
                                Nivel <span class="text-danger">*</span>
                            </label>
                            <input type="number" name="nivel" class="form-control"
                                   value="1" min="1" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-medium">
                                Nombre del aula <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="nombre_aula" class="form-control"
                                   placeholder="Ej: 101" maxlength="50" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-medium">
                                Capacidad <span class="text-danger">*</span>
                            </label>
                            <input type="number" name="capacidad" class="form-control"
                                   value="30" min="1" max="500" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-medium">
                                Tipo <span class="text-danger">*</span>
                            </label>
                            <select name="tipo" class="form-select" required>
                                @foreach($tiposAula as $tipo)
                                    <option value="{{ $tipo->value }}">{{ $tipo->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-check form-switch">
                        <input type="hidden" name="activo" value="0">
                        <input class="form-check-input" type="checkbox" name="activo" value="1"
                            id="grupoActivoCrear" checked>
                        <label class="form-check-label small" for="grupoActivoCrear">
                            Activar aula 
                        </label>
                    </div>
                </div>
                
                <div class="modal-footer bg-light border-top-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-check-circle me-1"></i> Crear aula
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ================================================================
     MODALES EDITAR / ELIMINAR: EDIFICIOS
     ================================================================ --}}
@foreach($edificios as $edificio)
    {{-- Editar --}}
    <div class="modal fade" id="modalEditarEdificio{{ $edificio->edificio_id }}" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-white border-bottom">
                    <h5 class="modal-title fw-bold">Editar: {{ $edificio->nombre }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('coordinador-facultad.edificios.update', $edificio) }}" method="POST">
                    @csrf @method('PUT')
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label small fw-medium">Nombre *</label>
                            <input type="text" name="nombre" class="form-control"
                                   value="{{ $edificio->nombre }}" required maxlength="100">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-medium">Niveles *</label>
                            <input type="number" name="niveles" class="form-control"
                                   value="{{ $edificio->niveles }}" min="1" max="50" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-medium">Descripción</label>
                            <textarea name="descripcion" rows="2" class="form-control" maxlength="500">{{ $edificio->descripcion }}</textarea>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="activo" value="1"
                                   id="edActivo{{ $edificio->edificio_id }}"
                                   {{ $edificio->activo ? 'checked' : '' }}>
                            <label class="form-check-label small" for="edActivo{{ $edificio->edificio_id }}">
                                Edificio activo
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-top-0">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Eliminar --}}
    <div class="modal fade" id="modalEliminarEdificio{{ $edificio->edificio_id }}" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-danger text-white border-0">
                    <h5 class="modal-title fw-bold">Eliminar edificio</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="mb-3">
                        ¿Confirmás eliminar el edificio <strong>{{ $edificio->nombre }}</strong>?
                    </p>
                    <div class="alert alert-warning small mb-0">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i>
                        Se eliminarán también sus <strong>{{ $edificio->aulas->count() }} aula(s)</strong> asociadas.
                        Esta acción no se puede deshacer.
                    </div>
                </div>
                <div class="modal-footer bg-light border-top-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form action="{{ route('coordinador-facultad.edificios.destroy', $edificio) }}" method="POST">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash me-1"></i> Eliminar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endforeach

{{-- ================================================================
     MODALES EDITAR / ELIMINAR: AULAS
     ================================================================ --}}
@foreach($aulas as $aula)
    <div class="modal fade" id="modalEditarAula{{ $aula->aula_id }}" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-white border-bottom">
                    <h5 class="modal-title fw-bold">Editar Aula</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('coordinador-facultad.aulas.update', $aula) }}" method="POST">
                    @csrf @method('PUT')
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label small fw-medium">Edificio *</label>
                            <select name="id_edificio" class="form-select" required>
                                @foreach($edificios as $ed)
                                    @if($ed->activo == true)
                                        <option value="{{ $ed->edificio_id }}"
                                            {{ $aula->id_edificio == $ed->edificio_id ? 'selected' : '' }}>
                                            {{ $ed->nombre }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-medium">Nivel *</label>
                                <input type="number" name="nivel" class="form-control"
                                       value="{{ $aula->nivel }}" min="1" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-medium">Nombre *</label>
                                <input type="text" name="nombre_aula" class="form-control"
                                       value="{{ $aula->nombre_aula }}" maxlength="50" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-medium">Capacidad *</label>
                                <input type="number" name="capacidad" class="form-control"
                                       value="{{ $aula->capacidad }}" min="1" max="500" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-medium">Tipo *</label>
                                <select name="tipo" class="form-select" required>
                                    @foreach($tiposAula as $tipo)
                                        <option value="{{ $tipo->value }}"
                                            {{ $aula->tipo->value === $tipo->value ? 'selected' : '' }}>
                                            {{ $tipo->label() }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-check form-switch mt-3">
                            <input type="hidden" name="activo" value="0">
                            <input class="form-check-input" type="checkbox" name="activo" value="1"
                                id="aulaActiva{{ $aula->aula_id }}" checked>
                            <label class="form-check-label small" for="aulaActiva{{ $aula->aula_id }}">
                                Activar aula 
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-top-0">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEliminarAula{{ $aula->aula_id }}" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-danger text-white border-0">
                    <h5 class="modal-title fw-bold">Eliminar aula</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="mb-0">
                        ¿Confirmás eliminar el aula <strong>{{ $aula->nombre_aula }}</strong>
                        del edificio <strong>{{ $aula->edificio->nombre ?? '—' }}</strong>?
                    </p>
                </div>
                <div class="modal-footer bg-light border-top-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form action="{{ route('coordinador-facultad.aulas.destroy', $aula) }}" method="POST">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash me-1"></i> Eliminar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endforeach
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ---- Persistencia de tabs ----
    const PATH_KEY = 'activeTab_' + window.location.pathname;
    const hash      = window.location.hash;
    const lastTabLS = localStorage.getItem(PATH_KEY);
    const tabTarget = hash || lastTabLS;

    if (tabTarget) {
        const trigger = document.querySelector(`[data-bs-toggle="tab"][data-bs-target="${tabTarget}"]`);
        if (trigger) new bootstrap.Tab(trigger).show();
    }

    document.querySelectorAll('[data-bs-toggle="tab"]').forEach(tab => {
        tab.addEventListener('shown.bs.tab', function (e) {
            const target = e.target.getAttribute('data-bs-target');
            localStorage.setItem(PATH_KEY, target);
            history.replaceState(null, null, target);
        });
    });

    // ---- Guard de modales ----
    document.addEventListener('click', function (e) {
        const trigger = e.target.closest('[data-bs-toggle="modal"]');
        if (!trigger) return;

        const target = trigger.getAttribute('data-bs-target');
        if (!target) return;

        const modal = document.querySelector(target);
        if (!modal) {
            console.error('⚠️ Modal no encontrado:', target);
            e.preventDefault();
            e.stopPropagation();
        }
    });
});
</script>
@endpush