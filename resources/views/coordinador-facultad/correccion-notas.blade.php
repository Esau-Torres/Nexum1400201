@extends('layouts.app')

@section('title', $seccion ? 'Corregir Notas - ' . ($seccion->materia?->nombre ?? '') : 'Corrección de Notas')

@section('content')
<div class="container-fluid cn-page" @if($seccion) data-seccion-id="{{ $seccion->seccion_id }}" @endif>
    <div class="cn-shell">

        {{-- =====================================================
             CASO 1: LISTADO DE SECCIONES (INDEX)
             ===================================================== --}}
        @if (!$seccion)

            @php
                // Se conserva el mismo criterio de conteo de estudiantes CURSANDO.
                // El resultado se reutiliza en escritorio y móvil para evitar consultas duplicadas.
                $inscritosPorSeccion = [];
                foreach ($secciones as $seccionItem) {
                    $inscritosPorSeccion[$seccionItem->seccion_id] = $seccionItem
                        ->detallesInscripcion()
                        ->where('estado_materia', 'CURSANDO')
                        ->count();
                }
            @endphp

            {{-- ENCABEZADO PRINCIPAL --}}
            <header class="cn-hero cn-hero-index">
                <div class="cn-hero-main">
                    <div class="cn-eyebrow">
                        <span class="cn-eyebrow-dot"></span>
                        Gestión académica
                    </div>

                    <h1>CORRECÓN DE NOTAS</h1>
                    <p class="cn-hero-description">
                        Administra y revisa las calificaciones registradas en las secciones académicas.
                    </p>

                    <div class="cn-meta-list">
                        @if ($cicloActivo)
                            <span class="cn-meta-pill">
                                <i class="bi bi-calendar3"></i>
                                Ciclo <strong>{{ $cicloActivo->nombre_ciclo }}</strong>
                            </span>
                            <span class="cn-meta-text">
                                <i class="bi bi-collection"></i>
                                {{ $secciones->count() }} sección(es) disponibles
                            </span>
                        @else
                            <span class="cn-meta-pill cn-meta-pill-muted">
                                <i class="bi bi-calendar-x"></i>
                                Sin ciclo activo
                            </span>
                            <span class="cn-meta-text">No hay un ciclo activo en este momento.</span>
                        @endif
                    </div>
                </div>

                <a href="{{ route('home') }}" class="btn cn-home-button">
                    <i class="bi bi-house-door"></i>
                    <span>Inicio</span>
                </a>
            </header>

            {{-- MENSAJES DE SESIÓN --}}
            @if (session('success'))
                <div class="alert cn-alert cn-alert-success" role="alert">
                    <span class="cn-alert-icon"><i class="bi bi-check-lg"></i></span>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="alert cn-alert cn-alert-danger" role="alert">
                    <span class="cn-alert-icon"><i class="bi bi-exclamation-lg"></i></span>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            {{-- LISTADO DE SECCIONES --}}
            <section class="cn-content-section">
                <div class="cn-section-heading">
                    <div>
                        <span class="cn-section-kicker">Panel de trabajo</span>
                        <h2>Secciones disponibles</h2>
                        <p>Selecciona una sección para consultar y corregir sus calificaciones.</p>
                    </div>
                    @if ($secciones->isNotEmpty())
                        <span class="cn-count-pill">
                            <i class="bi bi-layers"></i>
                            {{ $secciones->count() }} disponibles
                        </span>
                    @endif
                </div>

                @if ($secciones->isNotEmpty())
                    {{-- TABLA DE ESCRITORIO --}}
                    <div class="cn-panel cn-desktop-view">
                        <div class="cn-panel-topbar">
                            <div class="cn-panel-topbar-icon"><i class="bi bi-journal-text"></i></div>
                            <div>
                                <h3>Listado de secciones</h3>
                                <p>Información académica y acceso a la revisión.</p>
                            </div>
                        </div>

                        <div class="table-responsive cn-table-wrap">
                            <table class="table align-middle mb-0 cn-sections-table" id="tablacorrecionnotas">
                                <thead>
                                    <tr>
                                        <th>Código</th>
                                        <th>Materia</th>
                                        <th>Grupo</th>
                                        <th>Docente</th>
                                        <th class="text-center">Inscritos</th>
                                        <th class="text-end">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($secciones as $sec)
                                        <tr>
                                            <td>
                                                <span class="cn-code-tag">{{ $sec->materia?->codigo_materia ?? 'S/C' }}</span>
                                            </td>
                                            <td>
                                                <div class="cn-table-primary">{{ $sec->materia?->nombre ?? 'Sin nombre' }}</div>
                                                <small class="cn-table-secondary">{{ $sec->materia?->carrera?->nombre ?? '' }}</small>
                                            </td>
                                            <td><span class="cn-group-tag">{{ $sec->nombre_grupo ?? '—' }}</span></td>
                                            <td>{{ $sec->docente?->usuario?->name ?? '—' }}</td>
                                            <td class="text-center">
                                                <span class="cn-student-count">{{ $inscritosPorSeccion[$sec->seccion_id] ?? 0 }}</span>
                                            </td>
                                            <td class="text-end">
                                                <a href="{{ route('coordinador-facultad.correccion-notas.show', $sec->seccion_id) }}"
                                                   class="btn cn-review-button">
                                                    <i class="bi bi-pencil-square"></i>
                                                    Revisar notas
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- TARJETAS PARA TABLET Y CELULAR --}}
                    <div class="cn-section-card-list cn-mobile-view">
                        @foreach ($secciones as $sec)
                            <article class="cn-section-card">
                                <div class="cn-section-card-head">
                                    <div class="cn-section-card-icon"><i class="bi bi-journal-bookmark"></i></div>
                                    <div class="cn-section-card-title">
                                        <span class="cn-code-tag">{{ $sec->materia?->codigo_materia ?? 'S/C' }}</span>
                                        <h3>{{ $sec->materia?->nombre ?? 'Sin nombre' }}</h3>
                                        @if ($sec->materia?->carrera?->nombre)
                                            <p>{{ $sec->materia->carrera->nombre }}</p>
                                        @endif
                                    </div>
                                </div>

                                <div class="cn-section-card-data">
                                    <div class="cn-data-item">
                                        <span><i class="bi bi-people"></i> Grupo</span>
                                        <strong>{{ $sec->nombre_grupo ?? '—' }}</strong>
                                    </div>
                                    <div class="cn-data-item">
                                        <span><i class="bi bi-person-badge"></i> Docente</span>
                                        <strong>{{ $sec->docente?->usuario?->name ?? '—' }}</strong>
                                    </div>
                                    <div class="cn-data-item cn-data-item-wide">
                                        <span><i class="bi bi-mortarboard"></i> Estudiantes inscritos</span>
                                        <strong>{{ $inscritosPorSeccion[$sec->seccion_id] ?? 0 }}</strong>
                                    </div>
                                </div>

                                <a href="{{ route('coordinador-facultad.correccion-notas.show', $sec->seccion_id) }}"
                                   class="btn cn-review-button cn-review-button-mobile">
                                    <span>Revisar calificaciones</span>
                                    <i class="bi bi-arrow-up-right"></i>
                                </a>
                            </article>
                        @endforeach
                    </div>
                @else
                    <div class="cn-empty-state">
                        <div class="cn-empty-icon"><i class="bi bi-inbox"></i></div>
                        <h3>No hay secciones para mostrar</h3>
                        <p>
                            @if ($cicloActivo)
                                No hay secciones registradas en el ciclo activo.
                            @else
                                No hay ciclo activo para mostrar secciones.
                            @endif
                        </p>
                    </div>
                @endif
            </section>

        {{-- =====================================================
             CASO 2: DETALLE DE SECCIÓN (SHOW)
             ===================================================== --}}
        @else

            {{-- ENCABEZADO DEL DETALLE --}}
            <header class="cn-hero cn-hero-detail">
                <div class="cn-hero-main">
                    <a href="{{ route('coordinador-facultad.correccion-notas.index') }}"
                       class="cn-back-link">
                        <i class="bi bi-arrow-left"></i>
                        Volver al listado
                    </a>

                    <div class="cn-detail-title-row">
                        <div>
                            <div class="cn-detail-code">{{ $seccion->materia?->codigo_materia ?? 'S/C' }}</div>
                            <h1>{{ $seccion->materia?->nombre ?? 'Sin nombre' }}</h1>
                        </div>
                        <div class="cn-student-total">
                            <strong>{{ $estudiantes->count() }}</strong>
                            <span>Estudiantes</span>
                        </div>
                    </div>

                    <div class="cn-meta-list cn-detail-meta">
                        <span class="cn-meta-pill">
                            <i class="bi bi-person-badge"></i>
                            {{ $seccion->docente?->usuario?->name ?? '—' }}
                        </span>
                        <span class="cn-meta-pill">
                            <i class="bi bi-people"></i>
                            Grupo {{ $seccion->nombre_grupo ?? '—' }}
                        </span>
                        <span class="cn-meta-pill">
                            <i class="bi bi-calendar3"></i>
                            {{ $seccion->cicloLectivo?->nombre_ciclo ?? 'Sin ciclo' }}
                        </span>
                    </div>
                </div>
            </header>

            {{-- TABLA Y TARJETAS DE CALIFICACIONES --}}
            <section class="cn-content-section cn-detail-section">
                <div class="cn-section-heading cn-detail-section-heading">
                    <div>
                        <span class="cn-section-kicker">Registro académico</span>
                        <h2>Calificaciones por estudiante</h2>
                        <p>Selecciona el botón de edición junto a una nota para registrar una corrección.</p>
                    </div>
                    <span class="cn-count-pill">
                        <i class="bi bi-people"></i>
                        {{ $estudiantes->count() }} estudiantes
                    </span>
                </div>

                @if ($estudiantes->isNotEmpty())
                    {{-- TABLA DE ESCRITORIO --}}
                    <div class="cn-panel cn-desktop-view">
                        <div class="cn-table-hint">
                            <i class="bi bi-arrows-angle-expand"></i>
                            Desliza horizontalmente para consultar todas las evaluaciones.
                        </div>
                        <div class="table-responsive cn-table-wrap cn-grades-table-wrap">
                            <table class="table align-middle mb-0 cn-grades-table" id="tablacambionotas">
                                <thead>
                                    <tr>
                                        <th class="cn-student-column">Estudiante</th>
                                        @foreach ($evaluacionesPorPeriodo as $periodoData)
                                            @foreach ($periodoData['categorias'] as $categoria)
                                                @foreach ($categoria->evaluaciones as $evaluacion)
                                                    <th class="text-center">
                                                        <div>{{ $evaluacion->nombre_evaluacion }}</div>
                                                        <small>
                                                            {{ $categoria->tipo_categoria }}
                                                            · P{{ $periodoData['periodo']->numero_periodo }}
                                                        </small>
                                                    </th>
                                                @endforeach
                                            @endforeach
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($estudiantes as $est)
                                        <tr>
                                            <td class="cn-student-column">
                                                <div class="cn-student-cell">
                                                    <span class="cn-avatar-initials">
                                                        {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($est->nombre, 0, 1)) }}
                                                    </span>
                                                    <span class="cn-student-identity">
                                                        <strong>{{ $est->nombre }}</strong>
                                                        <small>{{ $est->codigo }}</small>
                                                    </span>
                                                </div>
                                            </td>

                                            @foreach ($evaluacionesPorPeriodo as $periodoData)
                                                @foreach ($periodoData['categorias'] as $categoria)
                                                    @foreach ($categoria->evaluaciones as $evaluacion)
                                                        @php
                                                            $n = $est->notas[$evaluacion->evaluacion_id] ?? null;
                                                        @endphp
                                                        <td class="text-center">
                                                            @if ($n)
                                                                <div class="cn-grade-cell">
                                                                    <span class="cn-grade-value">{{ number_format($n['nota'], 1) }}</span>
                                                                    @if ($n['es_diferida'])
                                                                        <span class="cn-deferred-tag" title="Evaluación diferida">D</span>
                                                                    @endif
                                                                    <button type="button"
                                                                            class="btn cn-edit-grade-button"
                                                                            data-bs-toggle="modal"
                                                                            data-bs-target="#modalCorregir"
                                                                            data-calificacion-id="{{ $n['calificacion_id'] }}"
                                                                            data-nota-actual="{{ $n['nota'] }}"
                                                                            data-evaluacion="{{ $evaluacion->nombre_evaluacion }}"
                                                                            data-estudiante="{{ $est->nombre }}"
                                                                            data-action="{{ route('coordinador-facultad.correccion-notas.update', [$seccion->seccion_id, $n['calificacion_id']]) }}"
                                                                            title="Corregir nota"
                                                                            aria-label="Corregir nota de {{ $est->nombre }} en {{ $evaluacion->nombre_evaluacion }}">
                                                                        <i class="bi bi-pencil-square"></i>
                                                                    </button>
                                                                </div>
                                                            @else
                                                                <span class="cn-no-grade">—</span>
                                                            @endif
                                                        </td>
                                                    @endforeach
                                                @endforeach
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- TARJETAS REALES PARA TABLET Y CELULAR --}}
                    <div class="cn-student-card-list cn-mobile-view">
                        @foreach ($estudiantes as $est)
                            <article class="cn-student-card">
                                <div class="cn-student-card-header">
                                    <span class="cn-avatar-initials cn-avatar-large">
                                        {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($est->nombre, 0, 1)) }}
                                    </span>
                                    <div class="cn-student-card-identity">
                                        <h3>{{ $est->nombre }}</h3>
                                        <span><i class="bi bi-card-text"></i> {{ $est->codigo }}</span>
                                    </div>
                                    <span class="cn-student-index">#{{ $loop->iteration }}</span>
                                </div>

                                <div class="cn-student-card-divider"></div>

                                <div class="cn-mobile-evaluations-heading">
                                    <span>Evaluaciones</span>
                                    <small>Notas registradas y pendientes</small>
                                </div>

                                <div class="cn-mobile-evaluations">
                                    @foreach ($evaluacionesPorPeriodo as $periodoData)
                                        @foreach ($periodoData['categorias'] as $categoria)
                                            @foreach ($categoria->evaluaciones as $evaluacion)
                                                @php
                                                    $n = $est->notas[$evaluacion->evaluacion_id] ?? null;
                                                @endphp

                                                <div class="cn-mobile-evaluation">
                                                    <div class="cn-mobile-evaluation-info">
                                                        <strong>{{ $evaluacion->nombre_evaluacion }}</strong>
                                                        <span>
                                                            {{ $categoria->tipo_categoria }}
                                                            · Periodo {{ $periodoData['periodo']->numero_periodo }}
                                                        </span>
                                                    </div>

                                                    <div class="cn-mobile-evaluation-action">
                                                        @if ($n)
                                                            <div class="cn-mobile-grade">
                                                                <strong>{{ number_format($n['nota'], 1) }}</strong>
                                                                @if ($n['es_diferida'])
                                                                    <span class="cn-deferred-tag" title="Evaluación diferida">Diferida</span>
                                                                @endif
                                                            </div>

                                                            <button type="button"
                                                                    class="btn cn-edit-grade-button cn-edit-grade-mobile"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#modalCorregir"
                                                                    data-calificacion-id="{{ $n['calificacion_id'] }}"
                                                                    data-nota-actual="{{ $n['nota'] }}"
                                                                    data-evaluacion="{{ $evaluacion->nombre_evaluacion }}"
                                                                    data-estudiante="{{ $est->nombre }}"
                                                                    data-action="{{ route('coordinador-facultad.correccion-notas.update', [$seccion->seccion_id, $n['calificacion_id']]) }}"
                                                                    title="Corregir nota"
                                                                    aria-label="Corregir nota de {{ $est->nombre }} en {{ $evaluacion->nombre_evaluacion }}">
                                                                <i class="bi bi-pencil-square"></i>
                                                                <span>Editar</span>
                                                            </button>
                                                        @else
                                                            <span class="cn-pending-grade">
                                                                <i class="bi bi-clock"></i>
                                                                Sin nota
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endforeach
                                    @endforeach
                                </div>
                            </article>
                        @endforeach
                    </div>
                @else
                    <div class="cn-empty-state">
                        <div class="cn-empty-icon"><i class="bi bi-people"></i></div>
                        <h3>No hay estudiantes inscritos</h3>
                        <p>No hay estudiantes inscritos en esta sección.</p>
                    </div>
                @endif
            </section>

            {{-- MODAL DE CORRECCIÓN --}}
            <div class="modal fade cn-correction-modal" id="modalCorregir" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form method="POST" id="formCorregir" novalidate>
                            @csrf
                            @method('PUT')

                            <div class="modal-header">
                                <div class="cn-modal-icon"><i class="bi bi-pencil-square"></i></div>
                                <div class="cn-modal-heading">
                                    <h5 class="modal-title">Corregir calificación</h5>
                                    <p>Registra el nuevo valor y documenta el motivo.</p>
                                </div>
                                <button type="button"
                                        class="btn-close"
                                        data-bs-dismiss="modal"
                                        aria-label="Cerrar"></button>
                            </div>

                            <div class="modal-body">
                                <div class="cn-modal-context">
                                    <div>
                                        <span>Estudiante</span>
                                        <strong id="mcEstudiante"></strong>
                                    </div>
                                    <div>
                                        <span>Evaluación</span>
                                        <strong id="mcEvaluacion"></strong>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="mcNotaActual" class="form-label">Nota actual</label>
                                    <input type="text"
                                           class="form-control"
                                           id="mcNotaActual"
                                           disabled>
                                </div>

                                <div class="mb-3">
                                    <label for="mcNuevaNota" class="form-label">
                                        Nueva nota <span class="text-danger">*</span>
                                    </label>
                                    <input type="number"
                                           name="nota"
                                           id="mcNuevaNota"
                                           class="form-control @error('nota') is-invalid @enderror"
                                           step="0.1"
                                           min="0"
                                           max="10"
                                           required
                                           placeholder="Ej. 8.5">
                                    @error('nota')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="cn-field-hint">Valor permitido: de 0.0 a 10.0.</small>
                                </div>

                                <div class="mb-0">
                                    <label for="mcMotivo" class="form-label">
                                        Motivo de la corrección <span class="text-danger">*</span>
                                    </label>
                                    <textarea name="motivo"
                                              id="mcMotivo"
                                              class="form-control @error('motivo') is-invalid @enderror"
                                              rows="3"
                                              minlength="5"
                                              maxlength="500"
                                              required
                                              placeholder="Explica brevemente por qué se realiza esta corrección.">{{ old('motivo') }}</textarea>
                                    @error('motivo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="cn-field-hint">Mínimo 5 y máximo 500 caracteres.</small>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn cn-modal-cancel" data-bs-dismiss="modal">
                                    Cancelar
                                </button>
                                <button type="submit" class="btn cn-modal-submit">
                                    <i class="bi bi-check2-circle"></i>
                                    Guardar corrección
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        @endif

    </div>
</div>
@endsection

{{-- ESTILOS EXTERNOS --}}
@push('styles')
    @vite('resources/css/correccion-notas.css')
@endpush

{{-- JAVASCRIPT ORIGINAL DE LA CORRECCIÓN --}}
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('modalCorregir');
        if (!modal) return;

        modal.addEventListener('show.bs.modal', function (event) {
            const btn = event.relatedTarget;
            if (!btn) return;

            document.getElementById('formCorregir').action = btn.getAttribute('data-action');
            document.getElementById('mcEstudiante').textContent = btn.getAttribute('data-estudiante');
            document.getElementById('mcEvaluacion').textContent = btn.getAttribute('data-evaluacion');
            document.getElementById('mcNotaActual').value = btn.getAttribute('data-nota-actual');
            document.getElementById('mcNuevaNota').value = '';
            document.getElementById('mcMotivo').value = '';
        });

        document.getElementById('formCorregir').addEventListener('submit', function () {
            const btn = this.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Guardando...';
        });
    });
</script>
@endpush
