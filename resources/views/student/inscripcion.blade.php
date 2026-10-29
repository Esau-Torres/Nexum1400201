@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h4 fw-bold mb-0">INSCRIPCIÓN: {{ $ciclo->nombre_ciclo }}</h2>
        <span class="badge bg-danger">
            Máx. {{ $validacionGlobal['limite_materias'] ?? 4 }} materias
            ({{ $validacionGlobal['materias_inscritas'] ?? 0 }} inscritas)
        </span>
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-bs-toggle="tooltip"]')
        .forEach(el => new bootstrap.Tooltip(el));
});
</script>
@endpush
@endsection