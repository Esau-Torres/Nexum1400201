@extends('layouts.app')

@section('title', $seccion ? 'Corregir Notas - ' . ($seccion->materia?->nombre ?? '') : 'Corrección de Notas')

@section('content')
<div class="container-fluid p-4" @if($seccion) data-seccion-id="{{ $seccion->seccion_id }}" @endif>

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- CASO 1: LISTADO DE SECCIONES (index)                        --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    @if (!$seccion)
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h1 class="h3 fw-bold mb-1">Corrección de Notas</h1>
                <p class="text-muted mb-0 small">
                    @if ($cicloActivo)
                        Ciclo <strong>{{ $cicloActivo->nombre_ciclo }}</strong>
                        · {{ $secciones->count() }} sección(es) disponibles
                    @else
                        No hay un ciclo activo en este momento.
                    @endif
                </p>
            </div>
            <a href="{{ route('home') }}" class="btn btn-outline-secondary">
                <i class="bi bi-house me-1"></i> Inicio
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success border-0 shadow-sm">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger border-0 shadow-sm">{{ session('error') }}</div>
        @endif

        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="tablacorrecionnotas">
                    <thead class="table-light">
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
                        @forelse ($secciones as $sec)
                            <tr>
                                <td><code>{{ $sec->materia?->codigo_materia ?? 'S/C' }}</code></td>
                                <td>
                                    <div class="fw-semibold small">{{ $sec->materia?->nombre ?? 'Sin nombre' }}</div>
                                    <small class="text-muted">
                                        {{ $sec->materia?->carrera?->nombre ?? '' }}
                                    </small>
                                </td>
                                <td>{{ $sec->nombre_grupo ?? '—' }}</td>
                                <td>{{ $sec->docente?->usuario?->name ?? '—' }}</td>
                                <td class="text-center">
                                    {{ $sec->detallesInscripcion()->where('estado_materia', 'CURSANDO')->count() }}
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('coordinador-facultad.correccion-notas.show', $sec->seccion_id) }}"
                                       class="btn btn-sm btn-danger">
                                        <i class="bi bi-pencil-square me-1"></i> Revisar notas
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-5">
                                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                    @if ($cicloActivo)
                                        No hay secciones registradas en el ciclo activo.
                                    @else
                                        No hay ciclo activo para mostrar secciones.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- CASO 2: DETALLE DE SECCIÓN (show)                           --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    @else

        {{-- Encabezado --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-start gap-3 mb-4">
            <div>
                <a href="{{ route('coordinador-facultad.correccion-notas.index') }}" class="small text-danger text-decoration-none">
                    <i class="bi bi-arrow-left"></i> Volver al listado
                </a>
                <h1 class="h4 fw-bold mt-2 mb-1">
                    <code>{{ $seccion->materia?->codigo_materia ?? 'S/C' }}</code>
                    — {{ $seccion->materia?->nombre ?? 'Sin nombre' }}
                </h1>
                <p class="text-muted small mb-0">
                    <i class="bi bi-person-badge me-1"></i>{{ $seccion->docente?->usuario?->name ?? '—' }}
                    · <i class="bi bi-people me-1"></i>Grupo {{ $seccion->nombre_grupo ?? '—' }}
                    · <i class="bi bi-calendar me-1"></i>{{ $seccion->cicloLectivo?->nombre_ciclo }}
                </p>
            </div>
            <span class="badge bg-danger p-2">
                {{ $estudiantes->count() }} estudiante(s)
            </span>
        </div>

        {{-- Alertas --}}
        @if (session('success'))
            <div class="alert alert-success border-0 shadow-sm">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger border-0 shadow-sm">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger border-0 shadow-sm">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                </ul>
            </div>
        @endif

        {{-- Aviso --}}
        <div class="alert alert-info border-0 shadow-sm small">
            <i class="bi bi-info-circle-fill me-1"></i>
            Solo puedes corregir notas de secciones con ciclo <strong>ACTIVO</strong>.
            Cada cambio quedará registrado en los logs del sistema con tu usuario, la nota anterior, la nueva y el motivo.
        </div>

        {{-- Tabla de notas --}}
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" style="min-width: 220px;">Estudiante</th>
                            @foreach ($evaluacionesPorPeriodo as $periodoData)
                                @foreach ($periodoData['categorias'] as $categoria)
                                    @foreach ($categoria->evaluaciones as $evaluacion)
                                        <th class="text-center small" style="min-width: 120px;">
                                            <div>{{ $evaluacion->nombre_evaluacion }}</div>
                                            <small class="text-muted fw-normal">
                                                {{ $categoria->tipo_categoria }} · P{{ $periodoData['periodo']->numero_periodo }}
                                            </small>
                                        </th>
                                    @endforeach
                                @endforeach
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($estudiantes as $est)
                            <tr>
                                <td class="ps-3">
                                    <div class="fw-semibold small">{{ $est->nombre }}</div>
                                    <small class="text-muted">{{ $est->codigo }}</small>
                                </td>
                                @foreach ($evaluacionesPorPeriodo as $periodoData)
                                    @foreach ($periodoData['categorias'] as $categoria)
                                        @foreach ($categoria->evaluaciones as $evaluacion)
                                            @php
                                                $n = $est->notas[$evaluacion->evaluacion_id] ?? null;
                                            @endphp
                                            <td class="text-center">
                                                @if ($n)
                                                    <div class="d-flex align-items-center justify-content-center gap-1">
                                                        <span class="fw-bold">{{ number_format($n['nota'], 1) }}</span>
                                                        @if ($n['es_diferida'])
                                                            <span class="badge text-bg-info" style="font-size:0.6rem;">D</span>
                                                        @endif
                                                        <button type="button"
                                                                class="btn btn-sm btn-outline-danger p-1"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#modalCorregir"
                                                                data-calificacion-id="{{ $n['calificacion_id'] }}"
                                                                data-nota-actual="{{ $n['nota'] }}"
                                                                data-evaluacion="{{ $evaluacion->nombre_evaluacion }}"
                                                                data-estudiante="{{ $est->nombre }}"
                                                                data-action="{{ route('coordinador-facultad.correccion-notas.update', [$seccion->seccion_id, $n['calificacion_id']]) }}"
                                                                title="Corregir nota">
                                                            <i class="bi bi-pencil" style="font-size: 0.7rem;"></i>
                                                        </button>
                                                    </div>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                        @endforeach
                                    @endforeach
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="100" class="text-center text-muted py-5">
                                    No hay estudiantes inscritos en esta sección.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Modal de corrección --}}
        <div class="modal fade" id="modalCorregir" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <form method="POST" id="formCorregir" novalidate>
                        @csrf
                        @method('PUT')

                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title">
                                <i class="bi bi-pencil-square me-1"></i> Corregir calificación
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body p-4">
                            <p class="small text-muted mb-3">
                                <strong>Estudiante:</strong> <span id="mcEstudiante"></span><br>
                                <strong>Evaluación:</strong> <span id="mcEvaluacion"></span>
                            </p>

                            <div class="mb-3">
                                <label class="form-label small fw-bold">Nota actual</label>
                                <input type="text" class="form-control" id="mcNotaActual" disabled>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold">
                                    Nueva nota <span class="text-danger">*</span>
                                </label>
                                <input type="number" name="nota" id="mcNuevaNota"
                                       class="form-control @error('nota') is-invalid @enderror"
                                       step="0.1" min="0" max="10" required
                                       placeholder="0.0">
                                @error('nota') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-0">
                                <label class="form-label small fw-bold">
                                    Motivo de la corrección <span class="text-danger">*</span>
                                </label>
                                <textarea name="motivo" id="mcMotivo"
                                          class="form-control @error('motivo') is-invalid @enderror"
                                          rows="3" minlength="5" maxlength="500" required
                                          placeholder="Ej: Error de digitación al ingresar la nota del laboratorio 1.">{{ old('motivo') }}</textarea>
                                @error('motivo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                Cancelar
                            </button>
                            <button type="submit" class="btn btn-danger">
                                <i class="bi bi-check-circle me-1"></i> Guardar corrección
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    @endif

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('modalCorregir');
        if (!modal) return;

        modal.addEventListener('show.bs.modal', function (event) {
            const btn = event.relatedTarget;
            if (!btn) return;

            document.getElementById('formCorregir').action       = btn.getAttribute('data-action');
            document.getElementById('mcEstudiante').textContent  = btn.getAttribute('data-estudiante');
            document.getElementById('mcEvaluacion').textContent  = btn.getAttribute('data-evaluacion');
            document.getElementById('mcNotaActual').value        = btn.getAttribute('data-nota-actual');
            document.getElementById('mcNuevaNota').value         = '';
            document.getElementById('mcMotivo').value            = '';
        });

        document.getElementById('formCorregir').addEventListener('submit', function () {
            const btn = this.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Guardando...';
        });
    });
</script>
@endpush