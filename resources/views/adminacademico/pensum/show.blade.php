@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">

    {{-- HEADER --}}
    <div class="card p-4 mb-4">
        <div class="card-body d-flex flex-wrap justify-content-between align-items-start gap-3">
            <!-- Información Principal del Pensum -->
            <div>
                <h1 class="h3 fw-bold text-dark mb-2">{{ $pensum->nombre }}</h1>
                <p class="text-muted mb-0 small d-flex flex-wrap align-items-center">
                    <span class="d-inline-flex align-items-center me-2">
                        <i class="bi bi-mortarboard me-1 text-primary"></i>{{ $pensum->carrera?->nombre }}
                    </span>
                    <span class="mx-1 text-secondary">·</span>
                    <span class="d-inline-flex align-items-center mx-2">
                        <i class="bi bi-building me-1 text-primary"></i>{{ $pensum->carrera?->facultad?->nombre }}
                    </span>
                    <span class="mx-1 text-secondary">·</span>
                    <span class="d-inline-flex align-items-center ms-2">
                        <i class="bi bi-calendar-event me-1 text-primary"></i>Vigencia: {{ $pensum->anio_vigencia }}
                    </span>
                </p>
            </div>

            <!-- Badge de Estado Neumórfico (Soft UI) -->
            <div class="d-flex align-items-center">
                <a href="{{ route('admin-academico.pensum.pensum') }}" class="btn bg-danger-subtle text-danger border border-danger px-3 py-2 mb-4">
                    <i class="bi bi-arrow-left me-1"></i>Volver al listado
                </a>
            </div>
        </div>
    </div>

    {{-- RESUMEN --}}
    <div class="row g-3 mb-4">
        <!-- Tarjeta: Total Materias -->
        <div class="col-md-3">
            <div class="card border stat-card stat-card-light rounded-4 p-2 h-100 shadow-sm">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <div class="stat-label-small text-muted mb-1">Total Materias</div>
                        <div class="stat-value-big text-dark mb-0">{{ $pensum->totalMaterias() }}</div>
                    </div>
                    <div class="stat-icon-circle">
                        <i class="bi bi-journal-richtext"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tarjeta: Total UV -->
        <div class="col-md-3">
            <div class="card border stat-card stat-card-light rounded-4 p-2 h-100 shadow-sm">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <div class="stat-label-small text-muted mb-1">Total UV</div>
                        <div class="stat-value-big text-dark mb-0">{{ $pensum->totalUV() }}</div>
                    </div>
                    <div class="stat-icon-circle">
                        <i class="bi bi-award"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tarjeta: Ciclos Usados -->
        <div class="col-md-3">
            <div class="card border stat-card stat-card-light rounded-4 p-2 h-100 shadow-sm">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <div class="stat-label-small text-muted mb-1">Ciclos Usados</div>
                        <div class="stat-value-big text-dark mb-0 fs-3">{{ count($pensum->ciclosUsados()) }} <span class="fs-5 text-muted fw-normal">/ {{ $pensum->carrera?->duracion_ciclos }}</span></div>
                    </div>
                    <div class="stat-icon-circle">
                        <i class="bi bi-calendar-range"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tarjeta: Estado del Pensum -->
        <div class="col-md-3">
            <div class="card border stat-card stat-card-light rounded-4 p-2 h-100 shadow-sm">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <div class="stat-label-small text-muted mb-1">Estado</div>
                        <div class="mt-1">
                            <span class="badge {{ $pensum->estado->badge() }} px-3 py-2 fs-6 shadow-sm">
                                {{ $pensum->estado->label() }}
                            </span>
                        </div>
                    </div>
                    <div class="stat-icon-circle">
                        <i class="bi bi-shield-check"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MALLA CURRICULAR --}}
    <h5 class="fw-bold text-dark mb-3">
        <i class="bi bi-grid-3x3-gap-fill text-danger me-2"></i>PENSUM CURRICULAR
    </h5>

    @php
        $duracion = $pensum->carrera?->duracion_ciclos ?? 10;
        $porCiclo = $pensum->pensumMaterias->groupBy('ciclo_sugerido');
    @endphp

    <div class="row g-3">
        @for ($ciclo = 1; $ciclo <= $duracion; $ciclo++)
            @php $materiasCiclo = $porCiclo->get($ciclo, collect()); @endphp
            <div class="col-md-6 col-lg-4 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-bottom-0 pt-3 pb-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-dark">Ciclo {{ $ciclo }}</span>
                            <span class="badge {{ $materiasCiclo->count() >= 4 && $materiasCiclo->count() <= 5 ? 'text-bg-success' : 'text-bg-warning' }}">
                                {{ $materiasCiclo->count() }} materias
                            </span>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        @forelse ($materiasCiclo as $pm)
                            <div class="border rounded-3 p-2 mb-2">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold small text-dark">
                                            {{ $pm->materia->nombre }}
                                        </div>
                                        <div class="text-muted small font-monospace">
                                            {{ $pm->materia->codigo_materia }}
                                        </div>
                                    </div>
                                    <span class="badge text-bg-light border small">{{ $pm->materia->unidades_valorativas }} UV</span>
                                </div>

                                @if ($pm->prerequisitos->isNotEmpty())
                                    <div class="mt-1 pt-1 border-top">
                                        <div class="small text-muted mb-1">Prereq:</div>
                                        @foreach ($pm->prerequisitos as $pr)
                                            <span class="badge text-bg-light border small me-1 mb-1">
                                                {{ $pr->materiaRequisito?->codigo_materia ?? '—' }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif

                                @unless ($pm->es_obligatoria)
                                    <span class="badge text-bg-info small mt-1">Optativa</span>
                                @endunless
                            </div>
                        @empty
                            <div class="text-center py-3 text-muted small fst-italic">
                                Ciclo vacío
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        @endfor
    </div>

    {{-- ACCIONES --}}
    <div class="mt-4 d-flex justify-content-end gap-2">
        @if ($pensum->estado === \App\Enums\EstadoPensum::MODIFICADO)
            <a href="{{ route('admin-academico.pensum.edit', $pensum) }}" class="btn btn-danger">
                <i class="bi bi-pencil-square me-1"></i>Modificar Pensum
            </a>
        @endif
    </div>
</div>
@endsection