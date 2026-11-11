@extends('layouts.app')

@section('title', 'Secciones')

@section('content')
<div class="container-fluid py-4">

    {{-- Header --}}
    <div class="bg-white border border-light-subtle rounded-3 p-3 p-md-4 mb-4">
        <div class="d-flex flex-column flex-xl-row align-items-xl-center justify-content-between gap-3">
            
            {{-- Título y Contexto Académico --}}
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="badge bg-light text-secondary border border-light-subtle fw-medium px-2 py-1">
                        Coordinación de Facultad
                    </span>
                </div>
                <h1 class="h4 fw-bold text-dark mb-1 tracking-tight">GESTIÓN DE SECCIONES</h1>
                <p class="text-secondary mb-0 small">
                    Planificación de cátedras, distribución docente, aforos y asignación de aulas.
                </p>
            </div>

            {{-- Barra Operativa de Filtro y Acción --}}
            <div class="d-flex flex-wrap align-items-center gap-2 pt-2 pt-xl-0">
                
                {{-- Filtro de Ciclo Lectivo --}}
                <form method="GET" action="{{ route('coordinador-facultad.secciones.index') }}" class="d-flex align-items-center">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-light-subtle text-secondary">
                            <i class="bi bi-calendar3" aria-hidden="true"></i>
                        </span>
                        <select name="id_ciclo_lectivo" 
                                id="filtro-ciclo" 
                                class="form-select border-light-subtle text-dark" 
                                onchange="this.form.submit()"
                                aria-label="Filtrar por ciclo lectivo">
                            <option value="">Seleccione un ciclo</option>
                            @foreach($ciclosLectivos as $c)
                                @php
                                    // FIX SEGURO: Validación de Enum a String
                                    $estado = is_object($c->estado) ? ($c->estado->value ?? $c->estado->name) : $c->estado;
                                @endphp
                                <option value="{{ $c->ciclo_lectivo_id }}" {{ (string) $idCicloFiltro === (string) $c->ciclo_lectivo_id ? 'selected' : '' }}>
                                    {{ $c->nombre_ciclo }}
                                    @switch($estado)
                                        @case('MATRICULA_ABIERTA')
                                            &bull; Matrícula Abierta
                                            @break
                                        @case('ACTIVO')
                                            &bull; Ciclo Activo
                                            @break
                                        @case('CONFIGURACION')
                                            &bull; En Planificación
                                            @break
                                    @endswitch
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>

                <div class="vr d-none d-sm-block text-secondary opacity-25 my-1"></div>

                {{-- Botón de Enlace a Horarios --}}
                <a href="{{ route('coordinador-facultad.horarios-grupohorario.index') }}" 
                class="btn btn-sm btn-danger d-inline-flex align-items-center gap-2 px-3">
                    <i class="bi bi-calendar-week" aria-hidden="true"></i>
                    <span>Matriz de Horarios</span>
                </a>

            </div>

        </div>
    </div>

    {{-- Botón crear --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h6 fw-bold mb-0">
            <i class="bi bi-list-ul text-danger me-1"></i>
            SECCIONES REGISTRADAS
            <span class="badge bg-secondary ms-1">{{ $secciones->count() }}</span>
        </h2>
        <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalCrearSeccion">
            <i class="bi bi-plus-circle me-1"></i> Nueva sección
        </button>
    </div>

    {{-- Tabla de secciones --}}
    <div class="card border-0 shadow-sm">
        <div class="text-medium">
            <table class="table table-hover align-middle mb-0" id="tabla-seccion">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Ciclo</th>
                        <th>Materia</th>
                        <th>Grupo</th>
                        <th>Docente</th>
                        <th>Cupos</th>
                        <th>Horarios</th>
                        <th>Estado</th>
                        <th class="text-end pe-4">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($secciones as $seccion)
                        <tr>
                            <td class="ps-4 small">{{ $seccion->cicloLectivo->nombre_ciclo ?? '—' }}</td>
                            <td>
                                <div class="fw-semibold small">{{ $seccion->materia->nombre ?? '—' }}</div>
                                <small class="text-muted">{{ $seccion->materia->codigo_materia ?? '' }}</small>
                            </td>
                            <td>
                                <span class="badge bg-danger">Grupo {{ $seccion->nombre_grupo }}</span>
                            </td>
                            <td class="small">
                                {{ $seccion->docente->user->name ?? '—' }}
                                <small class="text-muted d-block">{{ $seccion->docente->codigo_empleado ?? '' }}</small>
                            </td>
                            <td>
                                <div class="small">
                                    <strong>{{ $seccion->cupo_maximo }}</strong> base
                                    @if($seccion->permite_sobrecupo)
                                        <span class="text-warning">+{{ $seccion->cupo_extra_maximo }} extra</span>
                                    @endif
                                </div>

                                @php 
                                    $fase = is_object($seccion->fase_ciclo) ? ($seccion->fase_ciclo->value ?? $seccion->fase_ciclo->name) : $seccion->fase_ciclo; 
                                @endphp

                                @if($fase === 'CONFIGURACION')
                                    <small class="text-muted d-block">
                                        <i class="bi bi-gear me-1"></i> En planificación
                                    </small>
                                @elseif(in_array($fase, ['MATRICULA_ABIERTA', 'ACTIVO'], true))
                                    <small class="text-primary d-block">
                                        <i class="bi bi-people-fill me-1"></i>
                                        {{ $seccion->inscritos_count }} inscritos
                                    </small>
                                    <small class="text-muted d-block">
                                        {{ $seccion->cupo_disponible }} disponibles
                                    </small>
                                @elseif($fase === 'CERRADO')
                                    <small class="text-muted d-block">
                                        <i class="bi bi-archive me-1"></i>
                                        {{ $seccion->inscritos_historicos_count }} totales
                                    </small>
                                    <small class="d-block">
                                        <span class="text-success">{{ $seccion->aprobados_count }} aprobados</span> ·
                                        <span class="text-danger">{{ $seccion->reprobados_count }} reprobados</span> ·
                                        <span class="text-secondary">{{ $seccion->retirados_count }} retirados</span>
                                    </small>
                                @endif
                            </td>
                            <td>
                                @if($seccion->horarios->isNotEmpty())
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach($seccion->horarios as $sh)
                                            <span class="badge text-bg-light border small font-monospace">
                                                {{-- FIX SEGURO ANTI-500: Validación del método short() --}}
                                                {{ is_object($sh->horario->dia) && method_exists($sh->horario->dia, 'short') ? $sh->horario->dia->short() : substr(is_object($sh->horario->dia) ? ($sh->horario->dia->value ?? $sh->horario->dia->name) : $sh->horario->dia, 0, 3) }} 
                                                {{ substr($sh->horario->hora_inicio, 0, 5) }}
                                                · {{ $sh->aula->nombre_aula }}
                                                @if($sh->claseCompartida && $sh->claseCompartida->seccionesHorario->count() > 1)
                                                    <span class="badge bg-info text-white ms-1" 
                                                          title="Compartida con otras {{ $sh->claseCompartida->seccionesHorario->count() - 1 }} sección(es)">
                                                        <i class="bi bi-people-fill"></i>
                                                    </span>
                                                @endif
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-muted small">Sin bloques</span>
                                @endif
                            </td>
                            <td>
                                {{-- FIX SEGURO ANTI-500: Validación de los métodos badge() y label() --}}
                                <span class="badge {{ is_object($seccion->estado) && method_exists($seccion->estado, 'badge') ? $seccion->estado->badge() : 'bg-secondary' }}">
                                    {{ is_object($seccion->estado) && method_exists($seccion->estado, 'label') ? $seccion->estado->label() : (is_object($seccion->estado) ? ($seccion->estado->value ?? $seccion->estado->name) : $seccion->estado) }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalEditarSeccion{{ $seccion->seccion_id }}"
                                            title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-outline-info"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalVerHorariosSeccion{{ $seccion->seccion_id }}"
                                            title="Ver horarios">
                                        <i class="bi bi-clock-history"></i>
                                    </button>
                                    <button class="btn btn-outline-danger"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalEliminarSeccion{{ $seccion->seccion_id }}"
                                            title="Eliminar">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <i class="bi bi-journal-x fs-2 d-block mb-2"></i>
                                No hay secciones registradas
                                {{ $idCicloFiltro ? 'en este ciclo' : '' }}.
                                @if($idCicloFiltro)
                                    <div class="mt-3">
                                        <button class="btn btn-sm btn-outline-secondary"
                                                onclick="document.getElementById('filtro-ciclo').value=''; document.getElementById('filtro-ciclo').form.submit();">
                                            <i class="bi bi-arrow-clockwise me-1"></i> Ver todos los ciclos
                                        </button>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ================================================================
     MODAL: CREAR SECCIÓN
     ================================================================ --}}
<div class="modal fade" id="modalCrearSeccion" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-white border-bottom">
                <h5 class="modal-title fw-bold">Nueva Sección</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('coordinador-facultad.secciones.store') }}" method="POST"
                  x-data="seccionForm()">
                @csrf
                <div class="modal-body p-4">
                    <div class="row">

                        @include('coordinador-facultad.partials.seccion-form-fields', ['seccion' => null])

                        {{-- ═══════════════════════════════════════════════════
                            BLOQUES HORARIOS (solo en crear)
                            ═══════════════════════════════════════════════════ --}}

                        {{-- Modo grupo --}}
                        <div class="col-12 mt-3" x-show="grupoId">
                            <hr>
                            <h6 class="fw-bold small text-muted mb-2">
                                <i class="bi bi-clock me-1"></i> Aulas por bloque del grupo
                            </h6>
                            <div class="alert alert-info small mb-2">
                                Los bloques horarios se heredan del grupo. Solo asigná un aula por bloque.
                            </div>

                            <div class="table-responsive border rounded-3">
                                <table class="table table-sm align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-3">Bloque horario</th>
                                            <th style="width: 45%;">Aula</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="h in horariosDelGrupo" :key="h.horario_id">
                                            <tr>
                                                <td class="ps-3">
                                                    <span class="badge text-bg-light border font-monospace"
                                                          x-text="h.etiqueta"></span>
                                                </td>
                                                <td>
                                                    <select :name="`aulas_por_horario[${h.horario_id}]`"
                                                            class="form-select form-select-sm"
                                                            x-model="aulasPorHorario[h.horario_id]"
                                                            required>
                                                        <option value="">Seleccione aula...</option>
                                                        @foreach($aulas as $a)
                                                            <option value="{{ $a->aula_id }}">
                                                                {{ $a->nombre_aula }} — {{ $a->edificio->nombre ?? '' }}
                                                                (cap. {{ $a->capacidad }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Modo libre --}}
                        <div class="col-12 mt-3" x-show="!grupoId">
                            <hr>
                            <h6 class="fw-bold small text-muted mb-2">
                                <i class="bi bi-clock me-1"></i> Bloques horarios
                            </h6>
                            <div class="alert alert-warning small mb-2">
                                Como no hay grupo, agregá los bloques horarios y su aula.
                            </div>

                            <template x-for="(bloque, idx) in bloquesLibres" :key="bloque.uid">
                                <div class="row g-2 align-items-end mb-2">
                                    <div class="col-md-5">
                                        <label class="form-label small">Bloque horario *</label>
                                        <select :name="`horarios[${idx}][id_horario]`"
                                                class="form-select form-select-sm"
                                                x-model="bloque.id_horario"
                                                required>
                                            <option value="">Seleccione...</option>
                                            <template x-for="h in bloque.horariosDisponibles" :key="h.horario_id">
                                                <option :value="h.horario_id" x-text="h.etiqueta"></option>
                                            </template>
                                        </select>
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label small">Aula *</label>
                                        <select :name="`horarios[${idx}][id_aula]`"
                                                class="form-select form-select-sm"
                                                x-model="bloque.id_aula"
                                                required>
                                            <option value="">Seleccione...</option>
                                            <template x-for="a in bloque.aulasDisponibles" :key="a.aula_id">
                                                <option :value="a.aula_id"
                                                        x-text="`${a.nombre} — ${a.edificio} (cap. ${a.capacidad})`"></option>
                                            </template>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button"
                                                class="btn btn-sm btn-outline-danger w-100"
                                                @click="bloquesLibres.splice(idx, 1)">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </div>
                                </div>
                            </template>

                            <div x-show="bloquesLibres.length === 0" class="text-muted small mb-2">
                                Sin bloques horarios.
                            </div>

                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                    @click="agregarBloqueLibre()">
                                <i class="bi bi-plus-circle me-1"></i> Agregar bloque horario
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-check-circle me-1"></i> Crear sección
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ================================================================
     MODALES: EDITAR / ELIMINAR / HORARIOS POR SECCIÓN
     ================================================================ --}}
