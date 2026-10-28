@extends('layouts.app')

@section('title', 'Horarios')

@section('content')

<div class="container-fluid py-4">

    {{-- Header --}}
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-md-between gap-3 p-3 mb-4 rounded-4 card">
        <div class="d-flex align-items-center">
            <div class="px-3 py-2 rounded-3 bg-danger bg-opacity-10" style="width:48px;height:48px;">
                <i class="bi bi-building-gear fs-4 text-danger"></i>
            </div>
            <div class="px-3">
                <h1 class="h4 fw-bold mb-0">GESTION DE HORARIOS Y GRUPOS</h1>
                <p class="text-muted mb-0 small">Gestión de edificios y aulas</p>
            </div>
        </div>
        <a href="{{ route('coordinador-facultad.infraestructura-horarios.index') }}"
           class="btn bg-danger-subtle text-danger border border-danger px-3 py-2">
            <i class="fa-solid fa-home me-1"></i> Infraestructura
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
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-horarios">
                <i class="bi bi-clock-history me-1"></i> Horarios
                <span class="badge bg-secondary ms-1">{{ $horarios->count() }}</span>
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-grupos">
                <i class="bi bi-people me-1"></i> Grupos-horarios
                <span class="badge bg-secondary ms-1">{{ $grupos->count() }}</span>
            </button>
        </li>
    </ul>

    <div class="tab-content">

        {{-- ============================================================
             TAB HORARIOS
             ============================================================ --}}
        <div class="tab-pane fade show active" id="tab-horarios">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h6 fw-bold mb-0">
                    <i class="bi bi-clock-history text-danger me-1"></i> Bloques horarios
                </h2>
                <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalCrearHorario">
                    <i class="bi bi-plus-circle me-1"></i> Nuevo bloque
                </button>
            </div>

            <div class="alert alert-info small border-0">
                <i class="bi bi-info-circle-fill me-1"></i>
                Los bloques horarios se reutilizan entre secciones. Un mismo bloque (ej. "Lun 06:10-07:50")
                puede estar asignado a varias secciones con distintas aulas.
            </div>

            <div class="card border-0 shadow-sm">
                <div>
                    <table class="table table-hover align-middle mb-0" id="horariotable">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Ciclo</th>
                                <th>Día</th>
                                <th>Hora inicio</th>
                                <th>Hora fin</th>
                                <th>Duración</th>
                                <th>Jornada</th>
                                <th class="text-end pe-4">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($horarios as $horario)
                                <tr>
                                    <td class="ps-4 small">{{ $horario->cicloLectivo->nombre_ciclo ?? '—' }}</td>
                                    <td>
                                        <span class="badge text-bg-light border">
                                            {{ $horario->dia->label() }}
                                        </span>
                                    </td>
                                    <td class="font-monospace">{{ substr($horario->hora_inicio, 0, 5) }}</td>
                                    <td class="font-monospace">{{ substr($horario->hora_fin, 0, 5) }}</td>
                                    <td>
                                        <small class="text-muted">
                                            {{ $horario->duracion_minutos }} min
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge {{ $horario->jornada->badge() }}">
                                            {{ $horario->jornada->label() }}
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-sm btn-outline-primary me-1"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalEditarHorario{{ $horario->horario_id }}">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalEliminarHorario{{ $horario->horario_id }}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-5">
                                        <i class="bi bi-clock fs-2 d-block mb-2"></i>
                                        No hay bloques horarios registrados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ============================================================
             TAB GRUPOS
             ============================================================ --}}
        <div class="tab-pane fade" id="tab-grupos">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h6 fw-bold mb-0">
                    <i class="bi bi-people text-danger me-1"></i> Grupos por ciclo
                </h2>
                <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalCrearGrupo">
                    <i class="bi bi-plus-circle me-1"></i> Nuevo grupo
                </button>
            </div>

            <div class="alert alert-info small border-0">
                <i class="bi bi-info-circle-fill me-1"></i>
                Un <strong>grupo</strong> agrupa los bloques horarios que cursa un conjunto fijo de estudiantes
                (ej: Grupo A cursa Lunes y Martes 06:10-07:50). Las secciones se asignan al grupo y heredan sus bloques.
            </div>

            @forelse($grupos->groupBy('id_ciclo_lectivo') as $cicloId => $gruposCiclo)
                @php
                    $ciclo = $gruposCiclo->first()->cicloLectivo;
                @endphp
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-0">
                        <div>
                            <table class="table table-hover align-middle mb-0" id="grupohorariotable">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">Grupo</th>
                                        <th>Bloques horarios</th>
                                        <th>Secciones</th>
                                        <th>Estado</th>
                                        <th class="text-end pe-4">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($gruposCiclo as $grupo)
                                        <tr>
                                            <td class="ps-4">
                                                <span class="badge bg-danger fs-6">Grupo {{ $grupo->nombre }}</span>
                                            </td>
                                            <td>
                                                @if($grupo->horarios->isNotEmpty())
                                                    <div class="d-flex flex-wrap gap-1">
                                                        @foreach($grupo->horarios->sortBy(fn($h) => [$h->dia->isoNumber(), $h->hora_inicio]) as $h)
                                                            <span class="badge text-bg-light border font-monospace">
                                                                {{ $h->dia->short() }} {{ substr($h->hora_inicio, 0, 5) }}-{{ substr($h->hora_fin, 0, 5) }}
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <span class="text-muted small">Sin bloques asignados</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary-subtle text-secondary border">
                                                    {{ $grupo->secciones->count() }} sección(es)
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge {{ $grupo->activo ? 'text-bg-success' : 'text-bg-secondary' }}">
                                                    {{ $grupo->activo ? 'Activo' : 'Inactivo' }}
                                                </span>
                                            </td>
                                            <td class="text-end pe-4">
                                                <button class="btn btn-sm btn-outline-primary me-1"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#modalEditarGrupo{{ $grupo->grupo_id }}">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#modalEliminarGrupo{{ $grupo->grupo_id }}">
                                                    <i class="bi bi-trash"></i>
                                                </button>
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
                        <i class="bi bi-people fs-1 text-muted d-block mb-3"></i>
                        <p class="text-muted mb-0">No hay grupos registrados.</p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</div>

