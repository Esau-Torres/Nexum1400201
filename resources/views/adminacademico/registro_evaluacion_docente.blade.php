@extends('layouts.app')

@section('title', 'Registro de Evaluaciones Docentes')

@section('content')
<div class="container-fluid p-4 p-lg-5">

    {{-- ============================================================
         HEADER
         ============================================================ --}}
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-md-between gap-3 p-3 mb-4 rounded-4 card">
        <div class="d-flex align-items-center">
            <div class="px-3 py-2 rounded-3 bg-danger bg-opacity-10" style="width:48px;height:48px;">
                <i class="bi bi-clipboard2-data fs-4 text-danger"></i>
            </div>
            <div class="px-3">
                <h1 class="h4 fw-bold mb-0">EVALUACIONES DOCENTES</h1>
                <p class="text-muted mb-0 text-medium">
                    Gestión de enlaces de encuestas por sección y ciclo
                </p>
            </div>
        </div>
        <div class="d-flex gap-2 align-items-end flex-wrap">
            <a href="{{ route('home') }}"
               class="btn bg-danger-subtle text-danger border border-danger px-3 py-2">
                <i class="fa-solid fa-home me-1"></i> Inicio
            </a>
        </div>
    </div>

    {{-- ============================================================
         FILTROS
         ============================================================ --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin-academico.evaluaciones.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-6">
                        <label for="filtro-ciclo" class="form-label small fw-medium text-muted">
                            <i class="bi bi-calendar3 me-1"></i> Filtrar por ciclo lectivo
                        </label>
                        <select name="id_ciclo_lectivo" id="filtro-ciclo"
                                class="form-select" onchange="this.form.submit()">
                            <option value="">Todos los ciclos</option>
                            @foreach($ciclosLectivos as $c)
                                @php
                                    $badge = match($c->estado->value) {
                                        'MATRICULA_ABIERTA' => ' · Matrícula abierta',
                                        'ACTIVO'            => ' · Activo',
                                        'CONFIGURACION'     => ' · En configuración',
                                        'CERRADO'           => ' · Cerrado',
                                        default             => '',
                                    };
                                @endphp
                                <option value="{{ $c->ciclo_lectivo_id }}"
                                    {{ $idCicloFiltro == $c->ciclo_lectivo_id ? 'selected' : '' }}>
                                    {{ $c->nombre_ciclo }}{{ $badge }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 text-md-end text-medium">
                        <a href="{{ route('admin-academico.evaluaciones.index') }}"
                        class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-clockwise me-1"></i> Quitar filtros
                        </a>
                        <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalCrearEvaluacion">
                            <i class="bi bi-plus-circle me-1"></i> Nueva encuesta
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ============================================================
         LISTADO
         ============================================================ --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h6 fw-bold mb-0">
            <i class="bi bi-list-ul text-danger me-1"></i>
            Encuestas registradas
            <span class="badge bg-secondary ms-1">{{ $evaluaciones->count() }}</span>
        </h2>
    </div>

    <div class="card border-0 shadow-sm">
        <div>
            <table class="table table-hover align-middle mb-0 text-medium" id="tabla-evaluaciones">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Ciclo</th>
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
                            $seccion = $ev->seccion;
                            $materia = $seccion?->materia;
                            $docente = $seccion?->docente?->user;
                        @endphp
                        <tr>
                            <td class="ps-4 small">
                                {{ $ev->cicloLectivo->nombre_ciclo ?? '—' }}
                            </td>
                            <td>
                                <div class="fw-semibold small">{{ $materia->nombre ?? '—' }}</div>
                                <small class="text-muted">{{ $materia->codigo_materia ?? '' }}</small>
                            </td>
                            <td>
                                <span class="badge bg-danger">
                                    Grupo {{ $seccion->nombre_grupo ?? '—' }}
                                </span>
                            </td>
                            <td class="small">
                                {{ $docente->name ?? '—' }}
                            </td>
                            <td>
                                <a href="{{ $ev->url }}"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-box-arrow-up-right me-1"></i> Abrir encuesta
                                </a>
                            </td>
                            <td class="small text-muted">
                                {{ $ev->fecha_registro?->format('d/m/Y H:i') ?? '—' }}
                                <br>
                                <small class="text-muted">
                                    por {{ $ev->registradoPor?->name ?? '—' }}
                                </small>
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalEditarEvaluacion{{ $ev->evaluacion_docente_id }}"
                                            title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-outline-danger"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalEliminarEvaluacion{{ $ev->evaluacion_docente_id }}"
                                            title="Eliminar">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="bi bi-clipboard-x fs-2 d-block mb-2"></i>
                                No hay encuestas registradas
                                {{ $idCicloFiltro ? 'en este ciclo' : '' }}.
                                <div class="mt-3">
                                    <button class="btn btn-sm btn-danger"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalCrearEvaluacion">
                                        <i class="bi bi-plus-circle me-1"></i>
                                        Registrar la primera encuesta
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Nota informativa --}}
    <div class="alert alert-info small border-0 mt-4">
        <i class="bi bi-info-circle-fill me-1"></i>
        <strong>Cómo funciona:</strong>
        Por cada <strong>sección</strong> y <strong>ciclo lectivo</strong> se puede registrar una única encuesta.
        Una vez registrada, los estudiantes de esa sección verán el botón <em>"Evaluar docente"</em>
        en su vista de desempeño.
        La encuesta debe estar alojada en una URL pública (Google Forms, Microsoft Forms, etc.).
    </div>

</div>

{{-- ================================================================
     MODAL: CREAR EVALUACIÓN
     ================================================================ --}}
<div class="modal fade" id="modalCrearEvaluacion" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-white border-bottom">
                <h5 class="modal-title fw-bold">Nueva encuesta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin-academico.evaluaciones.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">

                    <div class="mb-3">
                        @if($idCicloFiltro)
                            <input type="hidden" name="id_ciclo_lectivo" value="{{ $idCicloFiltro ?? $cicloDefault?->ciclo_lectivo_id }}">
                        @endif
                    </div>

                    <div class="mb-3">
                        <label for="crear-id-seccion" class="form-label small fw-medium">
                            Sección <span class="text-danger">*</span>
                        </label>
                        <select name="id_seccion"
                                id="crear-id-seccion"
                                class="form-select"
                                required>
                            <option value="">Primero seleccioná un ciclo...</option>
                        </select>
                        <div class="form-text small">
                            Solo se muestran secciones del ciclo seleccionado que aún no tienen encuesta.
                        </div>
                    </div>

                    <div class="mb-0">
                        <label for="crear-url" class="form-label small fw-medium">
                            URL de la encuesta <span class="text-danger">*</span>
                        </label>
                        <input type="url"
                               name="url"
                               id="crear-url"
                               class="form-control"
                               placeholder="https://docs.google.com/forms/d/e/..."
                               required>
                        <div class="form-text small">
                            Debe comenzar con <code>http://</code> o <code>https://</code>
                        </div>
                    </div>

                </div>
                <div class="modal-footer bg-light border-top-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-check-circle me-1"></i> Crear encuesta
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ================================================================
     MODALES: EDITAR / ELIMINAR (uno por evaluación)
     ================================================================ --}}
