@extends('layouts.app')

@section('title', 'Secciones')

@section('content')
<div class="container-fluid py-4">

    {{-- Header --}}
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-md-between gap-3 p-3 mb-4 rounded-4 card">
        <div class="d-flex align-items-center">
            <div class="px-3 py-2 rounded-3 bg-danger bg-opacity-10" style="width:48px;height:48px;">
                <i class="bi bi-journal-bookmark-fill fs-4 text-danger"></i>
            </div>
            <div class="px-3">
                <h1 class="h4 fw-bold mb-0">GESTIÓN DE SECCIONES</h1>
                <p class="text-muted mb-0 small">Asignación de materias, docentes, aulas y horarios</p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('coordinador-facultad.horarios-grupohorario.index') }}"
               class="btn btn-outline-secondary px-3 py-2">
                <i class="bi bi-clock-history me-1"></i> Horarios
            </a>
            <a href="{{ route('home') }}"
               class="btn bg-danger-subtle text-danger border border-danger px-3 py-2">
                <i class="fa-solid fa-home me-1"></i> Inicio
            </a>
        </div>
    </div>

    {{-- Botón crear --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h6 fw-bold mb-0">
            <i class="bi bi-list-ul text-danger me-1"></i>
            Secciones registradas
            <span class="badge bg-secondary ms-1">{{ $secciones->count() }}</span>
        </h2>
        <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalCrearSeccion">
            <i class="bi bi-plus-circle me-1"></i> Nueva sección
        </button>
    </div>

    {{-- Tabla de secciones --}}
    <div class="card border-0 shadow-sm">
        <div>
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
                                <small class="text-muted d-block">{{ $seccion->inscritos_count }} inscritos</small>
                            </td>
                            <td>
                                @if($seccion->horarios->isNotEmpty())
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach($seccion->horarios as $sh)
                                            <span class="badge text-bg-light border small font-monospace">
                                                {{ $sh->horario->dia->short() }} {{ substr($sh->horario->hora_inicio, 0, 5) }}
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
                                <span class="badge {{ $seccion->estado->badge() }}">
                                    {{ $seccion->estado->label() }}
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
                                No hay secciones registradas.
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
                                        {{ $sh->horario->dia->label() }}
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
        $horariosJson = $horariosPorCiclo->map(fn($items) => $items->map(fn($h) => [
            'horario_id'  => $h->horario_id,
            'etiqueta'    => $h->etiqueta,
            'dia'         => $h->dia->value,
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
    window.AULAS             = @json($aulasJson);

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
                // Si estamos editando, precargar
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
                    // Resetear aulas por horario
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
                // Calcula la capacidad mínima entre las aulas elegidas
                let capacidades = [];

                // Modo grupo
                Object.values(this.aulasPorHorario).forEach(id => {
                    const aula = window.AULAS.find(a => String(a.aula_id) === String(id));
                    if (aula) capacidades.push(aula.capacidad);
                });

                // Modo libre
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
                    // Opción A: no dejar avanzar (solo advertencia visual)
                    // Opción B: auto-ajustar al mínimo
                    // → Vamos con B: auto-ajustar y avisar
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