{{-- ================================================================
     MODAL: CREAR HORARIO
     ================================================================ --}}
<div class="modal fade" id="modalCrearHorario" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-white border-bottom">
                <h5 class="modal-title fw-bold">Nuevo Bloque Horario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('coordinador-facultad.horarios.store') }}" method="POST" id="formCrearHorario">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-medium">
                            Ciclo lectivo <span class="text-danger">*</span>
                        </label>
                        <select name="id_ciclo_lectivo" class="form-select" required>
                            <option value="">Seleccione...</option>
                            @foreach($ciclosLectivos as $c)
                                <option value="{{ $c->ciclo_lectivo_id }}">{{ $c->nombre_ciclo }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-medium">
                            Día <span class="text-danger">*</span>
                        </label>
                        <select name="dia" class="form-select" required>
                            <option value="">Seleccione...</option>
                            @foreach($dias as $d)
                                <option value="{{ $d->value }}">{{ $d->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-medium">
                                Hora inicio <span class="text-danger">*</span>
                            </label>
                            <input type="time" name="hora_inicio" class="form-control"
                                   min="06:00" max="20:30" required>
                            <div class="form-text small">Entre 06:00 y 20:30</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-medium">
                                Hora fin <span class="text-danger">*</span>
                            </label>
                            <input type="time" name="hora_fin" class="form-control"
                                   min="06:00" max="20:30" required>
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="form-label small fw-medium">
                            Jornada <span class="text-danger">*</span>
                        </label>
                        <select name="jornada" class="form-select" required>
                            @foreach($jornadas as $j)
                                <option value="{{ $j->value }}">{{ $j->label() }}</option>
                            @endforeach
                        </select>
                        <div class="form-text small">Se puede autocompletar según la hora de inicio.</div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-check-circle me-1"></i> Crear bloque
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ================================================================
     MODAL: CREAR GRUPO
     ================================================================ --}}
<div class="modal fade" id="modalCrearGrupo" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-white border-bottom">
                <h5 class="modal-title fw-bold">Nuevo Grupo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('coordinador-facultad.grupos.store') }}" method="POST"
                  x-data="grupoForm()">
                @csrf
                <div class="modal-body p-4">

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-medium">
                                Ciclo lectivo <span class="text-danger">*</span>
                            </label>
                            <select name="id_ciclo_lectivo" class="form-select"
                                    x-model="cicloId" @change="filtrarHorarios()" required>
                                <option value="">Seleccione...</option>
                                @foreach($ciclosLectivos as $c)
                                    <option value="{{ $c->ciclo_lectivo_id }}">{{ $c->nombre_ciclo }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-medium">
                                Nombre del grupo <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="nombre" class="form-control"
                                   placeholder="Ej: A, B, K, Ñ" maxlength="20" required>
                            <div class="form-text small">Solo letras, números y guiones.</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-medium">
                            Bloques horarios del grupo
                        </label>
                        <div class="alert alert-info small mb-2">
                            <i class="bi bi-info-circle me-1"></i>
                            Seleccioná los bloques que cursa este grupo. Se muestran los disponibles del ciclo elegido.
                        </div>

                        <div x-show="!cicloId" class="text-muted small">
                            <i class="bi bi-arrow-up me-1"></i> Elegí un ciclo para ver los bloques disponibles.
                        </div>

                        <div x-show="cicloId && horariosFiltrados.length === 0" class="text-muted small">
                            No hay bloques horarios creados en este ciclo.
                        </div>

                        <div x-show="cicloId" class="border rounded-3 p-3"
                             style="max-height: 260px; overflow-y:auto;">
                            <template x-for="h in horariosFiltrados" :key="h.horario_id">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox"
                                           :id="'horario-' + h.horario_id"
                                           name="horarios[]"
                                           :value="h.horario_id">
                                    <label class="form-check-label small font-monospace"
                                           :for="'horario-' + h.horario_id"
                                           x-text="h.etiqueta"></label>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="form-check form-switch">
                        <input type="hidden" name="activo" value="0">
                        <input class="form-check-input" type="checkbox" name="activo" value="1"
                               id="grupoActivoCrear" checked>
                        <label class="form-check-label small" for="grupoActivoCrear">
                            Grupo activo
                        </label>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-check-circle me-1"></i> Crear grupo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ================================================================
     MODALES EDITAR / ELIMINAR: HORARIOS
     ================================================================ --}}
