@extends('layouts.app')

@section('title', 'Desempeño Docente')

@section('content')
<div class="container-fluid p-4 p-lg-5">

    {{-- Header --}}
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-md-between gap-3 p-3 mb-4 rounded-4 card">
        <div class="d-flex align-items-center">
            <div class="px-3 py-2 rounded-3 bg-danger bg-opacity-10" style="width:48px;height:48px;">
                <i class="bi bi-clipboard2-check-fill fs-4 text-danger"></i>
            </div>
            <div class="px-3">
                <h1 class="h4 fw-bold mb-0">Desempeño Docente</h1>
                <p class="text-muted mb-0 small">
                    Evaluá a tus docentes de este ciclo lectivo
                </p>
            </div>
        </div>
        <a href="{{ route('home') }}"
           class="btn bg-danger-subtle text-danger border border-danger px-3 py-2">
            <i class="fa-solid fa-home me-1"></i> Inicio
        </a>
    </div>

    @if(!$inscripcion)
        <div class="card border-0 shadow-sm text-medium">
            <div class="card-body text-center py-5">
                <i class="bi bi-journal-x fs-1 text-muted d-block mb-3"></i>
                <h5 class="fw-bold mb-2">No tenés inscripción activa</h5>
                <p class="text-muted mb-0">
                    Una vez que te inscribas en un ciclo lectivo podrás evaluar a tus docentes.
                </p>
            </div>
        </div>
    @elseif($detalles->isEmpty())
        <div class="card border-0 shadow-sm text-medium">
            <div class="card-body text-center py-5">
                <i class="bi bi-journal fs-1 text-muted d-block mb-3"></i>
                <h5 class="fw-bold mb-2">No tenés materias cursando</h5>
                <p class="text-muted mb-0">
                    Cuando te inscribas a materias, aparecerán aquí para que puedas evaluar a los docentes.
                </p>
            </div>
        </div>
    @else
        {{-- Info del ciclo --}}
        <div class="alert alert-light border d-flex align-items-center gap-2 mb-4 text-medium">
            <i class="bi bi-calendar3 text-danger"></i>
            <div class="small">
                Ciclo lectivo:
                <strong>{{ $inscripcion->cicloLectivo->nombre_ciclo ?? '—' }}</strong>
                · {{ $detalles->count() }} materia(s) cursando
            </div>
        </div>

        <div class="row g-4">
            @foreach($detalles as $detalle)
                @php
                    $seccion = $detalle->seccion;
                    $materia = $seccion?->materia;
                    $docente = $seccion?->docente?->user;
                    $evaluacion = $evaluaciones[$detalle->id_seccion] ?? null;
                @endphp

                <div class="col-md-6 col-xl-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body p-4 text-medium">

                            {{-- Materia --}}
                            <div class="d-flex align-items-start gap-3 mb-3">
                                <div class="rounded-circle bg-danger bg-opacity-10 d-flex align-items-center justify-content-center fw-bold text-danger flex-shrink-0"
                                     style="width: 48px; height: 48px;">
                                    <i class="bi bi-book fs-5"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="fw-bold mb-1">{{ $materia->nombre ?? '—' }}</h6>
                                    <small class="text-muted d-block">
                                        {{ $materia->codigo_materia ?? '' }}
                                    </small>
                                </div>
                            </div>

                            {{-- Docente --}}
                            <div class="mb-3 pb-3 border-bottom">
                                <small class="text-muted d-block mb-1">
                                    <i class="bi bi-person-fill me-1"></i> Docente
                                </small>
                                <div class="fw-semibold small">
                                    {{ $docente->name ?? '—' }}
                                </div>
                            </div>

                            {{-- Horarios --}}
                            <div class="mb-3">
                                <small class="text-muted d-block mb-1">
                                    <i class="bi bi-clock-fill me-1"></i> Horarios
                                </small>
                                @if($seccion->horarios->isNotEmpty())
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach($seccion->horarios as $sh)
                                            <span class="badge text-bg-light border font-monospace small">
                                                {{ $sh->horario->dia->short() }}
                                                {{ substr($sh->horario->hora_inicio, 0, 5) }}–{{ substr($sh->horario->hora_fin, 0, 5) }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-muted small">Sin horario</span>
                                @endif
                            </div>

                            {{-- Botón de evaluar --}}
                            <div class="mt-3">
                                @if($evaluacion)
                                    <a href="{{ $evaluacion->url }}"
                                       target="_blank"
                                       rel="noopener noreferrer"
                                       class="btn btn-danger w-100">
                                        <i class="bi bi-clipboard2-check me-1"></i> Evaluar docente
                                    </a>
                                @else
                                    <button type="button"
                                            class="btn btn-outline-secondary w-100"
                                            disabled
                                            title="La encuesta aún no está disponible">
                                        <i class="bi bi-hourglass-split me-1"></i> Encuesta no disponible
                                    </button>
                                @endif
                            </div>

                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

</div>
@endsection