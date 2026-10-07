@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">

    {{-- HEADER --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">
                <i class="bi bi-journal-bookmark-fill text-danger me-2"></i>Gestión de Materias
            </h1>
            <p class="text-muted mb-0 small">Administración del catálogo de asignaturas de la UMA.</p>
        </div>
        <button type="button" class="btn btn-danger d-flex align-items-center gap-2"
                data-bs-toggle="modal" data-bs-target="#createMateriaModal">
            <i class="bi bi-plus-circle-fill"></i>
            Nueva Materia
        </button>
    </div>

    {{-- FILTROS --}}
        <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('admin-academico.materias.materias') }}">
                <div class="d-flex flex-wrap align-items-end gap-3 justify-content-end">

                    <div style="min-width: 160px;">
                        <label for="estado" class="form-label small fw-semibold text-secondary mb-1">Estado</label>
                        <select name="estado" id="estado" class="form-select form-select-sm">
                            <option value="">Todos</option>
                            @foreach ($estados as $estado)
                                <option value="{{ $estado->value }}" @selected(request('estado') === $estado->value)>
                                    {{ $estado->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div style="min-width: 240px;">
                        <label for="id_pensum" class="form-label small fw-semibold text-secondary mb-1">Pensum</label>
                        <select name="id_pensum" id="id_pensum" class="form-select form-select-sm">
                            <option value="">Todos</option>
                            @foreach ($pensums as $pensum)
                                <option value="{{ $pensum->pensum_id }}" @selected(request('id_pensum') == $pensum->pensum_id)>
                                    {{ $pensum->nombreCompleto() }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-sm btn-danger">
                            <i class="bi bi-funnel-fill me-1"></i>Filtrar
                        </button>
                        <a href="{{ route('admin-academico.materias.materias') }}"
                           class="btn btn-sm btn-outline-secondary"
                           title="Limpiar filtros">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- TABLA --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div>
                <table class="table align-middle mb-0" style="font-size: 0.9rem;" id="materiasTable">
                    <thead style="background-color: #fafbfc; border-bottom: 2px solid #eef0f2;">
                        <tr>
                            <th class="px-4 py-3 text-secondary small fw-semibold text-uppercase">Código</th>
                            <th class="py-3 text-secondary small fw-semibold text-uppercase">Nombre</th>
                            <th class="py-3 text-center text-secondary small fw-semibold text-uppercase">UV</th>
                            <th class="py-3 text-center text-secondary small fw-semibold text-uppercase">Estado</th>
                            <th class="py-3 text-secondary small fw-semibold text-uppercase">Pensums</th>
                            <th class="py-3 text-end px-4 text-secondary small fw-semibold text-uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($materias as $materia)
                            <tr style="border-bottom: 1px solid #f1f3f5;">
                                <td class="px-4 py-3">
                                    <span class="fw-bold text-dark font-monospace">{{ $materia->codigo_materia }}</span>
                                </td>
                                <td class="py-3">
                                    <div class="fw-medium text-dark">{{ $materia->nombre }}</div>
                                </td>
                                <td class="py-3 text-center">
                                    <span class="badge rounded-pill text-bg-light border">
                                        {{ $materia->unidades_valorativas }} UV
                                    </span>
                                </td>
                                <td class="py-3 text-center">
                                    <span class="badge rounded-pill {{ $materia->estado->badge() }}">
                                        {{ $materia->estado->label() }}
                                    </span>
                                </td>
                                <td class="py-3">
                                    @if ($materia->pensumMaterias->isEmpty())
                                        <span class="text-muted small fst-italic">Sin pensum asignado</span>
                                    @else
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach ($materia->pensumMaterias->take(3) as $pm)
                                                <span class="badge text-bg-light border small" title="{{ $pm->pensum->nombreCompleto() }}">
                                                    {{ $pm->pensum->carrera?->nombre ?? 'N/A' }} · C{{ $pm->ciclo_sugerido }}
                                                </span>
                                            @endforeach
                                            @if ($materia->pensumMaterias->count() > 3)
                                                <span class="badge text-bg-danger small">
                                                    +{{ $materia->pensumMaterias->count() - 3 }}
                                                </span>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td class="py-3 text-end px-4">
                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editMateriaModal-{{ $materia->materia_id }}"
                                            title="Editar">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <div class="text-center py-5">
                                        <div class="stat-icon-circle mx-auto mb-3" style="width: 72px; height: 72px; font-size: 2rem;">
                                            <i class="bi bi-journal-x"></i>
                                        </div>
                                        <h5 class="fw-bold text-dark mb-2">
                                            @if(request()->anyFilled(['search', 'estado', 'id_pensum', 'ciclo_sugerido']))
                                                No se encontraron materias con esos filtros
                                            @else
                                                No hay materias registradas
                                            @endif
                                        </h5>
                                        <p class="text-muted small mb-3">
                                            @if(request()->anyFilled(['estado', 'id_pensum']))
                                                Intente ajustar los filtros o limpiar la búsqueda.
                                            @else
                                                Comience agregando una nueva materia al catálogo.
                                            @endif
                                        </p>
                                        @if(request()->anyFilled(['estado', 'id_pensum']))
                                            <a href="{{ route('admin-academico.materias.materias') }}" class="btn btn-outline-danger btn-sm">
                                                <i class="bi bi-x-lg me-1"></i>Limpiar Filtros
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ============================================= --}}
    {{-- MODAL CREAR MATERIA --}}
    {{-- ============================================= --}}
    <div class="modal fade" id="createMateriaModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form action="{{ route('admin-academico.materias.store') }}" method="POST" id="createMateriaForm">
                    @csrf
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold">
                            <i class="bi bi-plus-circle-fill text-danger me-2"></i>Nueva Materia
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body pt-2">

                        <div class="row g-3 mb-3">
                            <div class="col-5">
                                <label for="create_codigo" class="form-label small fw-semibold">
                                    Código <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control text-uppercase font-monospace"
                                       id="create_codigo" name="codigo_materia"
                                       value="{{ old('codigo_materia') }}"
                                       maxlength="20" required
                                       placeholder="Ej: MAT-001"
                                       pattern="[A-Z0-9\-]{3,20}"
                                       oninput="this.value = this.value.toUpperCase()">
                                <div class="form-text small">Mayúsculas, números y guiones.</div>
                            </div>
                            <div class="col-7">
                                <label for="create_nombre" class="form-label small fw-semibold">
                                    Nombre <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control"
                                       id="create_nombre" name="nombre"
                                       value="{{ old('nombre') }}"
                                       maxlength="150" required
                                       placeholder="Ej: Matemática I">
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label for="create_uv" class="form-label small fw-semibold">
                                    Unidades Valorativas <span class="text-danger">*</span>
                                </label>
                                <input type="number" class="form-control"
                                       id="create_uv" name="unidades_valorativas"
                                       value="{{ old('unidades_valorativas', 4) }}"
                                       min="1" max="20" required>
                            </div>
                            <div class="col-6">
                                <label for="create_estado" class="form-label small fw-semibold">
                                    Estado <span class="text-danger">*</span>
                                </label>
                                <select name="estado" id="create_estado" class="form-select" required>
                                    @foreach ($estados as $estado)
                                        <option value="{{ $estado->value }}"
                                                @selected(old('estado', 'ACTIVO') === $estado->value)>
                                            {{ $estado->label() }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="alert alert-info small mb-0">
                            <i class="bi bi-info-circle me-1"></i>
                            El código y el nombre no pueden repetirse con otras materias existentes.
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-check-circle me-1"></i>Crear Materia
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ============================================= --}}
    {{-- MODALES EDITAR (uno por materia) --}}
    {{-- ============================================= --}}
    @foreach ($materias as $materia)
        <div class="modal fade" id="editMateriaModal-{{ $materia->materia_id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <form action="{{ route('admin-academico.materias.update', $materia) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-header border-0 pb-0">
                            <h5 class="modal-title fw-bold">
                                <i class="bi bi-pencil-square text-danger me-2"></i>Editar Materia
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body pt-2">

                            {{-- Info visual --}}
                            <div class="p-3 rounded-3 mb-3" style="background-color: var(--color-special);">
                                <div class="small text-secondary mb-1">ID interno</div>
                                <div class="fw-bold font-monospace">#{{ $materia->materia_id }}</div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-5">
                                    <label class="form-label small fw-semibold">
                                        Código <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control text-uppercase font-monospace"
                                           name="codigo_materia"
                                           value="{{ old('codigo_materia', $materia->codigo_materia) }}"
                                           maxlength="20" required
                                           pattern="[A-Z0-9\-]{3,20}"
                                           oninput="this.value = this.value.toUpperCase()">
                                </div>
                                <div class="col-7">
                                    <label class="form-label small fw-semibold">
                                        Nombre <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control"
                                           name="nombre"
                                           value="{{ old('nombre', $materia->nombre) }}"
                                           maxlength="150" required>
                                </div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-6">
                                    <label class="form-label small fw-semibold">
                                        Unidades Valorativas <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" class="form-control"
                                           name="unidades_valorativas"
                                           value="{{ old('unidades_valorativas', $materia->unidades_valorativas) }}"
                                           min="1" max="20" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-semibold">
                                        Estado <span class="text-danger">*</span>
                                    </label>
                                    <select name="estado" class="form-select" required>
                                        @foreach ($estados as $estado)
                                            <option value="{{ $estado->value }}"
                                                    @selected($materia->estado === $estado)>
                                                {{ $estado->label() }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Pensums asociados (solo lectura) --}}
                            @if ($materia->pensumMaterias->isNotEmpty())
                                <div class="mb-0">
                                    <label class="form-label small fw-semibold text-secondary">
                                        Pensums donde está incluida
                                    </label>
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach ($materia->pensumMaterias as $pm)
                                            <span class="badge text-bg-light border small">
                                                {{ $pm->pensum->carrera?->nombre ?? 'N/A' }} ·
                                                C{{ $pm->ciclo_sugerido }} ·
                                                {{ $pm->es_obligatoria ? 'Obligatoria' : 'Optativa' }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
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
    @endforeach
</div>
@endsection