@foreach($horarios as $horario)
    <div class="modal fade" id="modalEditarHorario{{ $horario->horario_id }}" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-white border-bottom">
                    <h5 class="modal-title fw-bold">
                        Editar: {{ $horario->dia->label() }} {{ substr($horario->hora_inicio, 0, 5) }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('coordinador-facultad.horarios.update', $horario) }}" method="POST">
                    @csrf @method('PUT')
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label small fw-medium">Ciclo lectivo *</label>
                            <select name="id_ciclo_lectivo" class="form-select" required>
                                @foreach($ciclosLectivos as $c)
                                    <option value="{{ $c->ciclo_lectivo_id }}"
                                        {{ $horario->id_ciclo_lectivo == $c->ciclo_lectivo_id ? 'selected' : '' }}>
                                        {{ $c->nombre_ciclo }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-medium">Día *</label>
                            <select name="dia" class="form-select" required>
                                @foreach($dias as $d)
                                    <option value="{{ $d->value }}"
                                        {{ $horario->dia->value === $d->value ? 'selected' : '' }}>
                                        {{ $d->label() }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-medium">Hora inicio *</label>
                                <input type="time" name="hora_inicio" class="form-control"
                                       value="{{ substr($horario->hora_inicio, 0, 5) }}"
                                       min="06:00" max="20:30" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-medium">Hora fin *</label>
                                <input type="time" name="hora_fin" class="form-control"
                                       value="{{ substr($horario->hora_fin, 0, 5) }}"
                                       min="06:00" max="20:30" required>
                            </div>
                        </div>
                        <div class="mt-3">
                            <label class="form-label small fw-medium">Jornada *</label>
                            <select name="jornada" class="form-select" required>
                                @foreach($jornadas as $j)
                                    <option value="{{ $j->value }}"
                                        {{ $horario->jornada->value === $j->value ? 'selected' : '' }}>
                                        {{ $j->label() }}
                                    </option>
                                @endforeach
                            </select>
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

    <div class="modal fade" id="modalEliminarHorario{{ $horario->horario_id }}" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-danger text-white border-0">
                    <h5 class="modal-title fw-bold">Eliminar bloque horario</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="mb-3">
                        ¿Confirmás eliminar el bloque
                        <strong>{{ $horario->dia->label() }} {{ substr($horario->hora_inicio, 0, 5) }}-{{ substr($horario->hora_fin, 0, 5) }}</strong>?
                    </p>
                    <div class="alert alert-warning small mb-0">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i>
                        Solo se puede eliminar si no está asignado a ninguna sección ni grupo.
                    </div>
                </div>
                <div class="modal-footer bg-light border-top-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form action="{{ route('coordinador-facultad.horarios.destroy', $horario) }}" method="POST">
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
     MODALES EDITAR / ELIMINAR: GRUPOS
     ================================================================ --}}
