@extends('layouts.app')

@section('content')
<div class="container-fluid p-4 p-lg-5">

    {{-- HEADER --}}
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-md-between gap-3 p-3 mb-4 rounded-4 card">
        <div class="d-flex align-items-center">
            <div class="px-3 py-2 rounded-3 bg-danger bg-opacity-10" style="width:48px;height:48px;">
                <i class="bi bi-mortarboard-fill fs-4 text-danger"></i>
            </div>
            <div class="px-3">
                <h2 class="h4 fw-bold mb-0">RÉCORD ACADÉMICO</h2>
                <p class="text-muted mb-0 small">
                    Historial completo de materias cursadas y en curso.
                </p>
            </div>
        </div>
        <span class="badge bg-danger">
            {{ $actuales->count() + $historicas->count() }} materia(s)
        </span>
    </div>

    {{-- ═══════════════════════════════════════════ --}}
    {{--  MATERIAS ACTUALES (prioridad)              --}}
    {{-- ═══════════════════════════════════════════ --}}
    <div class="mb-4">
        <h5 class="fw-bold mb-3 text-uppercase small text-muted">
            <i class="bi bi-play-circle-fill text-danger me-1"></i>
            Materias en curso ({{ $actuales->count() }})
        </h5>

        @if($actuales->isEmpty())
            <div class="alert alert-info border-0 shadow-sm">
                <i class="bi bi-info-circle-fill me-1"></i>
                No tienes materias en curso actualmente.
            </div>
        @else
            <div class="row g-3">
                @foreach($actuales as $detalle)
                    <div class="col-md-6 col-xl-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="badge bg-light text-dark">{{ $detalle->codigo_materia }}</span>
                                    <span class="badge bg-danger">{{ $detalle->unidades_valorativas }} UV</span>
                                </div>
                                <h6 class="fw-bold mb-1">{{ $detalle->materia_nombre }}</h6>
                                <small class="text-muted d-block mb-2">
                                    <i class="bi bi-calendar3 me-1"></i>{{ $detalle->nombre_ciclo }}
                                    · Grupo {{ $detalle->nombre_grupo }}
                                </small>
                                <span class="badge bg-primary mb-3">Cursando</span>

                                @php
                                    $valRetiro = $validacionesRetiro[$detalle->detalle_inscripcion_id] ?? null;
                                @endphp
                                <button type="button"
                                        class="btn btn-sm btn-outline-danger w-100"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalDetalle"
                                        data-detalle-id="{{ $detalle->detalle_inscripcion_id }}"
                                        data-codigo="{{ $detalle->codigo_materia }}"
                                        data-nombre="{{ $detalle->materia_nombre }}"
                                        data-uv="{{ $detalle->unidades_valorativas }}"
                                        data-ciclo="{{ $detalle->nombre_ciclo }}"
                                        data-periodo="{{ \Carbon\Carbon::parse($detalle->fecha_inicio)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($detalle->fecha_fin)->format('d/m/Y') }}"
                                        data-estado="Cursando"
                                        data-estado-value="CURSANDO"
                                        data-estado-badge="bg-primary"
                                        data-docente="{{ $detalle->docente_nombre ?? 'Sin asignar' }}"
                                        data-grupo="{{ $detalle->nombre_grupo }}"
                                        data-nota="{{ $detalle->promedio_final ?? 'Sin nota' }}"
                                        data-horarios='@json($detalle->horarios)'
                                        data-matricula="{{ $detalle->numero_matricula }}"
                                        data-sobrecupo="{{ $detalle->es_sobrecupo ? 'Sí' : 'No' }}"
                                        data-puede-retirar="{{ $valRetiro && $valRetiro['puede'] ? '1' : '0' }}"
                                        data-retiros-disponibles="{{ $valRetiro['retiros_disponibles'] ?? 0 }}"
                                        data-bloqueadores='@json($valRetiro['bloqueadores'] ?? [])'>
                                    <i class="bi bi-eye"></i> Ver detalles
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- ═══════════════════════════════════════════ --}}
    {{--  MATERIAS HISTÓRICAS                        --}}
    {{-- ═══════════════════════════════════════════ --}}
    <div>
        <h5 class="fw-bold mb-3 text-uppercase small text-muted">
            <i class="bi bi-clock-history text-secondary me-1"></i>
            Historial académico ({{ $historicas->count() }})
        </h5>

        @if($historicas->isEmpty())
            <div class="alert alert-info border-0 shadow-sm">
                <i class="bi bi-info-circle-fill me-1"></i>
                Aún no hay materias en tu historial.
            </div>
        @else
            <div class="card border-0 shadow-sm">
                <div class="text-medium">
                    <table class="table table-hover align-middle mb-0" id="recod_academico_table">
                        <thead class="table-light">
                            <tr>
                                <th>Código</th>
                                <th>Materia</th>
                                <th class="text-center">UV</th>
                                <th>Ciclo</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center">Nota</th>
                                <th class="text-end">Detalle</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($historicas as $detalle)
                                @php
                                    $estado = strtoupper($detalle->estado_materia);
                                    [$estadoLabel, $estadoBadge] = match ($estado) {
                                        'APROBADA'  => ['Aprobada',  'bg-success'],
                                        'REPROBADA' => ['Reprobada', 'bg-danger'],
                                        'RETIRADA'  => ['Retirada',  'bg-warning text-dark'],
                                        default     => [ucfirst(strtolower($estado)), 'bg-secondary'],
                                    };
                                @endphp
                                <tr>
                                    <td><code class="small">{{ $detalle->codigo_materia }}</code></td>
                                    <td class="fw-semibold small">{{ $detalle->materia_nombre }}</td>
                                    <td class="text-center">{{ $detalle->unidades_valorativas }}</td>
                                    <td><small class="text-muted">{{ $detalle->nombre_ciclo }}</small></td>
                                    <td class="text-center">
                                        <span class="badge {{ $estadoBadge }}">{{ $estadoLabel }}</span>
                                    </td>
                                    <td class="text-center fw-bold">
                                        {{ $detalle->promedio_final ?? '—' }}
                                    </td>
                                    <td class="text-end">
                                        
                                        <button type="button"
                                                class="btn btn-sm btn-outline-danger"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalDetalle"
                                                data-codigo="{{ $detalle->codigo_materia }}"
                                                data-nombre="{{ $detalle->materia_nombre }}"
                                                data-uv="{{ $detalle->unidades_valorativas }}"
                                                data-ciclo="{{ $detalle->nombre_ciclo }}"
                                                data-periodo="{{ \Carbon\Carbon::parse($detalle->fecha_inicio)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($detalle->fecha_fin)->format('d/m/Y') }}"
                                                data-estado="{{ $estadoLabel }}"
                                                data-estado-badge="{{ $estadoBadge }}"
                                                data-docente="{{ $detalle->docente_nombre ?? 'Sin asignar' }}"
                                                data-grupo="{{ $detalle->nombre_grupo }}"
                                                data-nota="{{ $detalle->promedio_final ?? 'Sin nota' }}"
                                                data-horarios='@json($detalle->horarios)'
                                                data-matricula="{{ $detalle->numero_matricula }}"
                                                data-estado="{{ $estadoLabel }}"
                                                data-estado-value="{{ $estado }}"
                                                data-estado-badge="{{ $estadoBadge }}"
                                                data-sobrecupo="{{ $detalle->es_sobrecupo ? 'Sí' : 'No' }}">
                                            <i class="bi bi-eye"></i> Ver
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</div>