@foreach($evaluaciones as $ev)
    {{-- EDITAR --}}
    <div class="modal fade" id="modalEditarEvaluacion{{ $ev->evaluacion_docente_id }}" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-white border-bottom">
                    <h5 class="modal-title fw-bold">
                        Editar encuesta
                        <small class="text-muted fw-normal">
                            — {{ $ev->seccion?->materia?->nombre ?? '—' }}
                        </small>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin-academico.evaluaciones.update', $ev) }}" method="POST">
                    @csrf @method('PUT')
                    <div class="modal-body p-4">

                        {{-- Info de la sección y ciclo (solo lectura, no editables) --}}
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-medium text-muted">Ciclo lectivo</label>
                                <input type="text"
                                    class="form-control bg-light"
                                    readonly
                                    value="{{ $ev->cicloLectivo?->nombre_ciclo ?? '—' }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-medium text-muted">Sección</label>
                                <input type="text"
                                    class="form-control bg-light"
                                    readonly
                                    value="{{ $ev->seccion?->materia?->nombre ?? '—' }} — Grupo {{ $ev->seccion?->nombre_grupo ?? '—' }}">
                            </div>
                        </div>

                        <div class="alert alert-light border small mb-3">
                            <i class="bi bi-info-circle me-1"></i>
                            Solo podés cambiar la URL. Si necesitás mover la encuesta a otra sección o ciclo,
                            eliminá esta y creá una nueva.
                        </div>

                        {{-- URL editable, con ID único por evaluación --}}
                        <div class="mb-0">
                            <label for="edit-url-{{ $ev->evaluacion_docente_id }}"
                                class="form-label small fw-medium">
                                URL de la encuesta <span class="text-danger">*</span>
                            </label>
                            <input type="url"
                                name="url"
                                id="edit-url-{{ $ev->evaluacion_docente_id }}"
                                class="form-control"
                                value="{{ old('url', $ev->url) }}"
                                placeholder="https://docs.google.com/forms/d/e/..."
                                required>
                            <div class="form-text small">
                                Debe comenzar con <code>http://</code> o <code>https://</code>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer bg-light border-top-0">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-check-circle me-1"></i> Guardar cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ELIMINAR --}}
    <div class="modal fade" id="modalEliminarEvaluacion{{ $ev->evaluacion_docente_id }}" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-danger text-white border-0">
                    <h5 class="modal-title fw-bold">Eliminar encuesta</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="mb-3">
                        ¿Confirmás eliminar la encuesta de
                        <strong>{{ $ev->seccion?->materia?->nombre ?? '—' }}</strong>
                        — Grupo <strong>{{ $ev->seccion?->nombre_grupo ?? '—' }}</strong>
                        del ciclo <strong>{{ $ev->cicloLectivo?->nombre_ciclo ?? '—' }}</strong>?
                    </p>
                    <div class="alert alert-warning small mb-0">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i>
                        Los estudiantes de esta sección dejarán de ver el botón "Evaluar docente".
                        Esta acción no se puede deshacer.
                    </div>
                </div>
                <div class="modal-footer bg-light border-top-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form action="{{ route('admin-academico.evaluaciones.destroy', $ev) }}" method="POST">
                        @csrf @method('DELETE')
                        @if($idCicloFiltro)
                            <input type="hidden" name="filtro_ciclo" value="{{ $idCicloFiltro }}">
                        @endif
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