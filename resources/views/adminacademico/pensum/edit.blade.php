@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">

    {{-- HEADER --}}
<a href="{{ route('admin-academico.pensum.pensum') }}" class="btn bg-danger-subtle text-danger border border-danger px-2 py-1 mb-4">
    <i class="bi bi-arrow-left me-1"></i>Volver al listado
</a>
<div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <!-- Información Principal y Navegación -->
        <div>
            <div class="d-flex align-items-center flex-wrap gap-2 mt-1 mb-1">
                <h1 class="h3 fw-bold text-dark mb-0">{{ $pensum->nombre }}</h1>
            </div>
            <p class="text-muted mb-0 small d-flex flex-wrap align-items-center">
                <span class="d-inline-flex align-items-center me-2">
                    <i class="bi bi-mortarboard me-1 text-danger"></i>{{ $pensum->carrera?->nombre }}
                </span>
                <span class="mx-1 text-secondary">·</span>
                <span class="d-inline-flex align-items-center mx-2">
                    <i class="bi bi-building me-1 text-danger"></i>{{ $pensum->carrera?->facultad?->nombre }}
                </span>
                <span class="mx-1 text-secondary">·</span>
                <span class="d-inline-flex align-items-center ms-2">
                    <i class="bi bi-calendar-event me-1 text-danger"></i>Vigencia: {{ $pensum->anio_vigencia }}
                </span>
            </p>
        </div>

        <!-- Acciones de Estado y Badge -->
        <div class="d-flex align-items-center flex-wrap gap-3">
            <!-- Badge de Estado Actual -->
            <div>
                <span class="badge rounded-pill {{ $pensum->estado->badge() }} px-3 py-2 fs-6 shadow-sm">
                    {{ $pensum->estado->label() }}
                </span>
            </div>

            <!-- Formulario de Cambio de Estado Condicional -->
            <div>
                @if ($pensum->estado === \App\Enums\EstadoPensum::MODIFICADO)
                    <form action="{{ route('admin-academico.pensum.changeEstado', $pensum) }}" method="POST" class="m-0">
                        @csrf 
                        @method('PATCH')
                        <input type="hidden" name="nuevo_estado" value="ACTIVO">
                        <button type="submit" class="btn btn-success btn-sm px-3 py-2 fw-semibold shadow-sm d-inline-flex align-items-center"
                                onclick="return confirm('¿Está seguro de activar este pensum? Al hacerlo, se establecerá como vigente y se desactivará el pensum activo anterior de la carrera.')">
                            <i class="bi bi-check-circle me-1"></i>Activar Pensum
                        </button>
                    </form>
                @elseif ($pensum->estado === \App\Enums\EstadoPensum::ACTIVO)
                    <form action="{{ route('admin-academico.pensum.changeEstado', $pensum) }}" method="POST" class="m-0">
                        @csrf 
                        @method('PATCH')
                        <input type="hidden" name="nuevo_estado" value="INACTIVO">
                        <button type="submit" class="btn btn-outline-secondary btn-sm px-3 py-2 fw-semibold d-inline-flex align-items-center"
                                onclick="return confirm('¿Está seguro de desactivar este pensum? Quedará en modo histórico.')">
                            <i class="bi bi-pause-circle me-1"></i>Desactivar
                        </button>
                    </form>
                @elseif ($pensum->estado === \App\Enums\EstadoPensum::INACTIVO)
                    <form action="{{ route('admin-academico.pensum.changeEstado', $pensum) }}" method="POST" class="m-0">
                        @csrf 
                        @method('PATCH')
                        <input type="hidden" name="nuevo_estado" value="MODIFICADO">
                        <button type="submit" class="btn btn-outline-warning btn-sm px-3 py-2 fw-semibold text-dark d-inline-flex align-items-center"
                                onclick="return confirm('¿Desea reabrir la edición de este pensum para realizar modificaciones?')">
                            <i class="bi bi-pencil-square me-1"></i>Reabrir Edición
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>

    @if ($pensum->estado !== \App\Enums\EstadoPensum::MODIFICADO)
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle-fill me-1"></i>
            Este pensum no está en estado <strong>MODIFICADO</strong>. Para editarlo, primero reábralo.
        </div>
    @endif

    {{-- TABS DE CICLOS --}}
    <ul class="nav nav-pills mb-3 gap-2 flex-wrap" role="tablist">
        @for ($c = 1; $c <= $duracionCiclos; $c++)
            @php
                $materiasCiclo = $pensum->pensumMaterias->where('ciclo_sugerido', $c);
                $cantidad = $materiasCiclo->count();
                $color = $cantidad >= 4 && $cantidad <= 5 ? 'success' : ($cantidad === 0 ? 'secondary' : 'warning');
            @endphp
            <li class="nav-item">
                <button class="nav-link {{ $c === 1 ? 'active' : '' }}"
                        data-bs-toggle="pill" data-bs-target="#ciclo-{{ $c }}" type="button">
                    Ciclo {{ $c }}
                    <span class="badge text-bg-{{ $color }} ms-1">{{ $cantidad }}</span>
                </button>
            </li>
        @endfor
    </ul>

    <div class="tab-content">
        @for ($c = 1; $c <= $duracionCiclos; $c++)
            @php $materiasCiclo = $pensum->pensumMaterias->where('ciclo_sugerido', $c); @endphp
            <div class="tab-pane fade {{ $c === 1 ? 'show active' : '' }}" id="ciclo-{{ $c }}">

                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <div>
                            <strong>Ciclo {{ $c }}</strong>
                            <span class="text-muted small ms-2">
                                {{ $materiasCiclo->count() }} / 5 materias
                            </span>
                        </div>
                        @if ($pensum->estado === \App\Enums\EstadoPensum::MODIFICADO && $materiasCiclo->count() < 5)
                            <button type="button" class="btn btn-sm btn-danger"
                                    data-bs-toggle="modal" data-bs-target="#addMateriaModal-{{ $c }}">
                                <i class="bi bi-plus-circle me-1"></i>Agregar Materia
                            </button>
                        @endif
                    </div>
                    <div class="card-body p-0">
                        @if ($materiasCiclo->isEmpty())
                            <div class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                Sin materias asignadas
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table align-middle mb-0">
                                    <thead style="background-color: #fafbfc;">
                                        <tr>
                                            <th class="px-4">Código</th>
                                            <th>Materia</th>
                                            <th class="text-center">UV</th>
                                            <th>Tipo</th>
                                            <th>Prerequisitos</th>
                                            <th class="text-end px-4">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($materiasCiclo as $pm)
                                            <tr>
                                                <td class="px-4 font-monospace fw-bold small">{{ $pm->materia->codigo_materia }}</td>
                                                <td>{{ $pm->materia->nombre }}</td>
                                                <td class="text-center">{{ $pm->materia->unidades_valorativas }}</td>
                                                <td>
                                                    <span class="badge {{ $pm->es_obligatoria ? 'text-bg-light border' : 'text-bg-info' }}">
                                                        {{ $pm->es_obligatoria ? 'Obligatoria' : 'Optativa' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @forelse ($pm->prerequisitos as $pr)
                                                        <span class="badge text-bg-light border small me-1">
                                                            {{ $pr->materiaRequisito?->codigo_materia }}
                                                            <form action="{{ route('admin-academico.pensum.removePrerequisito', $pr) }}"
                                                                  method="POST" class="d-inline">
                                                                @csrf @method('DELETE')
                                                                <button type="submit" class="btn-close btn-close-sm ms-1"
                                                                        style="font-size: 0.5rem;"
                                                                        onclick="return confirm('¿Quitar prerequisito?')"></button>
                                                            </form>
                                                        </span>
                                                    @empty
                                                        <span class="text-muted small fst-italic">Sin prerequisitos</span>
                                                    @endforelse
                                                </td>
                                                <td class="text-end px-4">
                                                    @if ($pensum->estado === \App\Enums\EstadoPensum::MODIFICADO)
                                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#addPrereqModal-{{ $pm->pensum_materia_id }}"
                                                                title="Agregar prerequisito">
                                                            <i class="bi bi-link-45deg"></i>
                                                        </button>
                                                        <form action="{{ route('admin-academico.pensum.removeMateria', $pm) }}"
                                                              method="POST" class="d-inline">
                                                            @csrf @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-outline-danger"
                                                                    onclick="return confirm('¿Eliminar esta materia del pensum?')"
                                                                    title="Eliminar">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- MODAL: AGREGAR MATERIA A ESTE CICLO --}}
                <div class="modal fade" id="addMateriaModal-{{ $c }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 shadow">
                            <form action="{{ route('admin-academico.pensum.addMateria', $pensum) }}" method="POST">
                                @csrf
                                <input type="hidden" name="ciclo_sugerido" value="{{ $c }}">
                                <div class="modal-header border-0 pb-0">
                                    <h5 class="modal-title fw-bold">
                                        <i class="bi bi-plus-circle-fill text-danger me-2"></i>
                                        Agregar Materia · Ciclo {{ $c }}
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body pt-2">
                                    <div class="mb-3">
                                        <label class="form-label small fw-semibold">
                                            Materia <span class="text-danger">*</span>
                                        </label>
                                        <select name="id_materia" class="form-select" required>
                                            <option value="">Seleccione...</option>
                                            @foreach ($catalogoMaterias as $m)
                                                @php
                                                    $yaEnPensum = $pensum->pensumMaterias->contains('id_materia', $m->materia_id);
                                                @endphp
                                                <option value="{{ $m->materia_id }}"
                                                        @disabled($yaEnPensum)>
                                                    {{ $m->codigo_materia }} — {{ $m->nombre }} ({{ $m->unidades_valorativas }} UV)
                                                    {{ $yaEnPensum ? '· YA EN PENSUM' : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-check mb-0">
                                        <input class="form-check-input" type="checkbox"
                                               name="es_obligatoria" value="1" id="oblig-{{ $c }}" checked>
                                        <label class="form-check-label small" for="oblig-{{ $c }}">
                                            Marcar como materia obligatoria
                                        </label>
                                    </div>
                                </div>
                                <div class="modal-footer border-0 pt-0">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-danger">
                                        <i class="bi bi-check-circle me-1"></i>Agregar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- MODALES DE PREREQUISITO (uno por cada materia del ciclo) --}}
                @foreach ($materiasCiclo as $pm)
                    <div class="modal fade" id="addPrereqModal-{{ $pm->pensum_materia_id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow">
                                <form action="{{ route('admin-academico.pensum.addPrerequisito', $pm) }}" method="POST">
                                    @csrf
                                    <div class="modal-header border-0 pb-0">
                                        <h5 class="modal-title fw-bold">
                                            <i class="bi bi-link-45deg text-danger me-2"></i>
                                            Prerequisito para: {{ $pm->materia->codigo_materia }}
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body pt-2">
                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold">
                                                Materia Requisito <span class="text-danger">*</span>
                                            </label>
                                            <select name="id_materia_requisito" class="form-select" required>
                                                <option value="">Seleccione...</option>
                                                @foreach ($pensum->pensumMaterias as $otra)
                                                    @if ($otra->id_materia !== $pm->id_materia)
                                                        <option value="{{ $otra->id_materia }}">
                                                            {{ $otra->materia->codigo_materia }} — {{ $otra->materia->nombre }}
                                                            (Ciclo {{ $otra->ciclo_sugerido }})
                                                        </option>
                                                    @endif
                                                @endforeach
                                            </select>
                                            <div class="form-text small">
                                                Solo materias que ya están en este pensum.
                                            </div>
                                        </div>
                                        <div class="mb-0">
                                            <label class="form-label small fw-semibold">
                                                UV mínimas requeridas <span class="text-muted">(opcional)</span>
                                            </label>
                                            <input type="number" name="uv_minimas_requeridas"
                                                   class="form-control" min="0" max="500" value="0">
                                        </div>
                                    </div>
                                    <div class="modal-footer border-0 pt-0">
                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                                        <button type="submit" class="btn btn-danger">
                                            <i class="bi bi-check-circle me-1"></i>Agregar Prerequisito
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endfor
    </div>

    {{-- RESUMEN INFERIOR --}}
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <strong class="text-dark">Total:</strong>
                    {{ $pensum->totalMaterias() }} materias ·
                    {{ $pensum->totalUV() }} UV ·
                    {{ count($pensum->ciclosUsados()) }} / {{ $duracionCiclos }} ciclos
                </div>
                <a href="{{ route('admin-academico.pensum.show', $pensum) }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-eye me-1"></i>Ver malla completa
                </a>
            </div>
        </div>
    </div>
</div>
@endsection