<div class="modal fade" id="modalDetalle" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">

            <div class="modal-header bg-danger text-white border-0">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-file-earmark-text-fill me-1"></i>
                    Detalle de la materia
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body p-4">

                {{-- Encabezado --}}
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <span class="badge bg-light text-dark mb-2" id="mCodigo"></span>
                        <span class="badge" id="mEstadoBadge"></span>
                        <h6 class="fw-bold mt-2 mb-1" id="mNombre"></h6>
                        <small class="text-muted">
                            <i class="bi bi-calendar3 me-1"></i><span id="mCiclo"></span>
                        </small>
                    </div>
                    <small class="text-muted text-end">
                        <i class="bi bi-clock me-1"></i>
                        <span id="mPeriodo"></span>
                    </small>
                </div>

                <hr>

                {{-- Info académica --}}
                <h6 class="fw-bold text-uppercase small text-muted mb-2">Información académica</h6>
                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <small class="text-muted d-block">Unidades Valorativas</small>
                        <span class="fw-bold" id="mUv"></span>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Grupo</small>
                        <span class="fw-bold" id="mGrupo"></span>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">N° Matrícula</small>
                        <span class="fw-bold" id="mMatricula"></span>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Sobrecupo</small>
                        <span class="fw-bold" id="mSobrecupo"></span>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Nota final</small>
                        <span class="fw-bold" id="mNota"></span>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Docente</small>
                        <span class="fw-bold" id="mDocente"></span>
                    </div>
                </div>

                <hr>

                {{-- Horarios --}}
                <h6 class="fw-bold text-uppercase small text-muted mb-2">Horarios y aula</h6>
                <div id="mHorarios" class="d-flex flex-column gap-2"></div>

                {{-- Botón de retiro (solo para materias CURSANDO) --}}
                <div class="mt-3" id="wrapperRetiro">
                    <hr>
                    <h6 class="fw-bold text-uppercase small text-muted mb-2">Acciones</h6>

                    {{-- Alerta de bloqueo (si no puede retirar) --}}
                    <div id="bloqueoRetiro" class="alert alert-warning border-0 small d-none mb-2">
                        <div class="d-flex align-items-start gap-2">
                            <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                            <div>
                                <strong class="d-block mb-1">No puedes retirar esta materia</strong>
                                <ul class="mb-0 ps-3" id="listaBloqueadoresRetiro"></ul>
                            </div>
                        </div>
                    </div>

                    {{-- Info de retiros disponibles --}}
                    <div id="infoRetiros" class="alert alert-info border-0 small d-none mb-2">
                        <i class="bi bi-info-circle me-1"></i>
                        Tienes <strong><span id="retirosDisponibles"></span></strong> retiro(s) disponible(s) para este ciclo.
                    </div>

                    <button type="button"
                            id="btnRetirar"
                            class="btn btn-outline-danger w-100"
                            data-bs-toggle="modal"
                            data-bs-target="#modalRetirar">
                        <i class="bi bi-box-arrow-right me-1"></i>
                        Darse de baja de la materia
                    </button>
                </div>
            </div>

            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalRetirar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <form method="POST"
              id="formRetirar"
              class="modal-content border-0 shadow">
            @csrf

            <div class="modal-header bg-danger text-white border-0">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                    Confirmar retiro de materia
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body p-4">
                <div class="alert alert-danger border-0 mb-3">
                    <h6 class="fw-bold mb-2">Estás a punto de retirar:</h6>
                    <p class="mb-0" id="retirarMateriaNombre"></p>
                </div>

                <p class="small mb-2">Al confirmar el retiro:</p>
                <ul class="small text-muted ps-3 mb-3">
                    <li>La materia quedará marcada como <strong>RETIRADA</strong>.</li>
                    <li><strong>No podrás volver a cursarla</strong> en el ciclo lectivo actual.</li>
                    <li>Se consumirá <strong>1 arancel de retiro</strong> (TRA-011) ya pagado.</li>
                    <li>El promedio final quedará <strong>sin calificación</strong> (no afectará tu CUM).</li>
                </ul>

                <div class="alert alert-warning border-0 small mb-3">
                    <i class="bi bi-info-circle me-1"></i>
                    Esta acción no se puede deshacer. Si tienes dudas, contacta a Administración Académica.
                </div>

                <div class="mb-3">
                    <label for="motivoRetiro" class="form-label small fw-semibold">
                        Motivo del retiro (opcional)
                    </label>
                    <textarea name="motivo" id="motivoRetiro" rows="2"
                              class="form-control" maxlength="500"
                              placeholder="Describe brevemente el motivo..."></textarea>
                </div>

                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="confirmarRetiro">
                    <label class="form-check-label small" for="confirmarRetiro">
                        Entiendo las consecuencias y confirmo el retiro de esta materia.
                    </label>
                </div>
            </div>

            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-danger" id="btnConfirmarRetiro" disabled>
                    <i class="bi bi-box-arrow-right me-1"></i> Sí, retirar materia
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const modalDetalle = document.getElementById('modalDetalle');
    const modalRetirar = document.getElementById('modalRetirar');
    const formRetirar  = document.getElementById('formRetirar');
    const btnConfirmar = document.getElementById('btnConfirmarRetiro');
    const checkbox     = document.getElementById('confirmarRetiro');
    const wrapperRetiro= document.getElementById('wrapperRetiro');
    const bloqueo      = document.getElementById('bloqueoRetiro');
    const infoRetiros  = document.getElementById('infoRetiros');

    let currentDetalleId = null;

    // ─────────────────────────────────────────
    // MODAL DETALLE
    // ─────────────────────────────────────────
    modalDetalle.addEventListener('show.bs.modal', function (event) {
        const btn = event.relatedTarget;
        if (!btn) return;

        currentDetalleId = btn.getAttribute('data-detalle-id');

        // Datos básicos
        document.getElementById('mCodigo').textContent     = btn.getAttribute('data-codigo');
        document.getElementById('mNombre').textContent     = btn.getAttribute('data-nombre');
        document.getElementById('mUv').textContent         = btn.getAttribute('data-uv') + ' UV';
        document.getElementById('mCiclo').textContent      = btn.getAttribute('data-ciclo');
        document.getElementById('mPeriodo').textContent    = btn.getAttribute('data-periodo');
        document.getElementById('mDocente').textContent    = btn.getAttribute('data-docente');
        document.getElementById('mGrupo').textContent      = btn.getAttribute('data-grupo');
        document.getElementById('mNota').textContent       = btn.getAttribute('data-nota');
        document.getElementById('mMatricula').textContent  = btn.getAttribute('data-matricula');
        document.getElementById('mSobrecupo').textContent  = btn.getAttribute('data-sobrecupo');

        const badge = document.getElementById('mEstadoBadge');
        badge.className   = 'badge ' + btn.getAttribute('data-estado-badge');
        badge.textContent = btn.getAttribute('data-estado');

        // Horarios
        const horarios = JSON.parse(btn.getAttribute('data-horarios') || '[]');
        const hw = document.getElementById('mHorarios');
        hw.innerHTML = '';
        if (!horarios.length) {
            hw.innerHTML = '<p class="text-muted small mb-0">Sin horarios registrados.</p>';
        } else {
            horarios.forEach(function (h) {
                const row = document.createElement('div');
                row.className = 'd-flex justify-content-between align-items-center p-2 rounded bg-light';
                row.innerHTML =
                    '<div>' +
                        '<div class="fw-semibold small">' + h.dia + '</div>' +
                        '<div class="text-muted" style="font-size: 0.75rem;">' + h.ini + ' - ' + h.fin + '</div>' +
                    '</div>' +
                    '<span class="badge bg-secondary"><i class="bi bi-geo-alt-fill me-1"></i>' + h.aula + '</span>';
                hw.appendChild(row);
            });
        }

        // ─────────────────────────────────────
        // Lógica de retiro
        // ─────────────────────────────────────
        const puedeRetirar = btn.getAttribute('data-puede-retirar') === '1';
        const bloqueadores = JSON.parse(btn.getAttribute('data-bloqueadores') || '[]');
        const disponibles  = btn.getAttribute('data-retiros-disponibles') || '0';
        const estadoValue  = btn.getAttribute('data-estado-value') || '';

        const listaBloq = document.getElementById('listaBloqueadoresRetiro');
        listaBloq.innerHTML = '';

        // Ocultar bloque completo si no es CURSANDO
        if (estadoValue !== 'CURSANDO') {
            wrapperRetiro.classList.add('d-none');
        } else {
            wrapperRetiro.classList.remove('d-none');

            if (puedeRetirar) {
                bloqueo.classList.add('d-none');
                infoRetiros.classList.remove('d-none');
                document.getElementById('retirosDisponibles').textContent = disponibles;
            } else {
                bloqueo.classList.remove('d-none');
                infoRetiros.classList.add('d-none');
                bloqueadores.forEach(function (b) {
                    const li = document.createElement('li');
                    li.textContent = b;
                    listaBloq.appendChild(li);
                });
            }
        }
    });

    // ─────────────────────────────────────────
    // MODAL RETIRAR
    // ─────────────────────────────────────────
    modalRetirar.addEventListener('show.bs.modal', function () {
        const nombreMateria = document.getElementById('mNombre').textContent;
        document.getElementById('retirarMateriaNombre').textContent = nombreMateria;

        // Reset
        checkbox.checked     = false;
        btnConfirmar.disabled = true;
        document.getElementById('motivoRetiro').value = '';

        // URL del form
        if (currentDetalleId) {
            formRetirar.action = '/student/alumno/materias/' + currentDetalleId + '/retirar';
        }
    });

    checkbox.addEventListener('change', function () {
        btnConfirmar.disabled = !this.checked;
    });

    formRetirar.addEventListener('submit', function () {
        btnConfirmar.disabled = true;
        btnConfirmar.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Procesando...';
    });
});
</script>
@endpush
@endsection