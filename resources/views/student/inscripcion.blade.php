@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-md-between gap-3 p-3 mb-4 rounded-4 card">
        <div class="d-flex align-items-center">
            <div class="px-3 py-2 rounded-3 bg-danger bg-opacity-10" style="width:48px;height:48px;">
                <i class="bi bi-journal-bookmark-fill fs-4 text-danger"></i>
            </div>
            <div class="px-3">
                <h2 class="h4 fw-bold mb-0">INSCRIPCIÓN DE ASIGNATURAS</h2>
                <p class="text-muted mb-0 small">Inscripción de asignaturas acadecimas del  <span class="bg-danger bg-opacity-10 border-danger p-2 mb-2 rounded border  bg-light border-light"> {{ $ciclo->nombre_ciclo }}</span></p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <span class="badge bg-danger">
                Máx. {{ $validacionGlobal['limite_materias'] ?? 4 }} materias
                ({{ $validacionGlobal['materias_inscritas'] ?? 0 }} inscritas)
            </span>
        </div>
    </div>

    {{-- Aviso de solvencia --}}
    @if(!$validacionGlobal['solvente'])
        <div class="alert alert-warning border-0 shadow-sm mb-4">
            <div class="d-flex align-items-start gap-2">
                <i class="bi bi-exclamation-triangle-fill fs-4"></i>
                <div>
                    <strong class="d-block mb-1">No puedes inscribirte aún</strong>
                    <ul class="mb-0 ps-3">
                        @foreach($validacionGlobal['motivos'] as $m)
                            <li>{{ $m }}</li>
                        @endforeach
                    </ul>
                    <small class="text-muted">Regulariza tu situación financiera para inscribirte.</small>
                </div>
            </div>
        </div>
    @endif

    {{-- Si no hay ciclos con secciones disponibles --}}
    @if($seccionesPorCiclo->isEmpty())
        <div class="alert alert-info border-0 shadow-sm">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-info-circle-fill fs-4"></i>
                <div>
                    <strong class="d-block mb-1">No hay materias disponibles para inscripción</strong>
                    <small class="text-muted">
                        No hay secciones activas en el ciclo lectivo actual para las materias de tu pensum.
                        Consulta con Administración Académica.
                    </small>
                </div>
            </div>
        </div>
    @else
        @foreach($seccionesPorCiclo as $cicloData)
            <div class="mb-4">
                <h5 class="fw-bold mb-3 text-uppercase small text-muted">
                    Ciclo {{ $cicloData['ciclo'] }}
                    @if($cicloData['es_ciclo_actual'])
                        <span class="badge bg-danger ms-2">Tu ciclo actual</span>
                    @endif
                </h5>

                <div class="row g-3">
                    @foreach($cicloData['materias'] as $materia)
                        <div class="col-md-6 col-lg-4">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="badge bg-light text-dark">{{ $materia['codigo'] }}</span>
                                        <span class="badge bg-danger">{{ $materia['uv'] }} UV</span>
                                    </div>
                                    <h6 class="fw-bold mb-1">{{ $materia['nombre'] }}</h6>
                                    <small class="text-muted d-block mb-3">
                                        Prerrequisito: {{ $materia['prerequisito'] ?? 'Ninguno' }}
                                        @if($materia['uv_minimas'] > 0)
                                            ({{ $materia['uv_minimas'] }} UV mín.)
                                        @endif
                                    </small>

                                    <hr class="my-2">

                                    {{-- Listado de secciones --}}
                                    @foreach($materia['secciones'] as $sec)
                                        <div class="p-2 mb-2 rounded border 
                                                    {{ $sec['ya_inscrito']
                                                        ? 'bg-success bg-opacity-10 border-success'
                                                        : ($sec['puede_inscribir']
                                                            ? 'bg-light border-light'
                                                            : 'bg-light bg-opacity-25 border-warning') }}">
                                            <div class="d-flex justify-content-between align-items-start gap-2">
                                                <div class="flex-grow-1">
                                                    <div class="fw-semibold small d-flex align-items-center gap-1">
                                                        Grupo {{ $sec['nombre_grupo'] }}
                                                        @if($sec['ya_inscrito'])
                                                            <i class="bi bi-check-circle-fill text-success" style="font-size: 0.75rem;"></i>
                                                        @endif
                                                    </div>
                                                    <div class="text-muted" style="font-size: 0.72rem;">
                                                        {{ $sec['docente'] }} · Cupo: {{ $sec['cupo_disponible'] }}/{{ $sec['cupo_total'] }}
                                                    </div>

                                                    @if(!empty($sec['horarios']))
                                                        <div class="mt-1" style="font-size: 0.7rem;">
                                                            @foreach($sec['horarios'] as $h)
                                                                <span class="badge bg-secondary me-1">
                                                                    {{ $h['dia'] }} {{ $h['ini'] }}-{{ $h['fin'] }}
                                                                </span>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </div>

                                                @if($sec['ya_inscrito'])
                                                    <span class="badge bg-success d-inline-flex align-items-center gap-1 py-2">
                                                        <i class="bi bi-check-circle-fill"></i>
                                                        Inscrito
                                                    </span>
                                                @elseif($sec['puede_inscribir'])
                                                    <form method="POST" action="{{ route('student.inscripcion.store') }}" class="d-inline">
                                                        @csrf
                                                        <input type="hidden" name="seccion_id" value="{{ $sec['seccion_id'] }}">
                                                        <button type="submit" class="btn btn-sm btn-danger">
                                                            Inscribir
                                                        </button>
                                                    </form>
                                                @else
                                                    <button type="button"
                                                            class="btn btn-sm btn-secondary"
                                                            data-bs-toggle="tooltip"
                                                            title="No puedes inscribirte">
                                                        Bloqueado
                                                    </button>
                                                @endif
                                            </div>

                                            @if(!$sec['ya_inscrito'] && !$sec['puede_inscribir'] && !empty($sec['bloqueadores']))
                                                <div class="mt-2 pt-2 border-top border-warning" style="font-size: 0.72rem;">
                                                    <ul class="mb-0 ps-3 text-danger">
                                                        @foreach($sec['bloqueadores'] as $b)
                                                            <li>{{ $b }}</li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    @endif
</div>

@if($matriculaCerrada)
    <div class="modal fade"
         id="modalMatriculaCerrada"
         tabindex="-1"
         data-bs-backdrop="static"
         data-bs-keyboard="false"
         aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-danger text-white border-0">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-lock-fill me-1"></i>
                        Periodo de inscripciones cerrado
                    </h5>
                </div>

                <div class="modal-body p-4 text-center">
                    <div class="mb-3">
                        <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-danger bg-opacity-10"
                              style="width: 80px; height: 80px;">
                            <i class="bi bi-calendar-x-fill fs-1 text-danger"></i>
                        </span>
                    </div>

                    <h5 class="fw-bold mb-2">No puedes inscribir materias en este momento</h5>
                    <p class="text-muted mb-3">
                        El periodo de inscripciones ha finalizado. Deberás esperar al siguiente ciclo académico
                        para realizar tu inscripción de asignaturas.
                    </p>

                    @if($ciclo)
                        <div class="alert alert-light border small mb-0 text-start">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <i class="bi bi-info-circle-fill text-danger"></i>
                                <strong>Ciclo: {{ $ciclo->nombre_ciclo }}</strong>
                            </div>
                            <div class="text-muted" style="font-size: 0.82rem;">
                                Estado actual: <span class="badge bg-secondary">{{ $ciclo->estado->label() }}</span>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="modal-footer border-0 justify-content-center">
                    <a href="{{ route('home') }}" class="btn btn-danger px-4">
                        <i class="bi bi-house-door-fill me-1"></i>
                        Volver al inicio
                    </a>
                </div>
            </div>
        </div>
    </div>
@endif

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-bs-toggle="tooltip"]')
            .forEach(el => new bootstrap.Tooltip(el));

        const modalCerrada = document.getElementById('modalMatriculaCerrada');
        if (modalCerrada) {
            const modal = new bootstrap.Modal(modalCerrada, {
                backdrop: 'static',
                keyboard: false
            });
            modal.show();
        }
    });
</script>
@endpush
@endsection