@foreach($grupos as $grupo)
    <div class="modal fade" id="modalEditarGrupo{{ $grupo->grupo_id }}" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-white border-bottom">
                    <h5 class="modal-title fw-bold">Editar Grupo {{ $grupo->nombre }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('coordinador-facultad.grupos.update', $grupo) }}" method="POST"
                      x-data="grupoFormEditar({{ $grupo->id_ciclo_lectivo }}, {{ $grupo->horarios->pluck('horario_id')->toJson() }})">
                    @csrf @method('PUT')
                    <div class="modal-body p-4">

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-medium">Ciclo lectivo *</label>
                                <select name="id_ciclo_lectivo" class="form-select"
                                        x-model="cicloId" @change="filtrarHorarios()" required
                                        {{ $grupo->secciones->isNotEmpty() ? 'disabled' : '' }}>
                                    @foreach($ciclosLectivos as $c)
                                        <option value="{{ $c->ciclo_lectivo_id }}"
                                            {{ $grupo->id_ciclo_lectivo == $c->ciclo_lectivo_id ? 'selected' : '' }}>
                                            {{ $c->nombre_ciclo }}
                                        </option>
                                    @endforeach
                                </select>

                                @if($grupo->secciones->isNotEmpty())
                                    {{-- Select disabled NO envía el valor. Hidden estático para asegurarlo. --}}
                                    <input type="hidden" name="id_ciclo_lectivo" value="{{ $grupo->id_ciclo_lectivo }}">
                                    <div class="form-text small text-warning">
                                        <i class="bi bi-lock-fill me-1"></i>
                                        No se puede cambiar el ciclo: el grupo tiene secciones.
                                    </div>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-medium">Nombre *</label>
                                <input type="text" name="nombre" class="form-control"
                                       value="{{ $grupo->nombre }}" maxlength="20" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-medium">Bloques horarios del grupo</label>

                            <div class="border rounded-3 p-3" style="max-height: 260px; overflow-y:auto;">
                                <template x-for="h in horariosFiltrados" :key="h.horario_id">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox"
                                               :id="'edit-horario-{{ $grupo->grupo_id }}-' + h.horario_id"
                                               name="horarios[]"
                                               :value="h.horario_id"
                                               :checked="seleccionados.includes(h.horario_id)"
                                               @change="toggle(h.horario_id)">
                                        <label class="form-check-label small font-monospace"
                                               :for="'edit-horario-{{ $grupo->grupo_id }}-' + h.horario_id"
                                               x-text="h.etiqueta"></label>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div class="form-check form-switch">
                            <input type="hidden" name="activo" value="0">
                            <input class="form-check-input" type="checkbox" name="activo" value="1"
                                   id="grupoActivo{{ $grupo->grupo_id }}"
                                   {{ $grupo->activo ? 'checked' : '' }}>
                            <label class="form-check-label small" for="grupoActivo{{ $grupo->grupo_id }}">
                                Grupo activo
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

    {{-- Modal eliminar grupo --}}
    <div class="modal fade" id="modalEliminarGrupo{{ $grupo->grupo_id }}" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-danger text-white border-0">
                    <h5 class="modal-title fw-bold">Eliminar grupo</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="mb-3">
                        ¿Confirmás eliminar el grupo <strong>{{ $grupo->nombre }}</strong>?
                    </p>
                    @if($grupo->secciones->isNotEmpty())
                        <div class="alert alert-danger small mb-0">
                            <i class="bi bi-x-circle-fill me-1"></i>
                            Este grupo tiene <strong>{{ $grupo->secciones->count() }} sección(es)</strong> asignada(s).
                            No se puede eliminar hasta que las reasignes o elimines.
                        </div>
                    @else
                        <div class="alert alert-warning small mb-0">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i>
                            Se eliminarán también sus {{ $grupo->horarios->count() }} bloque(s) horario(s).
                        </div>
                    @endif
                </div>
                <div class="modal-footer bg-light border-top-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form action="{{ route('coordinador-facultad.grupos.destroy', $grupo) }}" method="POST">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger"
                                {{ $grupo->secciones->isNotEmpty() ? 'disabled' : '' }}>
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
/* ============================================================
   1. DATOS DE HORARIOS POR CICLO
   ============================================================ */
