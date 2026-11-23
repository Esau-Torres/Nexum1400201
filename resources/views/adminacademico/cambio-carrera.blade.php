@extends('layouts.app')

@section('title', 'Cambio de Carrera')

@push('styles')
    @vite('resources/css/cambiocarrera.css')
@endpush

@section('content')
<div class="cc-view">
    <div class="cc-shell container-fluid">

        {{-- ENCABEZADO DE LA VISTA --}}
        <header class="cc-page-header">
            <div class="cc-heading-content">
                <div class="cc-eyebrow">
                    <span class="cc-eyebrow-mark"></span>
                    ADMINISTRACIÓN ACADÉMICA
                    <span class="cc-eyebrow-divider">/</span>
                    GESTIÓN ESTUDIANTIL
                </div>

                <div class="cc-title-row">
                    <div class="cc-title-icon" aria-hidden="true">
                        <i class="bi bi-arrow-left-right"></i>
                    </div>
                    <div>
                        <h1 class="cc-page-title">CAMBIO DE CARRERA</h1>
                        <p class="cc-page-subtitle">
                            Gestiona y valida las solicitudes de traslado académico de los estudiantes.
                        </p>
                    </div>
                </div>
            </div>

            <a href="{{ route('home') }}" class="cc-back-link">
                <i class="bi bi-arrow-left" aria-hidden="true"></i>
                <span>Volver al inicio</span>
            </a>
        </header>

        {{-- INDICADOR DEL PROCESO --}}
        <div class="cc-process-track" aria-label="Etapas del proceso">
            <div class="cc-process-step {{ $alumnoSeleccionado ? 'cc-process-step--complete' : 'cc-process-step--active' }}">
                <span class="cc-process-number">
                    @if ($alumnoSeleccionado)
                        <i class="bi bi-check2" aria-hidden="true"></i>
                    @else
                        01
                    @endif
                </span>
                <span class="cc-process-copy">
                    <strong>Seleccionar estudiante</strong>
                    <small>Identificación del alumno</small>
                </span>
            </div>

            <span class="cc-process-connector" aria-hidden="true"></span>

            <div class="cc-process-step {{ $alumnoSeleccionado ? 'cc-process-step--active' : '' }}">
                <span class="cc-process-number">02</span>
                <span class="cc-process-copy">
                    <strong>Revisar y procesar</strong>
                    <small>Validación académica y financiera</small>
                </span>
            </div>
        </div>

        {{-- PASO 1: SELECCIÓN DEL ESTUDIANTE --}}
        <section class="cc-panel cc-selection-panel" aria-labelledby="cc-selection-title">
            <div class="cc-panel-header">
                <div class="cc-panel-heading">
                    <span class="cc-section-icon">
                        <i class="bi bi-people" aria-hidden="true"></i>
                    </span>
                    <div>
                        <span class="cc-section-kicker">PASO 01</span>
                        <h2 id="cc-selection-title" class="cc-panel-title">Seleccionar estudiante</h2>
                        <p class="cc-panel-description">Selecciona al alumno al que se le gestionará el cambio de carrera.</p>
                    </div>
                </div>
            </div>

            <div class="cc-panel-body cc-panel-body--table">
                <div class="cc-table-heading">
                    <div>
                        <h3 class="cc-content-title">Directorio de estudiantes</h3>
                        <p class="cc-content-caption">Consulta la información académica antes de iniciar el proceso.</p>
                    </div>
                    <span class="cc-record-label">
                        <i class="bi bi-list-ul" aria-hidden="true"></i>
                        Registros disponibles
                    </span>
                </div>

                <div class="table-responsive cc-table-responsive">
                    <table class="table align-middle cc-student-table" id="cambiocarreratable">
                        <thead>
                            <tr>
                                <th scope="col">Código</th>
                                <th scope="col">Estudiante</th>
                                <th scope="col">Documento</th>
                                <th scope="col">Carrera actual</th>
                                <th scope="col" class="cc-action-column">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($alumnos as $a)
                                <tr>
                                    <td data-label="Código">
                                        <span class="cc-student-code">{{ $a->codigo_estudiante }}</span>
                                    </td>
                                    <td data-label="Estudiante">
                                        <div class="cc-student-identity">
                                            <span class="cc-student-avatar" aria-hidden="true">
                                                {{ strtoupper(substr($a->usuario?->name ?? 'ES', 0, 1)) }}
                                            </span>
                                            <span class="cc-student-name">{{ $a->usuario?->name }}</span>
                                        </div>
                                    </td>
                                    <td data-label="Documento" class="cc-muted-cell">
                                        {{ $a->usuario?->documento_identidad }}
                                    </td>
                                    <td data-label="Carrera actual">
                                        <span class="cc-career-cell">{{ $a->carrera?->nombre }}</span>
                                    </td>
                                    <td data-label="Acción" class="cc-action-column">
                                        <a href="{{ route('admin-academico.cambio-carrera', ['alumno_id' => $a->alumno_id]) }}"
                                           class="cc-select-button">
                                            Seleccionar
                                            <i class="bi bi-arrow-right-short" aria-hidden="true"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="cc-empty-cell">
                                        <span class="cc-empty-icon"><i class="bi bi-inbox" aria-hidden="true"></i></span>
                                        <strong>No hay estudiantes disponibles</strong>
                                        <span>Actualmente no hay registros para mostrar.</span>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        @if ($alumnoSeleccionado)
            {{-- RESUMEN DEL ESTUDIANTE Y SU SITUACIÓN FINANCIERA --}}
            <div class="cc-summary-grid">
                <section class="cc-panel cc-summary-panel" aria-labelledby="cc-student-summary-title">
                    <div class="cc-panel-header">
                        <div class="cc-panel-heading">
                            <span class="cc-section-icon cc-section-icon--red">
                                <i class="bi bi-person-vcard" aria-hidden="true"></i>
                            </span>
                            <div>
                                <span class="cc-section-kicker">PERFIL ACADÉMICO</span>
                                <h2 id="cc-student-summary-title" class="cc-panel-title">Datos del estudiante</h2>
                            </div>
                        </div>
                        <span class="cc-header-tag">Identificación</span>
                    </div>

                    <div class="cc-panel-body">
                        <div class="cc-profile-highlight">
                            <span class="cc-profile-avatar" aria-hidden="true">
                                {{ strtoupper(substr($alumnoSeleccionado->usuario?->name ?? 'ES', 0, 1)) }}
                            </span>
                            <div class="cc-profile-main">
                                <strong>{{ $alumnoSeleccionado->usuario?->name }}</strong>
                                <span>{{ $alumnoSeleccionado->codigo_estudiante }}</span>
                            </div>
                            @if ($esGraduado)
                                <span class="cc-status-pill cc-status-pill--info">Graduado</span>
                            @else
                                <span class="cc-status-pill cc-status-pill--success">Activo</span>
                            @endif
                        </div>

                        <div class="cc-detail-grid">
                            <div class="cc-detail-item">
                                <span class="cc-detail-label">Documento de identidad</span>
                                <strong class="cc-detail-value">{{ $alumnoSeleccionado->usuario?->documento_identidad }}</strong>
                            </div>
                            <div class="cc-detail-item">
                                <span class="cc-detail-label">Estado de carrera</span>
                                <strong class="cc-detail-value">
                                    <span class="cc-status-pill cc-status-pill--neutral">{{ $alumnoSeleccionado->estado_carrera }}</span>
                                </strong>
                            </div>
                            <div class="cc-detail-item cc-detail-item--wide">
                                <span class="cc-detail-label">Carrera actual</span>
                                <strong class="cc-detail-value">{{ $alumnoSeleccionado->carrera?->nombre }}</strong>
                            </div>
                            <div class="cc-detail-item cc-detail-item--wide">
                                <span class="cc-detail-label">Facultad</span>
                                <strong class="cc-detail-value">{{ $alumnoSeleccionado->carrera?->facultad?->nombre }}</strong>
                            </div>
                        </div>

                        <div class="cc-condition-note {{ $esGraduado ? 'cc-condition-note--info' : 'cc-condition-note--success' }}">
                            <i class="bi {{ $esGraduado ? 'bi-mortarboard' : 'bi-person-check' }}" aria-hidden="true"></i>
                            <span>
                                @if ($esGraduado)
                                    Estudiante egresado / graduado: exento de arancel por cambio de carrera.
                                @else
                                    Estudiante activo: sujeto a las validaciones académicas y financieras correspondientes.
                                @endif
                            </span>
                        </div>
                    </div>
                </section>

                <section class="cc-panel cc-summary-panel" aria-labelledby="cc-financial-summary-title">
                    <div class="cc-panel-header">
                        <div class="cc-panel-heading">
                            <span class="cc-section-icon cc-section-icon--teal">
                                <i class="bi bi-wallet2" aria-hidden="true"></i>
                            </span>
                            <div>
                                <span class="cc-section-kicker">ESTADO DE CUENTA</span>
                                <h2 id="cc-financial-summary-title" class="cc-panel-title">Resumen financiero</h2>
                            </div>
                        </div>
                        @if ($resumenFinanciero)
                            <span class="cc-cycle-tag">
                                <i class="bi bi-calendar3" aria-hidden="true"></i>
                                {{ $resumenFinanciero['ciclo']?->nombre_ciclo ?? 'Sin ciclo' }}
                            </span>
                        @endif
                    </div>

                    <div class="cc-panel-body">
                        @if ($resumenFinanciero)
                            <div class="cc-finance-metrics">
                                <div class="cc-finance-metric">
                                    <span class="cc-finance-label">Deuda</span>
                                    <strong>${{ number_format($resumenFinanciero['total_deuda'], 2) }}</strong>
                                    <span class="cc-finance-icon cc-finance-icon--red"><i class="bi bi-receipt" aria-hidden="true"></i></span>
                                </div>
                                <div class="cc-finance-metric">
                                    <span class="cc-finance-label">Pagado</span>
                                    <strong>${{ number_format($resumenFinanciero['total_pagado'], 2) }}</strong>
                                    <span class="cc-finance-icon cc-finance-icon--teal"><i class="bi bi-check2-circle" aria-hidden="true"></i></span>
                                </div>
                                <div class="cc-finance-metric cc-finance-metric--balance">
                                    <span class="cc-finance-label">Saldo</span>
                                    <strong class="{{ $resumenFinanciero['solvente'] ? 'cc-amount-positive' : 'cc-amount-negative' }}">
                                        ${{ number_format($resumenFinanciero['saldo'], 2) }}
                                    </strong>
                                    <span class="cc-finance-icon cc-finance-icon--neutral"><i class="bi bi-scale" aria-hidden="true"></i></span>
                                    @if ($resumenFinanciero['total_pagado'] > $resumenFinanciero['total_deuda'])
                                        <span class="cc-credit-note">
                                            A favor: ${{ number_format($resumenFinanciero['total_pagado'] - $resumenFinanciero['total_deuda'], 2) }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="cc-finance-status-row">
                                <div class="cc-finance-status-item">
                                    <span class="cc-detail-label">Solvencia</span>
                                    @if ($resumenFinanciero['solvente'])
                                        <span class="cc-status-pill cc-status-pill--success"><i class="bi bi-check-circle-fill" aria-hidden="true"></i> Solvente</span>
                                    @else
                                        <span class="cc-status-pill cc-status-pill--danger"><i class="bi bi-exclamation-circle-fill" aria-hidden="true"></i> No solvente</span>
                                    @endif
                                </div>

                                <div class="cc-finance-status-item">
                                    <span class="cc-detail-label">Arancel TRA-005</span>
                                    @if ($esGraduado)
                                        <span class="cc-status-pill cc-status-pill--info">Exento (graduado)</span>
                                    @elseif ($resumenFinanciero['arancel_pagado'])
                                        <span class="cc-status-pill cc-status-pill--success">
                                            Pagado (${{ number_format($resumenFinanciero['arancel_pagado_monto'], 2) }})
                                        </span>
                                    @else
                                        <span class="cc-status-pill cc-status-pill--warning">
                                            Pendiente — ${{ number_format($resumenFinanciero['arancel_pagado_monto'], 2) }} / ${{ number_format($resumenFinanciero['arancel_requerido'], 2) }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="cc-finance-empty">
                                <i class="bi bi-info-circle" aria-hidden="true"></i>
                                <span>No hay un resumen financiero disponible para este estudiante.</span>
                            </div>
                        @endif
                    </div>
                </section>
            </div>

            {{-- RESTRICCIÓN POR MATERIAS EN CURSO --}}
            @if (($materiasEnCurso ?? collect())->isNotEmpty())
                <section class="cc-blocker-panel" aria-labelledby="cc-course-blocker-title">
                    <div class="cc-blocker-icon"><i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i></div>
                    <div class="cc-blocker-content">
                        <div class="cc-blocker-heading">
                            <h2 id="cc-course-blocker-title">No es posible procesar el cambio todavía</h2>
                            <span class="cc-status-pill cc-status-pill--warning">Acción requerida</span>
                        </div>
                        <p>
                            El estudiante tiene materias en curso. Debe darse de baja de las siguientes asignaturas antes de continuar con el cambio de carrera:
                        </p>
                        <ul class="cc-course-list">
                            @foreach ($materiasEnCurso as $d)
                                <li>
                                    <span class="cc-course-code">{{ $d->seccion?->materia?->codigo_materia ?? '—' }}</span>
                                    <span class="cc-course-name">{{ $d->seccion?->materia?->nombre ?? 'Materia #' . $d->id_seccion }}</span>
                                    @if ($d->seccion?->nombre)
                                        <span class="cc-course-section">Sección {{ $d->seccion->nombre }}</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </section>
            @endif

            {{-- PASO 2: FORMULARIO DE CAMBIO --}}
            <section class="cc-panel cc-form-panel" aria-labelledby="cc-change-form-title">
                <div class="cc-panel-header">
                    <div class="cc-panel-heading">
                        <span class="cc-section-icon cc-section-icon--red">
                            <i class="bi bi-arrow-left-right" aria-hidden="true"></i>
                        </span>
                        <div>
                            <span class="cc-section-kicker">PASO 02</span>
                            <h2 id="cc-change-form-title" class="cc-panel-title">Datos del cambio</h2>
                            <p class="cc-panel-description">Define el motivo y la carrera de destino para el traslado.</p>
                        </div>
                    </div>
                    <span class="cc-required-note"><span>*</span> Campos obligatorios</span>
                </div>

                <div class="cc-panel-body cc-form-body">
                    <form method="POST" action="{{ route('admin-academico.cambio-carrera.store') }}">
                        @csrf
                        <input type="hidden" name="alumno_id" value="{{ $alumnoSeleccionado->alumno_id }}">
                        <input type="hidden" name="es_graduado" value="{{ $esGraduado ? 1 : 0 }}">

                        <div class="row g-4">
                            <div class="col-md-6">
                                <label for="motivo" class="cc-form-label">
                                    Motivo del cambio <span>*</span>
                                </label>
                                <div class="cc-input-wrap">
                                    <i class="bi bi-signpost-split cc-input-icon" aria-hidden="true"></i>
                                    <select name="motivo" id="motivo" class="form-select cc-form-control @error('motivo') is-invalid @enderror" required>
                                        <option value="VOLUNTARIO" @selected($motivo === 'VOLUNTARIO')>Voluntario (vocacional/académico)</option>
                                        <option value="FORZOSO_REPROBACION" @selected($motivo === 'FORZOSO_REPROBACION')>Forzoso (reprobación 3ra matrícula)</option>
                                    </select>
                                </div>
                                @error('motivo')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <div class="cc-field-help">Selecciona la condición que justifica el traslado académico.</div>
                            </div>

                            <div class="col-md-6" id="wrapper-materia" style="{{ $motivo === 'FORZOSO_REPROBACION' ? '' : 'display:none;' }}">
                                <label for="materia_reprobada_id" class="cc-form-label">Materia reprobada (3 veces)</label>
                                <div class="cc-input-wrap">
                                    <i class="bi bi-journal-x cc-input-icon" aria-hidden="true"></i>
                                    <select name="materia_reprobada_id" id="materia_reprobada_id" class="form-select cc-form-control @error('materia_reprobada_id') is-invalid @enderror">
                                        <option value="">— Seleccione —</option>
                                        @foreach ($materiasReprobadas as $m)
                                            <option value="{{ $m['materia_id'] }}" @selected((int) $materiaReprobadaId === (int) $m['materia_id'])>
                                                {{ $m['codigo'] }} — {{ $m['nombre'] }} ({{ $m['reprobaciones'] }} reprobaciones)
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('materia_reprobada_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                @if ($materiasReprobadas->isEmpty())
                                    <div class="cc-field-help cc-field-help--danger">No se encontraron materias con 3 reprobaciones.</div>
                                @else
                                    <div class="cc-field-help">Elige la asignatura que originó el cambio forzoso.</div>
                                @endif
                            </div>

                            <div class="col-12">
                                <label for="nueva_carrera_id" class="cc-form-label">
                                    Nueva carrera <span>*</span>
                                </label>
                                <div class="cc-input-wrap">
                                    <i class="bi bi-mortarboard cc-input-icon" aria-hidden="true"></i>
                                    <select name="nueva_carrera_id" id="nueva_carrera_id" class="form-select cc-form-control @error('nueva_carrera_id') is-invalid @enderror" required>
                                        <option value="">— Seleccione la carrera de destino —</option>
                                        @foreach ($carreras as $c)
                                            <option value="{{ $c->carrera_id }}" @selected(old('nueva_carrera_id') == $c->carrera_id)>
                                                {{ $c->nombre }} ({{ $c->facultad?->nombre }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('nueva_carrera_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                @if ($motivo === 'FORZOSO_REPROBACION')
                                    <div class="cc-field-help">
                                        <i class="bi bi-info-circle" aria-hidden="true"></i>
                                        Solo se muestran carreras cuyo pensum activo NO contiene la materia reprobada.
                                    </div>
                                @else
                                    <div class="cc-field-help">Selecciona la carrera a la que será trasladado el estudiante.</div>
                                @endif
                            </div>
                        </div>

                        <div class="cc-form-footer">
                            <div class="cc-form-security-note">
                                <i class="bi bi-shield-check" aria-hidden="true"></i>
                                <span>Verifica la información antes de procesar el cambio.</span>
                            </div>
                            <div class="cc-form-actions">
                                <a href="{{ route('admin-academico.cambio-carrera') }}" class="cc-cancel-button">Cancelar</a>
                                <button type="submit" class="cc-submit-button"
                                    @if (($materiasEnCurso ?? collect())->isNotEmpty()) disabled @endif>
                                    <i class="bi bi-check2-circle" aria-hidden="true"></i>
                                    Procesar cambio
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </section>
        @endif

        <footer class="cc-page-footer">
            <span>NEXUM · Gestión académica</span>
            <span>Universidad Modular Abierta</span>
        </footer>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('motivo')?.addEventListener('change', function () {
        const wrapper = document.getElementById('wrapper-materia');
        if (!wrapper) return;
        wrapper.style.display = this.value === 'FORZOSO_REPROBACION' ? '' : 'none';
    });
</script>
@endpush
