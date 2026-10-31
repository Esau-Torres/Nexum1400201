@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">

    {{-- HEADER --}}
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-md-between gap-3 p-3 mb-4 rounded-4 card">
        <div class="d-flex align-items-center">
            <div class="px-3 py-2 rounded-3 bg-danger bg-opacity-10" style="width:48px;height:48px;">
                <i class="bi bi-mortarboard-fill fs-4 text-danger"></i>
            </div>
            <div class="px-3">
                <h2 class="h4 fw-bold mb-0">Récord Académico</h2>
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

                                <button type="button"
                                        class="btn btn-sm btn-outline-danger w-100"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalDetalle"
                                        data-codigo="{{ $detalle->codigo_materia }}"
                                        data-nombre="{{ $detalle->materia_nombre }}"
                                        data-uv="{{ $detalle->unidades_valorativas }}"
                                        data-ciclo="{{ $detalle->nombre_ciclo }}"
                                        data-periodo="{{ \Carbon\Carbon::parse($detalle->fecha_inicio)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($detalle->fecha_fin)->format('d/m/Y') }}"
                                        data-estado="Cursando"
                                        data-estado-badge="bg-primary"
                                        data-docente="{{ $detalle->docente_nombre ?? 'Sin asignar' }}"
                                        data-grupo="{{ $detalle->nombre_grupo }}"
                                        data-nota="{{ $detalle->promedio_final ?? 'Sin nota' }}"
                                        data-horarios='@json($detalle->horarios)'
                                        data-matricula="{{ $detalle->numero_matricula }}"
                                        data-sobrecupo="{{ $detalle->es_sobrecupo ? 'Sí' : 'No' }}">
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
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
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
    const modal = document.getElementById('modalDetalle');

    modal.addEventListener('show.bs.modal', function (event) {
        const btn = event.relatedTarget;
        if (!btn) return;

        const codigo     = btn.getAttribute('data-codigo');
        const nombre     = btn.getAttribute('data-nombre');
        const uv         = btn.getAttribute('data-uv');
        const ciclo      = btn.getAttribute('data-ciclo');
        const periodo    = btn.getAttribute('data-periodo');
        const estado     = btn.getAttribute('data-estado');
        const estadoBadge= btn.getAttribute('data-estado-badge');
        const docente    = btn.getAttribute('data-docente');
        const grupo      = btn.getAttribute('data-grupo');
        const nota       = btn.getAttribute('data-nota');
        const matricula  = btn.getAttribute('data-matricula');
        const sobrecupo  = btn.getAttribute('data-sobrecupo');
        const horarios   = JSON.parse(btn.getAttribute('data-horarios') || '[]');

        document.getElementById('mCodigo').textContent     = codigo;
        document.getElementById('mNombre').textContent     = nombre;
        document.getElementById('mUv').textContent         = uv + ' UV';
        document.getElementById('mCiclo').textContent      = ciclo;
        document.getElementById('mPeriodo').textContent    = periodo;
        document.getElementById('mDocente').textContent    = docente;
        document.getElementById('mGrupo').textContent      = grupo;
        document.getElementById('mNota').textContent       = nota;
        document.getElementById('mMatricula').textContent  = matricula;
        document.getElementById('mSobrecupo').textContent  = sobrecupo;

        const badge = document.getElementById('mEstadoBadge');
        badge.className   = 'badge ' + estadoBadge;
        badge.textContent = estado;

        // Render de horarios
        const horariosWrapper = document.getElementById('mHorarios');
        horariosWrapper.innerHTML = '';

        if (!horarios || horarios.length === 0) {
            horariosWrapper.innerHTML = '<p class="text-muted small mb-0">Sin horarios registrados.</p>';
        } else {
            horarios.forEach(function (h) {
                const row = document.createElement('div');
                row.className = 'd-flex justify-content-between align-items-center p-2 rounded bg-light';
                row.innerHTML =
                    '<div>' +
                        '<div class="fw-semibold small">' + h.dia + '</div>' +
                        '<div class="text-muted" style="font-size: 0.75rem;">' +
                            h.ini + ' - ' + h.fin +
                        '</div>' +
                    '</div>' +
                    '<span class="badge bg-secondary">' +
                        '<i class="bi bi-geo-alt-fill me-1"></i>' + h.aula +
                    '</span>';
                horariosWrapper.appendChild(row);
            });
        }
    });
});
</script>
@endpush
@endsection