@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">
                <i class="bi bi-diagram-3-fill text-danger me-2"></i>Gestión de Pensums
            </h1>
            <p class="text-muted mb-0 small">Planes de estudio por carrera.</p>
        </div>
        <button type="button" class="btn btn-danger d-flex align-items-center gap-2"
                data-bs-toggle="modal" data-bs-target="#createPensumModal">
            <i class="bi bi-plus-circle-fill"></i>Nuevo Pensum
        </button>
    </div>

    {{-- FILTROS --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('admin-academico.pensum.pensum') }}">
                <div class="d-flex flex-wrap align-items-end gap-3 justify-content-end">
                    
                    <div style="min-width: 160px;">
                        <label class="form-label small fw-semibold text-secondary mb-1">Estado</label>
                        <select name="estado" class="form-select form-select-sm">
                            <option value="">Todos</option>
                            @foreach ($estados as $estado)
                                <option value="{{ $estado->value }}" @selected(request('estado') === $estado->value)>
                                    {{ $estado->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div style="min-width: 240px;">
                        <label class="form-label small fw-semibold text-secondary mb-1">Facultad</label>
                        <select name="id_facultad" class="form-select form-select-sm">
                            <option value="">Todas</option>
                            @foreach ($facultades as $f)
                                <option value="{{ $f->facultad_id }}" @selected(request('id_facultad') == $f->facultad_id)>
                                    {{ $f->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-sm btn-danger">
                            <i class="bi bi-funnel-fill me-1"></i>Filtrar
                        </button>
                        <a href="{{ route('admin-academico.pensum.pensum') }}"
                           class="btn btn-sm btn-outline-secondary"><i class="bi bi-x-lg"></i></a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- TABLA --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div>
                <table class="table align-middle mb-0" style="font-size: 0.9rem;" id="pensum-table">
                    <thead style="background-color: #fafbfc; border-bottom: 2px solid #eef0f2;">
                        <tr>
                            <th class="px-4 py-3 text-secondary small fw-semibold text-uppercase">Pensum</th>
                            <th class="py-3 text-secondary small fw-semibold text-uppercase">Carrera</th>
                            <th class="py-3 text-secondary small fw-semibold text-uppercase">Facultad</th>
                            <th class="py-3 text-center text-secondary small fw-semibold text-uppercase">Año</th>
                            <th class="py-3 text-center text-secondary small fw-semibold text-uppercase">Materias</th>
                            <th class="py-3 text-center text-secondary small fw-semibold text-uppercase">Estado</th>
                            <th class="py-3 text-end px-4 text-secondary small fw-semibold text-uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pensums as $pensum)
                            <tr style="border-bottom: 1px solid #f1f3f5;">
                                <td class="px-4 py-3">
                                    <div class="fw-bold text-dark">{{ $pensum->nombre }}</div>
                                </td>
                                <td class="py-3">
                                    {{ $pensum->carrera?->nombre ?? '—' }}
                                </td>
                                <td class="py-3 text-muted small">
                                    {{ $pensum->carrera?->facultad?->nombre ?? '—' }}
                                </td>
                                <td class="py-3 text-center">
                                    <span class="badge text-bg-light border">{{ $pensum->anio_vigencia }}</span>
                                </td>
                                <td class="py-3 text-center">
                                    <span class="badge text-bg-light border">
                                        {{ $pensum->totalMaterias() }} · {{ $pensum->totalUV() }} UV
                                    </span>
                                </td>
                                <td class="py-3 text-center">
                                    <span class="badge rounded-pill {{ $pensum->estado->badge() }}">
                                        {{ $pensum->estado->label() }}
                                    </span>
                                </td>
                                <td class="py-3 text-end px-4">
                                    <div class="d-flex justify-content-end gap-1">
                                        <a href="{{ route('admin-academico.pensum.show', $pensum) }}"
                                           class="btn btn-sm btn-outline-secondary" title="Visualizar">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('admin-academico.pensum.edit', $pensum) }}"
                                           class="btn btn-sm btn-outline-danger" title="Modificar">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="text-center py-5">
                                        <div class="stat-icon-circle mx-auto mb-3" style="width: 72px; height: 72px; font-size: 2rem;">
                                            <i class="bi bi-diagram-3"></i>
                                        </div>
                                        <h5 class="fw-bold text-dark mb-2">No hay pensums registrados</h5>
                                        <p class="text-muted small mb-0">Comience creando el primer plan de estudio.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- MODAL CREAR --}}
    <div class="modal fade" id="createPensumModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form action="{{ route('admin-academico.pensum.store') }}" method="POST">
                    @csrf
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold">
                            <i class="bi bi-plus-circle-fill text-danger me-2"></i>Nuevo Pensum
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body pt-2">

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">
                                Nombre del Pensum <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" name="nombre"
                                   value="{{ old('nombre') }}"
                                   maxlength="100" required
                                   placeholder="Ej: Plan 2026 - Licenciatura en Informática">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">
                                Carrera <span class="text-danger">*</span>
                            </label>
                            <select name="id_carrera" class="form-select" required>
                                <option value="">Seleccione una carrera...</option>
                                @foreach ($carreras as $c)
                                    <option value="{{ $c->carrera_id }}" @selected(old('id_carrera') == $c->carrera_id)>
                                        {{ $c->nombre }} — {{ $c->facultad?->nombre }} ({{ $c->duracion_ciclos }} ciclos)
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-2">
                            <label class="form-label small fw-semibold">
                                Año de Vigencia <span class="text-danger">*</span>
                            </label>
                            <input type="number" class="form-control" name="anio_vigencia"
                                   value="{{ old('anio_vigencia', date('Y')) }}"
                                   min="2000" max="2100" required>
                        </div>

                        <div class="alert alert-info small mb-0">
                            <i class="bi bi-info-circle me-1"></i>
                            El pensum se creará en estado <strong>Modificado</strong>. A continuación podrá asignar materias y prerequisitos.
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-check-circle me-1"></i>Crear y continuar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection