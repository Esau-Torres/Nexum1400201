
@extends('layouts.app')

@section('title', 'Registro de Evaluaciones Docentes')

@push('styles')
    @vite('resources/css/evaluaciones-docentes.css')
@endpush

@section('content')
<div class="container-fluid p-4 p-lg-5 evaluaciones-view">

    {{-- ============================================================
         HEADER
         ============================================================ --}}
    <div class="ev-header mb-4">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-md-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="ev-header-icon">
                    <i class="bi bi-clipboard2-data"></i>
                </div>

                <div>
                    <h1 class="mb-1">EVALUACIONES DOCENTES</h1>
                    <p class="mb-0">
                        Gestión de enlaces de encuestas por sección y ciclo lectivo
                    </p>
                </div>
            </div>

            <div class="ev-header-actions">
                <a href="{{ route('home') }}"
                   class="btn btn-outline-secondary px-3 py-2">
                    <i class="fa-solid fa-house me-1"></i>
                    Inicio
                </a>
            </div>
        </div>
    </div>

    {{-- ============================================================
         FILTROS
         ============================================================ --}}
    <div class="ev-filter-card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin-academico.evaluaciones.index') }}">
                <div class="row g-3 align-items-end">

                    {{-- FILTRO CICLO --}}
                    <div class="col-md-6">
                        <label for="filtro-ciclo" class="form-label">
                            <i class="bi bi-calendar3 me-1"></i>
                            Filtrar por ciclo lectivo
                        </label>

                        <select name="id_ciclo_lectivo"
                                id="filtro-ciclo"
                                class="form-select"
                                onchange="this.form.submit()">

                            <option value="">Todos los ciclos</option>

                            @foreach($ciclosLectivos as $c)
                                @php
                                    $badge = match($c->estado->value) {
                                        'MATRICULA_ABIERTA' => ' · Matrícula abierta',
                                        'ACTIVO'            => ' · Activo',
                                        'CONFIGURACION'      => ' · En configuración',
                                        'CERRADO'            => ' · Cerrado',
                                        default              => '',
                                    };
                                @endphp

                                <option value="{{ $c->ciclo_lectivo_id }}"
                                    {{ $idCicloFiltro == $c->ciclo_lectivo_id ? 'selected' : '' }}>
                                    {{ $c->nombre_ciclo }}{{ $badge }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- ACCIONES --}}
                    <div class="col-md-6">
                        <div class="ev-filter-actions">

                            <a href="{{ route('admin-academico.evaluaciones.index') }}"
                               class="btn btn-outline-secondary px-3 py-2">
                                <i class="bi bi-arrow-clockwise me-1"></i>
                                Quitar filtros
                            </a>

                            {{-- NUEVA ENCUESTA --}}
                            @if($puedeGestionar)
                                <button type="button"
                                        class="btn btn-danger px-3 py-2"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalCrearEvaluacion">
                                    <i class="bi bi-plus-circle me-1"></i>
                                    Nueva encuesta
                                </button>
                            @else
                                <button type="button"
                                        class="btn btn-secondary px-3 py-2"
                                        disabled
                                        title="Solo se pueden crear encuestas en ciclos ACTIVOS">
                                    <i class="bi bi-lock-fill me-1"></i>
                                    Nueva encuesta
                                </button>
                            @endif

                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ============================================================
         AVISO: GESTIÓN BLOQUEADA
         ============================================================ --}}
    @if($idCicloFiltro && !$puedeGestionar)
        <div class="alert alert-warning border mb-4 d-flex align-items-start gap-3">
            <i class="bi bi-lock-fill fs-5"></i>

            <div>
                <strong class="d-block mb-1">
                    Gestión bloqueada para este ciclo
                </strong>

                <span class="small">
                    Solo se pueden crear, editar o eliminar encuestas en ciclos
                    con estado <strong>ACTIVO</strong>. Este ciclo está en otro estado.
                </span>
            </div>
        </div>
    @endif

    {{-- ============================================================
         ENCABEZADO DEL LISTADO
         ============================================================ --}}
    <div class="ev-list-heading">
        <h2 class="mb-0">
            <span class="ev-list-icon">
                <i class="bi bi-list-ul"></i>
            </span>

            Encuestas registradas
        </h2>

        <span class="ev-counter">
            {{ $evaluaciones->count() }}
        </span>
    </div>

    {{-- ============================================================
         TABLA DE EVALUACIONES
         ============================================================ --}}
    <div class="ev-table-card mb-4">
        <div class="ev-table-container">

            <table class="table table-hover align-middle mb-0 text-medium"
                   id="tabla-evaluaciones">

                <thead>
                    <tr>
                        <th>Ciclo</th>
                        <th>Materia</th>
                        <th>Grupo</th>
                        <th>Docente</th>
                        <th>URL</th>
                        <th>Fecha registro</th>
                        <th class="text-end pe-4">Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($evaluaciones as $ev)

                        @php
                            $seccion    = $ev->seccion;
                            $materia    = $seccion?->materia;
                            $docente    = $seccion?->docente?->user;
                            $cicloEv    = $ev->cicloLectivo;
                            $evEditable = $cicloEv && $cicloEv->estado === \App\Enums\EstadoCiclo::ACTIVO;
                        @endphp

                        <tr>

                            {{-- CICLO --}}
                            <td>
                                <span class="small">
                                    {{ $cicloEv->nombre_ciclo ?? '—' }}
                                </span>
                            </td>

                            {{-- MATERIA --}}
                            <td>
                                <div class="fw-semibold">
                                    {{ $materia->nombre ?? '—' }}
                                </div>

                                <small class="text-muted">
                                    {{ $materia->codigo_materia ?? '' }}
                                </small>
                            </td>

                            {{-- GRUPO --}}
                            <td>
                                <span class="ev-group-badge">
                                    Grupo {{ $seccion->nombre_grupo ?? '—' }}
                                </span>
                            </td>

                            {{-- DOCENTE --}}
                            <td>
                                <span class="small">
                                    {{ $docente->name ?? '—' }}
                                </span>
                            </td>

                            {{-- URL --}}
                            <td>
                                <a href="{{ $ev->url }}"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-box-arrow-up-right me-1"></i>
                                    Abrir encuesta
                                </a>
                            </td>

                            {{-- FECHA DE REGISTRO --}}
                            <td>
                                <span class="small text-muted">
                                    {{ $ev->fecha_registro?->format('d/m/Y H:i') ?? '—' }}
                                </span>

                                <br>

                                <small class="text-muted">
                                    por {{ $ev->registradoPor?->name ?? '—' }}
                                </small>
                            </td>

                            {{-- ACCIONES --}}
                            <td class="text-end pe-4">

                                @if($evEditable)

                                    <div class="ev-actions">

                                        {{-- EDITAR --}}
                                        <button type="button"
                                                class="btn btn-outline-primary"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalEditarEvaluacion{{ $ev->evaluacion_docente_id }}"
                                                title="Editar encuesta"
                                                aria-label="Editar encuesta">
                                            <i class="bi bi-pencil"></i>
                                        </button>

                                        {{-- ELIMINAR --}}
                                        <button type="button"
                                                class="btn btn-outline-danger"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalEliminarEvaluacion{{ $ev->evaluacion_docente_id }}"
                                                title="Eliminar encuesta"
                                                aria-label="Eliminar encuesta">
                                            <i class="bi bi-trash"></i>
                                        </button>

                                    </div>

                                @else

                                    <span class="badge bg-secondary-subtle text-secondary border small">
                                        <i class="bi bi-lock-fill me-1"></i>
                                        Ciclo no activo
                                    </span>

                                @endif

                            </td>
                        </tr>

                    @empty

                        {{-- ESTADO VACÍO --}}
                        <tr>
                            <td colspan="7" class="ev-empty-state">

                                <div class="ev-empty-icon">
                                    <i class="bi bi-clipboard-x"></i>
                                </div>

                                <p class="mb-2 fw-semibold">
                                    No hay encuestas registradas
                                    {{ $idCicloFiltro ? 'en este ciclo' : '' }}.
                                </p>

                                @if($puedeGestionar)

                                    <div>
                                        <button type="button"
                                                class="btn btn-sm btn-danger"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalCrearEvaluacion">
                                            <i class="bi bi-plus-circle me-1"></i>
                                            Registrar la primera encuesta
                                        </button>
                                    </div>

                                @else

                                    <div class="small text-muted mt-3">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Seleccioná un ciclo <strong>ACTIVO</strong>
                                        para poder registrar encuestas.
                                    </div>

                                @endif

                            </td>
                        </tr>

                    @endforelse
                </tbody>
            </table>

        </div>
    </div>

    {{-- ============================================================
         NOTA INFORMATIVA
         ============================================================ --}}
    <div class="ev-info-box">

        <div class="ev-info-icon">
            <i class="bi bi-info-circle-fill"></i>
        </div>

        <div class="ev-info-content">

            <strong>Cómo funciona:</strong>

            Por cada <strong>sección</strong> y <strong>ciclo lectivo</strong>
            se puede registrar una única encuesta. Una vez registrada,
            los estudiantes de esa sección verán el botón
            <em>"Evaluar docente"</em> en su vista de desempeño.

            La encuesta debe estar alojada en una URL pública
            (Google Forms, Microsoft Forms, etc.).

            <div class="mt-2 text-danger fw-semibold">
                <i class="bi bi-lock-fill me-1"></i>
                Solo se permite gestionar encuestas cuando el ciclo lectivo
                está en estado ACTIVO.
            </div>

        </div>
    </div>

</div>

{{-- ================================================================
     MODAL: CREAR EVALUACIÓN
     Solo se renderiza si el ciclo filtrado está ACTIVO.
     ================================================================ --}}
@if($puedeGestionar)

    <div class="modal fade evaluaciones-modal"
         id="modalCrearEvaluacion"
         tabindex="-1"
         aria-labelledby="tituloModalCrearEvaluacion"
         aria-hidden="true">

        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">

                <div class="modal-header bg-white border-bottom">
                    <h5 class="modal-title fw-bold" id="tituloModalCrearEvaluacion">
                        <i class="bi bi-plus-circle text-danger me-2"></i>
                        Nueva encuesta
                    </h5>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Cerrar">
                    </button>
                </div>

                <form action="{{ route('admin-academico.evaluaciones.store') }}"
                      method="POST">

                    @csrf

                    <div class="modal-body">

                        @if($idCicloFiltro)
                            <input type="hidden"
                                   name="id_ciclo_lectivo"
                                   value="{{ $idCicloFiltro }}">
                        @endif

                        {{-- SECCIÓN --}}
                        <div class="mb-3">

                            <label for="crear-id-seccion" class="form-label">
                                <i class="bi bi-people me-1"></i>
                                Sección <span class="text-danger">*</span>
                            </label>

                            <select name="id_seccion"
                                    id="crear-id-seccion"
                                    class="form-select"
                                    required>
                                <option value="">Primero seleccioná un ciclo...</option>
                            </select>

                            <div class="form-text">
                                Solo se muestran secciones del ciclo seleccionado
                                que aún no tienen encuesta.
                            </div>
                        </div>

                        {{-- URL --}}
                        <div class="mb-0">

                            <label for="crear-url" class="form-label">
                                <i class="bi bi-link-45deg me-1"></i>
                                URL de la encuesta <span class="text-danger">*</span>
                            </label>

                            <input type="url"
                                   name="url"
                                   id="crear-url"
                                   class="form-control"
                                   placeholder="https://docs.google.com/forms/d/e/..."
                                   required>

                            <div class="form-text">
                                Debe comenzar con <code>http://</code> o <code>https://</code>
                            </div>
                        </div>

                    </div>

                    <div class="modal-footer bg-light border-top-0">

                        <button type="button"
                                class="btn btn-outline-secondary"
                                data-bs-dismiss="modal">
                            Cancelar
                        </button>

                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-check-circle me-1"></i>
                            Crear encuesta
                        </button>

                    </div>
                </form>

            </div>
        </div>
    </div>

@endif

{{-- ================================================================
     MODALES: EDITAR / ELIMINAR
     Uno por evaluación editable.
     Solo se renderizan para ciclos ACTIVOS.
     ================================================================ --}}

@foreach($evaluaciones as $ev)

    @php
        $cicloEv    = $ev->cicloLectivo;
        $evEditable = $cicloEv && $cicloEv->estado === \App\Enums\EstadoCiclo::ACTIVO;
    @endphp

    @if($evEditable)

        {{-- ========================================================
             MODAL: EDITAR
             ======================================================== --}}
        <div class="modal fade evaluaciones-modal"
             id="modalEditarEvaluacion{{ $ev->evaluacion_docente_id }}"
             tabindex="-1"
             aria-labelledby="tituloModalEditar{{ $ev->evaluacion_docente_id }}"
             aria-hidden="true">

            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">

                    <div class="modal-header bg-white border-bottom">

                        <h5 class="modal-title fw-bold"
                            id="tituloModalEditar{{ $ev->evaluacion_docente_id }}">

                            <i class="bi bi-pencil-square text-primary me-2"></i>
                            Editar encuesta

                            <small class="text-muted fw-normal">
                                — {{ $ev->seccion?->materia?->nombre ?? '—' }}
                            </small>

                        </h5>

                        <button type="button"
                                class="btn-close"
                                data-bs-dismiss="modal"
                                aria-label="Cerrar">
                        </button>

                    </div>

                    <form action="{{ route('admin-academico.evaluaciones.update', $ev) }}"
                          method="POST">

                        @csrf
                        @method('PUT')

                        <div class="modal-body">

                            {{-- INFORMACIÓN --}}
                            <div class="row g-3 mb-3">

                                <div class="col-md-6">

                                    <label class="form-label text-muted">
                                        Ciclo lectivo
                                    </label>

                                    <input type="text"
                                           class="form-control bg-light"
                                           readonly
                                           value="{{ $cicloEv?->nombre_ciclo ?? '—' }}">

                                </div>

                                <div class="col-md-6">

                                    <label class="form-label text-muted">
                                        Sección
                                    </label>

                                    <input type="text"
                                           class="form-control bg-light"
                                           readonly
                                           value="{{ $ev->seccion?->materia?->nombre ?? '—' }} — Grupo {{ $ev->seccion?->nombre_grupo ?? '—' }}">

                                </div>

                            </div>

                            {{-- AVISO --}}
                            <div class="alert alert-light border small mb-3">

                                <i class="bi bi-info-circle me-1"></i>

                                Solo podés cambiar la URL. Si necesitás mover
                                la encuesta a otra sección o ciclo, eliminá esta
                                y creá una nueva.

                            </div>

                            {{-- URL --}}
                            <div class="mb-0">

                                <label for="edit-url-{{ $ev->evaluacion_docente_id }}"
                                       class="form-label">
                                    URL de la encuesta
                                    <span class="text-danger">*</span>
                                </label>

                                <input type="url"
                                       name="url"
                                       id="edit-url-{{ $ev->evaluacion_docente_id }}"
                                       class="form-control"
                                       value="{{ old('url', $ev->url) }}"
                                       placeholder="https://docs.google.com/forms/d/e/..."
                                       required>

                                <div class="form-text">
                                    Debe comenzar con <code>http://</code> o <code>https://</code>
                                </div>

                            </div>

                        </div>

                        {{-- FOOTER --}}
                        <div class="modal-footer bg-light border-top-0">

                            <button type="button"
                                    class="btn btn-outline-secondary"
                                    data-bs-dismiss="modal">
                                Cancelar
                            </button>

                            <button type="submit" class="btn btn-danger">
                                <i class="bi bi-check-circle me-1"></i>
                                Guardar cambios
                            </button>

                        </div>

                    </form>

                </div>
            </div>
        </div>

        {{-- ========================================================
             MODAL: ELIMINAR
             ======================================================== --}}
        <div class="modal fade evaluaciones-modal"
             id="modalEliminarEvaluacion{{ $ev->evaluacion_docente_id }}"
             tabindex="-1"
             aria-labelledby="tituloModalEliminar{{ $ev->evaluacion_docente_id }}"
             aria-hidden="true">

            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">

                    <div class="modal-header bg-danger text-white border-0">

                        <h5 class="modal-title fw-bold"
                            id="tituloModalEliminar{{ $ev->evaluacion_docente_id }}">
                            <i class="bi bi-trash me-2"></i>
                            Eliminar encuesta
                        </h5>

                        <button type="button"
                                class="btn-close btn-close-white"
                                data-bs-dismiss="modal"
                                aria-label="Cerrar">
                        </button>

                    </div>

                    <div class="modal-body">

                        <p class="mb-3">

                            ¿Confirmás eliminar la encuesta de

                            <strong>
                                {{ $ev->seccion?->materia?->nombre ?? '—' }}
                            </strong>

                            — Grupo

                            <strong>
                                {{ $ev->seccion?->nombre_grupo ?? '—' }}
                            </strong>

                            del ciclo

                            <strong>
                                {{ $cicloEv?->nombre_ciclo ?? '—' }}
                            </strong>?

                        </p>

                        <div class="alert alert-warning small mb-0">

                            <i class="bi bi-exclamation-triangle-fill me-1"></i>

                            Los estudiantes de esta sección dejarán de ver
                            el botón "Evaluar docente". Esta acción no se puede deshacer.

                        </div>

                    </div>

                    {{-- FOOTER --}}
                    <div class="modal-footer bg-light border-top-0">

                        <button type="button"
                                class="btn btn-outline-secondary"
                                data-bs-dismiss="modal">
                            Cancelar
                        </button>

                        <form action="{{ route('admin-academico.evaluaciones.destroy', $ev) }}"
                              method="POST">

                            @csrf
                            @method('DELETE')

                            @if($idCicloFiltro)
                                <input type="hidden"
                                       name="filtro_ciclo"
                                       value="{{ $idCicloFiltro }}">
                            @endif

                            <button type="submit" class="btn btn-danger">
                                <i class="bi bi-trash me-1"></i>
                                Eliminar
                            </button>

                        </form>

                    </div>

                </div>
            </div>
        </div>

    @endif

@endforeach

@endsection

{{-- ================================================================
     JAVASCRIPT
     ================================================================ --}}
@push('scripts')
<script>
const SECCIONES_POR_CICLO = @json($seccionesPorCiclo);
const CICLO_MODAL_CREAR    = @js($idCicloFiltro ?? $cicloDefault?->ciclo_lectivo_id);

function actualizarSeccionesDisponibles() {
    const select = document.getElementById('crear-id-seccion');

    if (!select) return;

    select.innerHTML = '';

    if (!CICLO_MODAL_CREAR) {
        select.innerHTML = '<option value="">No hay ciclo seleccionado</option>';
        return;
    }

    const secciones = SECCIONES_POR_CICLO[CICLO_MODAL_CREAR] || [];

    if (secciones.length === 0) {
        select.innerHTML = '<option value="">No hay secciones disponibles en este ciclo (todas ya tienen encuesta)</option>';
        return;
    }

    select.innerHTML = '<option value="">Seleccione una sección...</option>';

    secciones.forEach(s => {
        const opt = document.createElement('option');
        opt.value = s.seccion_id;
        opt.textContent = s.label;
        select.appendChild(opt);
    });
}

document.addEventListener('DOMContentLoaded', function () {

    const modalCrear = document.getElementById('modalCrearEvaluacion');

    if (!modalCrear) return;

    modalCrear.addEventListener('shown.bs.modal', function () {
        actualizarSeccionesDisponibles();
    });

});
</script>
@endpush