@foreach($secciones as $seccion)
    {{-- Editar --}}
   <div class="modal fade" id="modalEditarSeccion{{ $seccion->seccion_id }}" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-white border-bottom">
                    <h5 class="modal-title fw-bold">
                        Editar: {{ $seccion->materia->nombre ?? '—' }} — Grupo {{ $seccion->nombre_grupo }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('coordinador-facultad.secciones.update', $seccion) }}" method="POST"
                    x-data="seccionFormEdit(
                        {{ $seccion->id_ciclo_lectivo }}, 
                        {{ $seccion->id_grupo ?? 'null' }}, 
                        {{ $seccion->cupo_maximo }}, 
                        {{ $seccion->cupo_extra_maximo }},
                        {{ $seccion->permite_sobrecupo ? 'true' : 'false' }},
                        '{{ addslashes($seccion->nombre_grupo) }}'
                    )">
                    @csrf @method('PUT')
                    <div class="modal-body p-4">
                        @include('coordinador-facultad.partials.seccion-form-fields', ['seccion' => $seccion])
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
    <div class="modal fade" id="modalEliminarSeccion{{ $seccion->seccion_id }}" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-danger text-white border-0">
                    <h5 class="modal-title fw-bold">Eliminar sección</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="mb-3">
                        ¿Confirmás eliminar la sección de
                        <strong>{{ $seccion->materia->nombre }}</strong> — Grupo
                        <strong>{{ $seccion->nombre_grupo }}</strong>?
                    </p>
                    @if($seccion->detallesInscripcion()->exists())
                        <div class="alert alert-danger small mb-0">
                            <i class="bi bi-x-circle-fill me-1"></i>
                            Tiene alumnos inscritos. No se puede eliminar.
                        </div>
                    @else
                        <div class="alert alert-warning small mb-0">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i>
                            Se eliminarán también sus bloques horarios
                            y, si compartía clase con otras secciones, se recalculará la capacidad del aula.
                        </div>
                    @endif
                </div>
                <div class="modal-footer bg-light border-top-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form action="{{ route('coordinador-facultad.secciones.destroy', $seccion) }}" method="POST">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger"
                                {{ $seccion->detallesInscripcion()->exists() ? 'disabled' : '' }}>
                            <i class="bi bi-trash me-1"></i> Eliminar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal VER HORARIOS (solo lectura) --}}
    <div class="modal fade" id="modalVerHorariosSeccion{{ $seccion->seccion_id }}" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-white border-bottom">
                    <h5 class="modal-title fw-bold">
                        Horarios: {{ $seccion->materia->nombre }} — Grupo {{ $seccion->nombre_grupo }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">

                    @forelse($seccion->horarios as $sh)
                        <div class="border rounded-3 p-3 mb-2">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <span class="badge text-bg-light border font-monospace">
                                        {{-- FIX SEGURO ANTI-500 --}}
                                        {{ is_object($sh->horario->dia) && method_exists($sh->horario->dia, 'label') ? $sh->horario->dia->label() : (is_object($sh->horario->dia) ? ($sh->horario->dia->value ?? $sh->horario->dia->name) : $sh->horario->dia) }}
                                        {{ substr($sh->horario->hora_inicio, 0, 5) }}–{{ substr($sh->horario->hora_fin, 0, 5) }}
                                    </span>
                                    <span class="ms-2 small text-muted">
                                        {{ $sh->aula->nombre_aula }} — {{ $sh->aula->edificio->nombre ?? '—' }}
                                        (cap. {{ $sh->aula->capacidad }})
                                    </span>
                                </div>
                            </div>

                            @if($sh->claseCompartida && $sh->claseCompartida->seccionesHorario->count() > 1)
                                <div class="mt-2 pt-2 border-top small text-muted">
                                    <i class="bi bi-people-fill me-1"></i>
                                    <strong>Compartida con:</strong>
                                    <ul class="mb-0 ps-3">
                                        @foreach($sh->claseCompartida->seccionesHorario as $otro)
                                            @if($otro->seccion_id !== $seccion->seccion_id)
                                                <li>
                                                    {{ $otro->seccion->materia->nombre ?? '—' }}
                                                    — Grupo {{ $otro->seccion->nombre_grupo }}
                                                    ({{ $otro->seccion->cicloLectivo->nombre_ciclo ?? '' }})
                                                </li>
                                            @endif
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="text-muted small text-center py-3">
                            <i class="bi bi-info-circle me-1"></i> Sin bloques asignados.
                        </div>
                    @endforelse

                    <div class="alert alert-info small mt-3 mb-0">
                        <i class="bi bi-info-circle me-1"></i>
                        Para cambiar los bloques horarios, <strong>eliminá la sección y volvé a crearla</strong>.
                        Esta acción solo es posible si no tiene alumnos inscritos.
                    </div>
                </div>
                <div class="modal-footer bg-light border-top-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
@endforeach
@endsection

@push('scripts')
    <script>
    @php
        // FIX SEGURO ANTI-500 EN EL MAPEADO JSON PARA EL SCRIPT DE ALPINE
        $horariosJson = $horariosPorCiclo->map(fn($items) => $items->map(fn($h) => [
            'horario_id'  => $h->horario_id,
            'etiqueta'    => $h->etiqueta,
            'dia'         => is_object($h->dia) ? ($h->dia->value ?? $h->dia->name) : $h->dia,
            'hora_inicio' => $h->hora_inicio,
        ])->values());

        $gruposConBloques = $grupos->load('horarios')->map(fn($g) => [
            'grupo_id'         => $g->grupo_id,
            'nombre'           => $g->nombre,
            'id_ciclo_lectivo' => $g->id_ciclo_lectivo,
            'horarios'         => $g->horarios->map(fn($h) => [
                'horario_id' => $h->horario_id,
                'etiqueta'   => $h->etiqueta,
            ])->values(),
        ])->values();

        $aulasJson = $aulas->map(fn($a) => [
            'aula_id'    => $a->aula_id,
            'nombre'     => $a->nombre_aula,
            'edificio'   => $a->edificio->nombre ?? '',
            'capacidad'  => $a->capacidad,
        ])->values();
    @endphp

    window.HORARIOS_POR_CICLO = @json($horariosJson);
    window.GRUPOS_CON_BLOQUES = @json($gruposConBloques);
    window.AULAS              = @json($aulasJson);

    document.addEventListener('alpine:init', () => {
        Alpine.data('seccionForm', () => ({
            cicloId: '',
            grupoId: '',
            nombreGrupo: '',
            cupoMaximo: 30,
            cupoExtra: 0,
            permiteSobrecupo: false, 
            gruposFiltrados: [],
            horariosDelGrupo: [],
            aulasPorHorario: {},
            bloquesLibres: [],

            init() {
                const cicloInicial   = @js($isEdit ?? false ? $seccion->id_ciclo_lectivo : '');
                const grupoInicial   = @js($isEdit ?? false ? $seccion->id_grupo : '');
                const cupoInicial    = @js($isEdit ?? false ? $seccion->cupo_maximo : 30);
                const extraInicial   = @js($isEdit ?? false ? $seccion->cupo_extra_maximo : 0);
                const permiteInicial = @js($isEdit ?? false ? $seccion->permite_sobrecupo : false);

                this.cicloId    = String(cicloInicial || '');
                this.cupoMaximo = Number(cupoInicial) || 30;
                this.filtrarGrupos();
                this.grupoId    = String(grupoInicial || '');
                this.cambioGrupo();
                this.cupoExtra        = Number(extraInicial) || 0;
                this.permiteSobrecupo = Boolean(permiteInicial);
                if (!this.permiteSobrecupo) {
                    this.cupoExtra = 0;
                }
            },

            cambioSobrecupo() {
                if (!this.permiteSobrecupo) {
                    this.cupoExtra = 0;
                } else if (this.cupoExtra === 0) {
                    this.cupoExtra = 1;
                }
            },

            cambioCiclo() {
                this.filtrarGrupos();
                this.grupoId = '';
                this.cambioGrupo();
            },

            filtrarGrupos() {
                this.gruposFiltrados = window.GRUPOS_CON_BLOQUES.filter(
                    g => String(g.id_ciclo_lectivo) === String(this.cicloId)
                );
            },

            cambioGrupo() {
                const grupo = this.gruposFiltrados.find(
                    g => String(g.grupo_id) === String(this.grupoId)
                );

                if (grupo) {
                    this.nombreGrupo = grupo.nombre;
                    this.horariosDelGrupo = grupo.horarios || [];
                    this.aulasPorHorario = {};
                    this.horariosDelGrupo.forEach(h => {
                        this.aulasPorHorario[h.horario_id] = '';
                    });
                } else {
                    this.horariosDelGrupo = [];
                    this.aulasPorHorario = {};
                }
            },

            agregarBloqueLibre() {
                const idx = this.bloquesLibres.length;
                const horarios = window.HORARIOS_POR_CICLO[String(this.cicloId)] || [];
                this.bloquesLibres.push({
                    idx,
                    id_horario: '',
                    id_aula: '',
                    horariosDisponibles: horarios,
                    aulasDisponibles: window.AULAS,
                });
            },

            get capacidadMinima() {
                let capacidades = [];
                Object.values(this.aulasPorHorario).forEach(id => {
                    const aula = window.AULAS.find(a => String(a.aula_id) === String(id));
                    if (aula) capacidades.push(aula.capacidad);
                });
                this.bloquesLibres.forEach(b => {
                    const aula = window.AULAS.find(a => String(a.aula_id) === String(b.id_aula));
                    if (aula) capacidades.push(aula.capacidad);
                });
                return capacidades.length > 0 ? Math.min(...capacidades) : null;
            },

            get cupoExcedido() {
                const min = this.capacidadMinima;
                return min !== null && this.cupoMaximo > min;
            },

            ajustarCupo() {
                const min = this.capacidadMinima;
                if (min !== null && this.cupoMaximo > min) {
                    this.cupoMaximo = min;
                }
            },
        }));
        
        Alpine.data('seccionFormEdit', (cicloId, grupoId, cupoMaximo, cupoExtra, permiteSobrecupo,nombreGrupo) => ({
            cicloId: String(cicloId || ''),
            grupoId: String(grupoId || ''),
            nombreGrupo: String(nombreGrupo || ''),
            cupoMaximo: Number(cupoMaximo) || 30,
            cupoExtra: Number(cupoExtra) || 0,
            permiteSobrecupo: Boolean(permiteSobrecupo),
            gruposFiltrados: [],

            init() {
                this.filtrarGrupos();
                if (!this.permiteSobrecupo) {
                    this.cupoExtra = 0;
                }
            },

            cambioSobrecupo() {
                if (!this.permiteSobrecupo) {
                    this.cupoExtra = 0;
                } else if (this.cupoExtra === 0) {
                    this.cupoExtra = 1;
                }
            },

            cambioCiclo() {
                this.filtrarGrupos();
                this.grupoId = '';
            },

            filtrarGrupos() {
                this.gruposFiltrados = window.GRUPOS_CON_BLOQUES.filter(
                    g => String(g.id_ciclo_lectivo) === String(this.cicloId)
                );
            },

            cambioGrupo() {
                const grupo = this.gruposFiltrados.find(
                    g => String(g.grupo_id) === String(this.grupoId)
                );
                if (grupo) {
                    this.nombreGrupo = grupo.nombre;
                }
            },
        }));
    });
    </script>
@endpush