@php
    $horariosJson = $horariosPorCiclo->map(fn($items) => $items->map(fn($h) => [
        'horario_id'  => $h->horario_id,
        'etiqueta'    => $h->etiqueta,
        'dia'         => $h->dia->value,
        'hora_inicio' => $h->hora_inicio,
    ])->values());
@endphp

window.HORARIOS_POR_CICLO = @json($horariosJson);

/* ============================================================
   2. COMPONENTES ALPINE — registrados ANTES de que Alpine
      inicialice el DOM (evento alpine:init)
   ============================================================ */
document.addEventListener('alpine:init', () => {
    Alpine.data('grupoForm', () => ({
        cicloId: '',
        horariosFiltrados: [],
        filtrarHorarios() {
            this.horariosFiltrados = window.HORARIOS_POR_CICLO[this.cicloId] || [];
        }
    }));

    Alpine.data('grupoFormEditar', (cicloInicial, seleccionados) => ({
        cicloId: String(cicloInicial),
        seleccionados: (seleccionados || []).map(Number),
        horariosFiltrados: [],
        init() {
            this.filtrarHorarios();
        },
        filtrarHorarios() {
            this.horariosFiltrados = window.HORARIOS_POR_CICLO[this.cicloId] || [];
        },
        toggle(id) {
            id = Number(id);
            const i = this.seleccionados.indexOf(id);
            if (i > -1) this.seleccionados.splice(i, 1);
            else this.seleccionados.push(id);
        }
    }));
});

/* ============================================================
   3. LÓGICA GENERAL DE LA VISTA
   ============================================================ */
document.addEventListener('DOMContentLoaded', function () {

    // ---- Autocompletar jornada según hora de inicio ----
    const formCrearHorario = document.getElementById('formCrearHorario');
    if (formCrearHorario) {
        const hi            = formCrearHorario.querySelector('input[name="hora_inicio"]');
        const jornadaSelect = formCrearHorario.querySelector('select[name="jornada"]');
        hi?.addEventListener('change', () => {
            const hora = parseInt((hi.value || '00:00').split(':')[0], 10);
            let j = 'MATUTINO';
            if (hora >= 12 && hora < 18) j = 'VESPERTINO';
            else if (hora >= 18) j = 'NOCTURNO';
            jornadaSelect.value = j;
        });
    }

    // ---- Persistencia del tab activo (hash + localStorage) ----
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

    // ---- Guard de modales: evitar error si el modal no existe ----
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