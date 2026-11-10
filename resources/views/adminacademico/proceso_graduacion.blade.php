@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- HEADER                                                       --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-md-between gap-3 p-3 mb-4 rounded-4 card">
        <div class="d-flex align-items-center">
            <div class="px-3 py-2 rounded-3 bg-danger bg-opacity-10" style="width:48px;height:48px;">
                <i class="bi bi-mortarboard-fill fs-4 text-danger"></i>
            </div>
            <div class="px-3">
                <h2 class="h4 fw-bold mb-0">PROCESO DE GRADUACIÓN</h2>
                <p class="text-muted mb-0 small">Gestión de tesis y cursos de pre-especialización.</p>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm">
            <i class="bi bi-check-circle-fill me-1"></i>{{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm">
            <i class="bi bi-x-circle-fill me-1"></i>{{ session('error') }}
        </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- TABS                                                        --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <ul class="nav nav-tabs mb-4" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="tab-activos-btn" data-bs-toggle="tab" data-bs-target="#tab-activos" type="button" role="tab">
                <i class="bi bi-play-circle-fill me-1"></i>
                Procesos Activos
                <span class="badge bg-danger ms-1">{{ $procesosActivos->total() }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-egresados-btn" data-bs-toggle="tab" data-bs-target="#tab-egresados" type="button" role="tab">
                <i class="bi bi-person-plus-fill me-1"></i>
                Egresados sin Proceso
                <span class="badge bg-warning text-dark ms-1">{{ $egresadosSinProceso->count() }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-historicos-btn" data-bs-toggle="tab" data-bs-target="#tab-historicos" type="button" role="tab">
                <i class="bi bi-clock-history me-1"></i>
                Históricos
                <span class="badge bg-secondary ms-1">{{ $procesosHistoricos->total() }}</span>
            </button>
        </li>
    </ul>

    <div class="tab-content">

        {{-- ═══════════════════════════════════════════════════════════ --}}
        {{-- TAB 1 · PROCESOS ACTIVOS                                     --}}
        {{-- ═══════════════════════════════════════════════════════════ --}}
        <div class="tab-pane fade show active" id="tab-activos" role="tabpanel">

            {{-- Filtros --}}
            <form method="GET" action="{{ route('admin-academico.graduacion.index') }}" class="card border-0 shadow-sm p-3 mb-3">
                <input type="hidden" name="tab" value="activos">
                <div class="row g-2">
                    <div class="col-md-4">
                        <select name="estado" class="form-select">
                            <option value="">Todos los estados</option>
                            @foreach($estados as $e)
                                <option value="{{ $e->value }}" {{ request('estado') === $e->value ? 'selected' : '' }}>
                                    {{ $e->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <select name="modalidad" class="form-select">
                            <option value="">Todas las modalidades</option>
                            @foreach($modalidades as $m)
                                <option value="{{ $m->value }}" {{ request('modalidad') === $m->value ? 'selected' : '' }}>
                                    {{ $m->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="bi bi-funnel me-1"></i> Filtrar
                        </button>
                    </div>
                </div>
            </form>

            {{-- Tabla activos --}}
            <div class="card border-0 shadow-sm">
                <div>
                    <table class="table table-hover align-middle mb-0" id="tableprocesograduado2">
                        <thead class="table-light">
                            <tr>
                                <th>Carnet</th>
                                <th>Alumno</th>
                                <th>Modalidad</th>
                                <th>Tema / Curso</th>
                                <th class="text-center">Grupo</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center">Intentos</th>
                                <th class="text-center">Nota</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($procesosActivos as $p)
                                <tr>
                                    <td><code class="small">{{ $p->alumno->codigo_estudiante }}</code></td>
                                    <td>
                                        <div class="fw-semibold small">{{ $p->alumno->usuario?->name }}</div>
                                        <small class="text-muted">{{ $p->alumno->carrera?->nombre }}</small>
                                    </td>
                                    <td>
                                        <span class="badge {{ $p->modalidad->badge() }}">{{ $p->modalidad->label() }}</span>
                                    </td>
                                    <td class="text-truncate" style="max-width: 200px;">
                                        {{ $p->modalidad === \App\Enums\ModalidadGraduacion::TRABAJO_GRADUACION
                                            ? ($p->grupo?->titulo_tema ?? '—')
                                            : ($p->nombre_curso ?? '—') }}
                                    </td>
                                    <td class="text-center">
                                        @if($p->grupo)
                                            <code class="small">{{ $p->grupo->codigo_grupo }}</code>
                                            <br>
                                            <small class="text-muted">{{ $p->grupo->procesos()->count() }}/3</small>
                                        @else
                                            <small class="text-muted">Individual</small>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="badge {{ $p->estado_proceso->badge() }}">
                                            {{ $p->estado_proceso->label() }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge {{ $p->intentos_defensa >= 2 ? 'bg-danger' : ($p->intentos_defensa === 1 ? 'bg-warning text-dark' : 'bg-secondary') }}">
                                            {{ $p->intentos_defensa }} / 2
                                        </span>
                                    </td>
                                    <td class="text-center fw-bold">{{ $p->nota_defensa ?? '—' }}</td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-1">
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-danger"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#modalEstado"
                                                    data-proceso-id="{{ $p->proceso_id }}"
                                                    data-alumno-id="{{ $p->id_alumno }}"
                                                    data-modalidad="{{ $p->modalidad->value }}"
                                                    data-estado="{{ $p->estado_proceso->value }}"
                                                    data-estado-label="{{ $p->estado_proceso->label() }}"
                                                    data-intentos="{{ $p->intentos_defensa }}"
                                                    data-nota="{{ $p->nota_defensa }}"
                                                    data-estado-url="{{ route('admin-academico.graduacion.cambiar-estado', $p->proceso_id) }}">
                                                <i class="bi bi-arrow-repeat"></i> Estado
                                            </button>
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-secondary"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#modalHistorial"
                                                    data-alumno-id="{{ $p->id_alumno }}"
                                                    data-nombre="{{ $p->alumno->usuario?->name }}">
                                                <i class="bi bi-clock-history"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-5">
                                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                        No hay procesos activos.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($procesosActivos->hasPages())
                    <div class="card-footer bg-white border-0">
                        {{ $procesosActivos->appends(request()->query())->links() }}
                    </div>
                @endif
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════════ --}}
        {{-- TAB 2 · EGRESADOS SIN PROCESO                                --}}
        {{-- ═══════════════════════════════════════════════════════════ --}}
        <div class="tab-pane fade" id="tab-egresados" role="tabpanel">

            <div class="alert alert-info border-0 small mb-3">
                <i class="bi bi-info-circle me-1"></i>
                Alumnos con estado <strong>EGRESADO</strong> que no tienen un proceso de graduación activo.
                Pueden ser alumnos nuevos o alumnos que reprobaron dos veces y deben iniciar de cero.
            </div>

            <div class="card border-0 shadow-sm">
                <div>
                    <table class="table table-hover align-middle mb-0" id="tableregistroegreso">
                        <thead class="table-light">
                            <tr>
                                <th>Carnet</th>
                                <th>Alumno</th>
                                <th>Carrera</th>
                                <th class="text-center">Procesos previos</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($egresadosSinProceso as $alumno)
                                @php
                                    $totalProcesos = $alumno->procesosGraduacion()->count();
                                @endphp
                                <tr>
                                    <td><code class="small">{{ $alumno->codigo_estudiante }}</code></td>
                                    <td>
                                        <div class="fw-semibold small">{{ $alumno->usuario?->name }}</div>
                                        <small class="text-muted">{{ $alumno->correo_institucional }}</small>
                                    </td>
                                    <td>
                                        <small class="fw-semibold">{{ $alumno->carrera?->nombre }}</small><br>
                                        <small class="text-muted">{{ $alumno->carrera?->facultad?->nombre }}</small>
                                    </td>
                                    <td class="text-center">
                                        @if($totalProcesos === 0)
                                            <span class="badge bg-secondary">Nuevo</span>
                                        @else
                                            <span class="badge bg-warning text-dark">
                                                {{ $totalProcesos }} intento(s) previo(s)
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-1">
                                            @if($totalProcesos > 0)
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-secondary"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#modalHistorial"
                                                        data-alumno-id="{{ $alumno->alumno_id }}"
                                                        data-nombre="{{ $alumno->usuario?->name }}">
                                                    <i class="bi bi-clock-history"></i>
                                                </button>
                                            @endif
                                            <button type="button"
                                                    class="btn btn-sm btn-danger"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#modalRegistrar"
                                                    data-alumno-id="{{ $alumno->alumno_id }}"
                                                    data-nombre="{{ $alumno->usuario?->name }}"
                                                    data-carnet="{{ $alumno->codigo_estudiante }}"
                                                    data-carrera="{{ $alumno->carrera?->nombre }}"
                                                    data-nivel="{{ DB::table('carreras as c')->join('niveles_academicos as na','c.id_nivel_academico','=','na.nivel_academico_id')->where('c.carrera_id',$alumno->id_carrera)->value('na.codigo') }}"
                                                    data-store-url="{{ route('admin-academico.graduacion.store', $alumno->alumno_id) }}">
                                                <i class="bi bi-plus-circle"></i> Registrar
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-5">
                                        <i class="bi bi-check-circle fs-1 d-block mb-2"></i>
                                        Todos los egresados tienen un proceso de graduación activo.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════════ --}}
        {{-- TAB 3 · HISTÓRICOS                                          --}}
        {{-- ═══════════════════════════════════════════════════════════ --}}
        <div class="tab-pane fade" id="tab-historicos" role="tabpanel">

            <div class="alert alert-secondary border-0 small mb-3">
                <i class="bi bi-info-circle me-1"></i>
                Procesos <strong>cerrados</strong> (reprobados dos veces) o que ya fueron reemplazados.
                Los alumnos con procesos históricos pueden iniciar uno nuevo desde "Egresados sin proceso".
            </div>

            <div class="card border-0 shadow-sm">
                <div>
                    <table class="table table-hover align-middle mb-0" id="tableHistoricos">
                        <thead class="table-light">
                            <tr>
                                <th>Carnet</th>
                                <th>Alumno</th>
                                <th>Modalidad</th>
                                <th>Tema / Curso</th>
                                <th class="text-center">Estado final</th>
                                <th class="text-center">Intentos</th>
                                <th class="text-center">Nota</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($procesosHistoricos as $p)
                                <tr>
                                    <td><code class="small">{{ $p->alumno->codigo_estudiante }}</code></td>
                                    <td>
                                        <div class="fw-semibold small">{{ $p->alumno->usuario?->name }}</div>
                                        <small class="text-muted">{{ $p->alumno->carrera?->nombre }}</small>
                                    </td>
                                    <td>
                                        <span class="badge {{ $p->modalidad->badge() }}">{{ $p->modalidad->label() }}</span>
                                    </td>
                                    <td class="text-truncate" style="max-width: 200px;">
                                        {{ $p->modalidad === \App\Enums\ModalidadGraduacion::TRABAJO_GRADUACION
                                            ? ($p->grupo?->titulo_tema ?? '—')
                                            : ($p->nombre_curso ?? '—') }}
                                    </td>
                                    <td class="text-center">
                                        <span class="badge {{ $p->estado_proceso->badge() }}">
                                            {{ $p->estado_proceso->label() }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-danger">{{ $p->intentos_defensa }} / 2</span>
                                    </td>
                                    <td class="text-center">{{ $p->nota_defensa ?? '—' }}</td>
                                    <td class="text-end">
                                        <button type="button"
                                                class="btn btn-sm btn-outline-secondary"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalHistorial"
                                                data-alumno-id="{{ $p->id_alumno }}"
                                                data-nombre="{{ $p->alumno->usuario?->name }}">
                                            <i class="bi bi-clock-history"></i> Ver historial
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-5">
                                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                        No hay procesos históricos registrados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($procesosHistoricos->hasPages())
                    <div class="card-footer bg-white border-0">
                        {{ $procesosHistoricos->appends(request()->query())->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════ --}}
{{-- MODAL: REGISTRAR PROCESO                                       --}}
{{-- ═══════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalRegistrar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <form method="POST" id="formRegistrar" class="modal-content border-0 shadow" novalidate>
            @csrf

            <div class="modal-header bg-danger text-white border-0">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-mortarboard-fill me-1"></i> Registrar Proceso de Graduación
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body p-4">

                <div class="mb-3 p-2 rounded bg-light">
                    <p class="mb-1"><strong id="rNombre"></strong></p>
                    <small class="text-muted d-block">
                        <i class="bi bi-person-vcard me-1"></i><span id="rCarnet"></span>
                    </small>
                    <small class="text-muted d-block">
                        <i class="bi bi-building me-1"></i><span id="rCarrera"></span>
                    </small>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Modalidad <span class="text-danger">*</span></label>
                    <select name="modalidad" id="rModalidad" class="form-select" required>
                        <option value="">Selecciona...</option>
                        @foreach($modalidades as $m)
                            <option value="{{ $m->value }}">{{ $m->label() }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted d-block mt-1" id="rModalidadHelp"></small>
                </div>

                <div id="wrapperTrabajoGraduacion" class="d-none">
                    <div class="alert alert-info border-0 small mb-3">
                        <i class="bi bi-info-circle me-1"></i>
                        El estudiante puede <strong>unirse a un grupo existente</strong> o crear un nuevo grupo.
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Unirse a un grupo existente (opcional)</label>
                        <select name="id_grupo" id="rGrupoExistente" class="form-select">
                            <option value="">No, crear nuevo grupo</option>
                            @foreach($gruposDisponibles as $g)
                                <option value="{{ $g->grupo_id }}">
                                    {{ $g->codigo_grupo }} — {{ $g->titulo_tema }} ({{ $g->procesos_count }}/3)
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div id="wrapperNuevoGrupo">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Título del tema <span class="text-danger">*</span></label>
                            <input type="text" name="titulo_tema" id="rTitulo" class="form-control"
                                   maxlength="255" minlength="10">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Docente asesor</label>
                            <select name="id_docente_asesor" id="rDocente" class="form-select">
                                <option value="">Sin asignar</option>
                                @foreach($docentes as $d)
                                    <option value="{{ $d->docente_id }}">{{ $d->usuario?->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div id="wrapperPreEspecializacion" class="d-none">
                    <div class="alert alert-info border-0 small mb-3">
                        <i class="bi bi-info-circle me-1"></i>
                        La pre-especialización es un <strong>curso grupal</strong> sin asesor individual.
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nombre del curso <span class="text-danger">*</span></label>
                        <input type="text" name="titulo_tema" id="rTituloPE" class="form-control"
                               maxlength="255" minlength="10"
                               placeholder="Ej: Curso de Pre-Especialización en Gestión del Talento Humano">
                    </div>
                </div>

                <div class="alert alert-light border small mt-3 mb-0">
                    <i class="bi bi-cash-coin me-1"></i>
                    Los <strong>derechos de grado</strong> se cancelan al final del proceso.
                </div>
            </div>

            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-danger" id="btnRegistrar">
                    <i class="bi bi-check2 me-1"></i> Registrar
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════ --}}
{{-- MODAL: CAMBIAR ESTADO                                         --}}
{{-- ═══════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalEstado" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <form method="POST" id="formEstado" class="modal-content border-0 shadow" novalidate>
            @csrf
            @method('PATCH')

            <div class="modal-header bg-danger text-white border-0">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-arrow-repeat me-1"></i> Cambiar Estado
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body p-4">

                <p class="small mb-3">
                    Estado actual: <span class="badge bg-secondary" id="eActual"></span>
                </p>

                <div class="mb-3" id="eSelectWrapper">
                    <label for="eSelect" class="form-label small fw-semibold">
                        Nuevo estado <span class="text-danger">*</span>
                    </label>
                    <select id="eSelect" class="form-select">
                        <option value="">Selecciona...</option>
                        @foreach($estados as $e)
                            @if(!in_array($e->value, ['INSCRITO', 'APROBADO', 'REPROBADO']))
                                <option value="{{ $e->value }}">{{ $e->label() }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>

                <div class="mb-3 d-none" id="eResultadoWrapper">
                    <label for="eResultado" class="form-label small fw-semibold">
                        Resultado de defensa <span class="text-danger">*</span>
                    </label>
                    <select id="eResultado" class="form-select">
                        <option value="">Selecciona...</option>
                        <option value="APROBADO">Aprobado (nota ≥ 6.0)</option>
                        <option value="REPROBADO">Reprobado (nota &lt; 6.0)</option>
                    </select>
                </div>

                <div class="mb-3 d-none" id="eNotaWrapper">
                    <label for="eNota" class="form-label small fw-semibold">
                        Nota de defensa <span class="text-danger">*</span>
                    </label>
                    <input type="number" name="nota_defensa" id="eNota"
                           step="0.01" min="0" max="10" class="form-control">
                    <small class="text-muted" id="eNotaHelp">Mínimo 6.0 para aprobar.</small>
                </div>

                <div class="mb-3 d-none" id="ePagoWrapper">
                    <label for="ePagoSelect" class="form-label small fw-semibold">
                        Pago de derechos de grado <span class="text-danger">*</span>
                    </label>
                    <select name="id_pago_derechos_grado" id="ePagoSelect" class="form-select">
                        <option value="">Selecciona un pago</option>
                    </select>
                </div>

                <input type="hidden" name="estado" id="eEstadoFinal" value="">

                <div class="alert alert-info border-0 small mb-0" id="eAvisoFlujo">
                    <i class="bi bi-info-circle me-1"></i>
                    Flujo: <strong>INSCRITO → EN_DESARROLLO → EN_DEFENSA → APROBADO → GRADUADO</strong>.
                </div>
            </div>

            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-danger" id="btnEstado">
                    <i class="bi bi-check2 me-1"></i> Actualizar
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════ --}}
{{-- MODAL: HISTORIAL                                              --}}
{{-- ═══════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalHistorial" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content border-0 shadow">

            <div class="modal-header bg-secondary text-white border-0">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-clock-history me-1"></i> Historial de Procesos
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body p-4">

                <p class="small text-muted mb-3">
                    <i class="bi bi-person me-1"></i><strong id="hNombre"></strong>
                </p>

                <div id="hLista" class="d-flex flex-column gap-2">
                    <div class="text-center text-muted py-4">
                        <span class="spinner-border spinner-border-sm me-2"></span> Cargando...
                    </div>
                </div>
            </div>

            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    /* ═══════════════════════════════════════════════════════
     *  MÓDULO 1 · MODAL REGISTRAR
     * ═══════════════════════════════════════════════════════ */
    const modalRegistrar = document.getElementById('modalRegistrar');
    const formRegistrar  = document.getElementById('formRegistrar');
    const btnRegistrar   = document.getElementById('btnRegistrar');
    const rModalidad     = document.getElementById('rModalidad');
    const wrapperTG      = document.getElementById('wrapperTrabajoGraduacion');
    const wrapperPE      = document.getElementById('wrapperPreEspecializacion');
    const rGrupoExistente= document.getElementById('rGrupoExistente');
    const wrapperNuevoG  = document.getElementById('wrapperNuevoGrupo');
    const rModalidadHelp = document.getElementById('rModalidadHelp');
    const rTitulo        = document.getElementById('rTitulo');
    const rTituloPE      = document.getElementById('rTituloPE');
    const rDocente       = document.getElementById('rDocente');
    let rNivel = '';

    if (modalRegistrar && formRegistrar) {
        modalRegistrar.addEventListener('show.bs.modal', function (event) {
            const btn = event.relatedTarget;
            if (!btn) return;

            rNivel = btn.getAttribute('data-nivel') || '';

            document.getElementById('rNombre').textContent  = btn.getAttribute('data-nombre');
            document.getElementById('rCarnet').textContent  = btn.getAttribute('data-carnet');
            document.getElementById('rCarrera').textContent = btn.getAttribute('data-carrera');

            formRegistrar.action = btn.getAttribute('data-store-url');
            formRegistrar.reset();

            wrapperTG.classList.add('d-none');
            wrapperPE.classList.add('d-none');
            wrapperNuevoG.classList.remove('d-none');
            rModalidadHelp.textContent = '';

            Array.from(rModalidad.options).forEach(opt => {
                if (opt.value === 'PRE_ESPECIALIZACION') {
                    opt.disabled = ['MAESTRIA', 'POSGRADO'].includes(rNivel);
                }
            });

            btnRegistrar.disabled = false;
            btnRegistrar.innerHTML = '<i class="bi bi-check2 me-1"></i> Registrar';
        });

        rModalidad.addEventListener('change', function () {
            const valor = this.value;

            wrapperTG.classList.toggle('d-none', valor !== 'TRABAJO_GRADUACION');
            wrapperPE.classList.toggle('d-none', valor !== 'PRE_ESPECIALIZACION');

            if (valor === 'PRE_ESPECIALIZACION') {
                rTituloPE.disabled = false;
                rTituloPE.required = true;
                if (rTitulo)  { rTitulo.disabled  = true;  rTitulo.required  = false; }
                if (rDocente) { rDocente.disabled = true; }
                if (rGrupoExistente) { rGrupoExistente.value = ''; }
                rModalidadHelp.textContent = 'Curso grupal, sin asesor individual.';
            } else if (valor === 'TRABAJO_GRADUACION') {
                rTituloPE.disabled = true;
                rTituloPE.required = false;
                if (rTitulo)  { rTitulo.disabled  = false; rTitulo.required  = true; }
                if (rDocente) { rDocente.disabled = false; }
                rModalidadHelp.textContent = 'Los aranceles varían según el tamaño del grupo.';
            } else {
                rModalidadHelp.textContent = '';
            }
        });

        rGrupoExistente.addEventListener('change', function () {
            const nuevoGrupo = this.value === '';
            wrapperNuevoG.classList.toggle('d-none', !nuevoGrupo);
            rTitulo.required  = nuevoGrupo;
            rTitulo.disabled  = !nuevoGrupo;
            rDocente.disabled = !nuevoGrupo;
        });

        formRegistrar.addEventListener('submit', function () {
            if (rModalidad.value === 'PRE_ESPECIALIZACION') {
                rTituloPE.disabled = false;
                rTitulo.disabled   = true;
            } else {
                rTitulo.disabled   = (rGrupoExistente.value !== '');
                rTituloPE.disabled = true;
            }

            btnRegistrar.disabled = true;
            btnRegistrar.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Registrando...';
        });
    }

    /* ═══════════════════════════════════════════════════════
     *  MÓDULO 2 · MODAL CAMBIAR ESTADO
     * ═══════════════════════════════════════════════════════ */
    const modalEstado       = document.getElementById('modalEstado');
    const formEstado        = document.getElementById('formEstado');
    const eSelect           = document.getElementById('eSelect');
    const eSelectWrapper    = document.getElementById('eSelectWrapper');
    const eResultadoWrapper = document.getElementById('eResultadoWrapper');
    const eResultado        = document.getElementById('eResultado');
    const eNotaWrapper      = document.getElementById('eNotaWrapper');
    const eNota             = document.getElementById('eNota');
    const eNotaHelp         = document.getElementById('eNotaHelp');
    const ePagoWrapper      = document.getElementById('ePagoWrapper');
    const ePagoSelect       = document.getElementById('ePagoSelect');
    const eEstadoFinal      = document.getElementById('eEstadoFinal');
    const btnEstado         = document.getElementById('btnEstado');
    const eAvisoFlujo       = document.getElementById('eAvisoFlujo');

    let alumnoActual    = null;
    let modalidadActual = null;
    let estadoActual    = null;
    let intentosActual  = 0;
    let notaActual = null;

    if (modalEstado && formEstado) {
        modalEstado.addEventListener('show.bs.modal', function (event) {
            const btn = event.relatedTarget;
            if (!btn) return;

            alumnoActual    = btn.getAttribute('data-alumno-id');
            modalidadActual = btn.getAttribute('data-modalidad');
            estadoActual    = btn.getAttribute('data-estado');
            intentosActual  = parseInt(btn.getAttribute('data-intentos') || '0');
            notaActual      = btn.getAttribute('data-nota');

            document.getElementById('eActual').textContent = btn.getAttribute('data-estado-label');
            formEstado.action = btn.getAttribute('data-estado-url');

            eSelect.value           = '';
            eResultado.value        = '';
            eNota.value             = '';
            eEstadoFinal.value      = '';
            ePagoSelect.innerHTML   = '<option value="">Selecciona un pago</option>';
            ePagoSelect.disabled    = false;

            eSelectWrapper.classList.remove('d-none');
            eResultadoWrapper.classList.add('d-none');
            eNotaWrapper.classList.add('d-none');
            ePagoWrapper.classList.add('d-none');

            btnEstado.disabled = false;
            btnEstado.innerHTML = '<i class="bi bi-check2 me-1"></i> Actualizar';

            if (estadoActual === 'GRADUADO') {
                eSelectWrapper.classList.add('d-none');
                btnEstado.disabled = true;
                eAvisoFlujo.className = 'alert alert-success border-0 small mb-0';
                eAvisoFlujo.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i>El alumno ya está graduado.';
                return;
            }

            if (estadoActual === 'EN_DEFENSA') {
                eSelectWrapper.classList.add('d-none');
                eResultadoWrapper.classList.remove('d-none');
                actualizarAvisoFlujo('EN_DEFENSA');
                return;
            }

            if (estadoActual === 'REPROBADO') {
                if (intentosActual >= 2) {
                    eSelectWrapper.classList.add('d-none');
                    btnEstado.disabled = true;
                    eAvisoFlujo.className = 'alert alert-danger border-0 small mb-0';
                    eAvisoFlujo.innerHTML = `
                        <i class="bi bi-x-circle-fill me-1"></i>
                        <strong>Proceso cerrado.</strong>
                        El alumno reprobó dos veces${notaActual ? ` (última nota: ${notaActual})` : ''}.
                        Debe iniciar un nuevo proceso desde cero.
                    `;
                    return;
                }

                // Primera reprobación → permitir EN_DEFENSA con aviso según modalidad
                eSelect.disabled = false;

                if (modalidadActual === 'PRE_ESPECIALIZACION') {
                    eAvisoFlujo.className = 'alert alert-warning border-0 small mb-0';
                    eAvisoFlujo.innerHTML = `
                        <i class="bi bi-exclamation-triangle-fill me-1"></i>
                        <strong>Primera reprobación (Pre-especialización).</strong>
                        El alumno debe cancelar el arancel <strong>TRA-017</strong> (Examen de Suficiencia, $100)
                        para presentar la recuperación en EN_DEFENSA.
                    `;
                } else {
                    eAvisoFlujo.className = 'alert alert-warning border-0 small mb-0';
                    eAvisoFlujo.innerHTML = `
                        <i class="bi bi-exclamation-triangle-fill me-1"></i>
                        <strong>Primera reprobación (Tesis).</strong>
                        El alumno tiene derecho a una segunda convocatoria.
                        Al pasar a EN_DEFENSA se reprograma la defensa.
                    `;
                }
                actualizarAvisoFlujo('');
                return;
            }

            eSelect.disabled = false;
            actualizarAvisoFlujo('');
        });

        eSelect.addEventListener('change', function () {
            const valor = this.value;
            eEstadoFinal.value = valor;

            ePagoWrapper.classList.toggle('d-none', valor !== 'GRADUADO');
            if (valor === 'GRADUADO' && alumnoActual && modalidadActual) {
                cargarPagosDerechosGrado(alumnoActual, modalidadActual);
            }

            actualizarAvisoFlujo(valor);
        });

        eResultado.addEventListener('change', function () {
            const valor = this.value;

            eNotaWrapper.classList.toggle('d-none', !valor);

            if (valor === 'APROBADO') {
                eNota.min = 6.0;
                eNotaHelp.textContent = 'Nota entre 6.0 y 10.0 para aprobar.';
            } else if (valor === 'REPROBADO') {
                eNota.min = 0;
                eNotaHelp.textContent = 'Nota entre 0.0 y 5.99 para reprobar.';
            }

            eEstadoFinal.value = valor;
            actualizarAvisoFlujo(valor);
        });

        formEstado.addEventListener('submit', function (e) {
            if (!eEstadoFinal.value) {
                e.preventDefault();
                if (estadoActual === 'EN_DEFENSA') eResultado.focus();
                else eSelect.focus();
                return;
            }

            if (['APROBADO', 'REPROBADO'].includes(eEstadoFinal.value)) {
                const nota = parseFloat(eNota.value);

                if (isNaN(nota) || nota < 0 || nota > 10) {
                    e.preventDefault();
                    eNota.focus();
                    return;
                }

                if (eEstadoFinal.value === 'APROBADO' && nota < 6.0) {
                    e.preventDefault();
                    alert('Para APROBAR, la nota debe ser mayor o igual a 6.0.');
                    eNota.focus();
                    return;
                }

                if (eEstadoFinal.value === 'REPROBADO' && nota >= 6.0) {
                    e.preventDefault();
                    alert('Para REPROBAR, la nota debe ser menor a 6.0.');
                    eNota.focus();
                    return;
                }
            }

            if (eEstadoFinal.value === 'GRADUADO' && !ePagoSelect.value) {
                e.preventDefault();
                ePagoSelect.focus();
                return;
            }

            btnEstado.disabled = true;
            btnEstado.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Actualizando...';
        });
    }

    function actualizarAvisoFlujo(valor) {
        let mensaje = '';

        if (valor === 'EN_DESARROLLO') {
            if (modalidadActual === 'PRE_ESPECIALIZACION') {
                mensaje = `
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                    <strong>Validaciones al pasar a EN_DESARROLLO (Pre-especialización):</strong>
                    <ul class="mb-0 ps-3 mt-1">
                        <li>Arancel de matrícula <strong>PRE-001</strong> pagado.</li>
                    </ul>
                `;
            } else {
                mensaje = `
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                    <strong>Validaciones al pasar a EN_DESARROLLO (Tesis):</strong>
                    <ul class="mb-0 ps-3 mt-1">
                        <li>Arancel de entrega/revisión de anteproyecto pagado.</li>
                    </ul>
                `;
            }
        } else if (valor === 'EN_DEFENSA') {
            if (estadoActual === 'REPROBADO') {
                if (modalidadActual === 'PRE_ESPECIALIZACION') {
                    mensaje = `
                        <i class="bi bi-exclamation-triangle-fill me-1"></i>
                        <strong>Recuperación de pre-especialización:</strong>
                        <ul class="mb-0 ps-3 mt-1">
                            <li>Arancel <strong>TRA-017</strong> (Examen de Suficiencia, $100) pagado.</li>
                        </ul>
                    `;
                } else {
                    mensaje = `
                        <i class="bi bi-exclamation-triangle-fill me-1"></i>
                        <strong>Segunda convocatoria de tesis:</strong>
                        <ul class="mb-0 ps-3 mt-1">
                            <li>El alumno ya tiene derecho a esta segunda oportunidad.</li>
                        </ul>
                    `;
                }
            } else {
                if (modalidadActual === 'PRE_ESPECIALIZACION') {
                    mensaje = `
                        <i class="bi bi-exclamation-triangle-fill me-1"></i>
                        <strong>Validaciones al pasar a EN_DEFENSA (Pre-especialización):</strong>
                        <ul class="mb-0 ps-3 mt-1">
                            <li>12 mensualidades <strong>PRE-002</strong> pagadas.</li>
                        </ul>
                    `;
                } else {
                    mensaje = `
                        <i class="bi bi-exclamation-triangle-fill me-1"></i>
                        <strong>Validaciones al pasar a EN_DEFENSA (Tesis):</strong>
                        <ul class="mb-0 ps-3 mt-1">
                            <li>12 cuotas de asesoría pagadas.</li>
                            <li>Arancel de revisión de tesis pagado.</li>
                            <li>Arancel de defensa pagado.</li>
                        </ul>
                    `;
                }
            }
        } else if (valor === 'APROBADO') {
            mensaje = `<i class="bi bi-info-circle me-1"></i>Ingresa una nota entre <strong>6.0 y 10.0</strong> para aprobar.`;
        } else if (valor === 'REPROBADO') {
            mensaje = `<i class="bi bi-info-circle me-1"></i>Ingresa una nota <strong>menor a 6.0</strong> para reprobar.`;
        } else if (valor === 'GRADUADO') {
            if (modalidadActual === 'PRE_ESPECIALIZACION') {
                mensaje = `
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                    <strong>Validaciones al pasar a GRADUADO (Pre-especialización):</strong>
                    <ul class="mb-0 ps-3 mt-1">
                        <li>Arancel de derechos de grado <strong>PRE-003</strong> pagado.</li>
                    </ul>
                `;
            } else {
                mensaje = `
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                    <strong>Validaciones al pasar a GRADUADO (Tesis):</strong>
                    <ul class="mb-0 ps-3 mt-1">
                        <li>Arancel de derechos de grado pagado.</li>
                    </ul>
                `;
            }
        } else {
            mensaje = `<i class="bi bi-info-circle me-1"></i>Flujo: <strong>INSCRITO → EN_DESARROLLO → EN_DEFENSA → APROBADO → GRADUADO</strong>.`;
        }

        eAvisoFlujo.innerHTML = mensaje;
        eAvisoFlujo.className = 'alert border-0 small mb-0 ' + (valor ? 'alert-warning' : 'alert-info');
    }

    function cargarPagosDerechosGrado(alumnoId, modalidad) {
        const url = `/admin-academico/graduacion/${alumnoId}/pagos-disponibles/${modalidad}`;
        ePagoSelect.innerHTML = '<option value="">Cargando...</option>';
        ePagoSelect.disabled = true;

        fetch(url, { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(data => {
                ePagoSelect.innerHTML = '<option value="">Selecciona un pago</option>';

                if (!data.pagos || data.pagos.length === 0) {
                    const opt = document.createElement('option');
                    opt.textContent = `No hay pagos registrados (${data.codigo || ''})`;
                    opt.disabled = true;
                    ePagoSelect.appendChild(opt);
                    btnEstado.disabled = true;
                } else {
                    data.pagos.forEach(p => {
                        const opt = document.createElement('option');
                        opt.value = p.pago_id;
                        opt.textContent = `${p.numero_recibo} · ${formatearFecha(p.fecha_pago)} · $${p.total}`;
                        ePagoSelect.appendChild(opt);
                    });
                    btnEstado.disabled = false;
                }
                ePagoSelect.disabled = false;
            })
            .catch(() => {
                ePagoSelect.innerHTML = '<option value="">Error al cargar</option>';
                ePagoSelect.disabled = false;
                btnEstado.disabled = true;
            });
    }

    function formatearFecha(fecha) {
        if (!fecha) return '—';
        const d = new Date(fecha);
        return d.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric' });
    }

    /* ═══════════════════════════════════════════════════════
     *  MÓDULO 3 · MODAL HISTORIAL
     * ═══════════════════════════════════════════════════════ */
    const modalHistorial = document.getElementById('modalHistorial');
    const hLista         = document.getElementById('hLista');

    if (modalHistorial && hLista) {
        modalHistorial.addEventListener('show.bs.modal', function (event) {
            const btn = event.relatedTarget;
            if (!btn) return;

            const alumnoId = btn.getAttribute('data-alumno-id');
            const nombre   = btn.getAttribute('data-nombre');

            document.getElementById('hNombre').textContent = nombre;
            hLista.innerHTML = '<div class="text-center text-muted py-4"><span class="spinner-border spinner-border-sm me-2"></span>Cargando...</div>';

            fetch(`/admin-academico/graduacion/${alumnoId}/historial`, {
                headers: { 'Accept': 'application/json' }
            })
                .then(r => r.json())
                .then(data => {
                    hLista.innerHTML = '';

                    if (!data.procesos || data.procesos.length === 0) {
                        hLista.innerHTML = '<div class="text-center text-muted py-4"><i class="bi bi-inbox fs-1 d-block mb-2"></i>No hay procesos registrados.</div>';
                        return;
                    }

                    data.procesos.forEach((p, idx) => {
                        const row = document.createElement('div');
                        row.className = 'border rounded p-3 mb-2 ' + (p.es_activo ? 'bg-success bg-opacity-10 border-success' : 'bg-light');

                        row.innerHTML = `
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <span class="badge bg-info ${p.modalidad_badge} me-1">${p.modalidad}</span>
                                    <span class="badge bg-info ${p.estado_badge}">${p.estado}</span>
                                    ${p.es_activo ? '<span class="badge bg-success ms-1">Activo</span>' : '<span class="badge bg-secondary ms-1">Histórico</span>'}
                                </div>
                                <small class="text-muted">Intento #${idx + 1}</small>
                            </div>
                            <div class="small mb-2"><strong>Tema:</strong> ${p.titulo}</div>
                            <div class="row g-2 small">
                                <div class="col-4">
                                    <span class="text-muted d-block">Intentos defensa</span>
                                    <strong>${p.intentos} / 2</strong>
                                </div>
                                <div class="col-4">
                                    <span class="text-muted d-block">Nota</span>
                                    <strong>${p.nota ?? '—'}</strong>
                                </div>
                                <div class="col-4">
                                    <span class="text-muted d-block">Defensa</span>
                                    <strong>${p.fecha_defensa}</strong>
                                </div>
                            </div>
                        `;
                        hLista.appendChild(row);
                    });
                })
                .catch(() => {
                    hLista.innerHTML = '<div class="text-center text-danger py-4">Error al cargar el historial.</div>';
                });
        });
    }

    /* ═══════════════════════════════════════════════════════
     *  MÓDULO 4 · TABS: mantener el tab activo al recargar
     * ═══════════════════════════════════════════════════════ */
    const urlParams = new URLSearchParams(window.location.search);
    const tabParam  = urlParams.get('tab');

    if (tabParam) {
        const tabBtn = document.querySelector(`[data-bs-target="#tab-${tabParam}"]`);
        if (tabBtn) {
            const tab = new bootstrap.Tab(tabBtn);
            tab.show();
        }
    }

    // Actualizar query param al cambiar de tab
    document.querySelectorAll('[data-bs-toggle="tab"]').forEach(btn => {
        btn.addEventListener('shown.bs.tab', function (e) {
            const target = e.target.getAttribute('data-bs-target');
            const tabName = target.replace('#tab-', '');
            const url = new URL(window.location.href);
            url.searchParams.set('tab', tabName);
            window.history.replaceState({}, '', url);
        });
    });
});
</script>
@endpush
@endsection