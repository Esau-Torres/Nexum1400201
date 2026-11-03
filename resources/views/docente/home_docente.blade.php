@extends('layouts.app')

@section('title', 'Inicio - Docente | NEXUM')

@section('content')
<div class="dashboard-wrapper">
    <div class="container-fluid p-4 p-lg-4 d-flex flex-column" style="min-height: 100vh;">

        <!-- ENCABEZADO (se queda fijo arriba) -->
        <div class="mx-auto w-100 mb-4" style="max-width: 850px;">
            <p class="text-muted small text-uppercase fw-semibold mb-1" style="letter-spacing: 1px;">Panel del Docente</p>
            <h1 class="display-6 fw-bold mb-2" style="letter-spacing: -0.5px;">
                Hola, {{ auth()->user()->name }}
                <i class="fa-solid fa-hand-sparkles text-danger ms-2"></i>
            </h1>
            <div class="d-flex flex-wrap gap-2">
                <span class="badge rounded-pill px-3 py-2 bg-danger bg-opacity-10 text-danger fw-medium">
                    <i class="fa-solid fa-graduation-cap me-1"></i>{{ $cicloActual }}
                </span>
                <span class="badge rounded-pill px-3 py-2 bg-white text-muted fw-medium border">
                    <i class="fa-solid fa-calendar me-1"></i>{{ now()->isoFormat('dddd, D [de] MMMM [de] YYYY') }}
                </span>
            </div>
            @if(session('success'))
            <div class="alert alert-success border-0 shadow-sm rounded-4 mt-3">
                <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
            </div>
            @endif
        </div>

        <!-- GRID DE TARJETAS 2x2 (centrado verticalmente en el espacio restante) -->
        <div class="mx-auto w-100 my-auto" style="max-width: 850px;">
            <div class="row g-3">

                <!-- Card 1: Materias (Blanca - CLICKEABLE) -->
                <div class="col-12 col-md-6">
                    <a href="{{ route('docente.materias') }}" class="text-decoration-none d-block h-100">
                        <div class="card border-0 shadow-sm rounded-4 h-100 dashboard-card-compact card-materias" style="min-height: 220px;">
                            <div class="card-body d-flex flex-column justify-content-between p-4">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="icon-square-compact bg-danger-soft text-danger">
                                        <i class="fa-solid fa-chalkboard-user"></i>
                                    </div>
                                    <div class="arrow-icon text-danger">
                                        <i class="fa-solid fa-arrow-right"></i>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <div class="card-number-compact text-dark">{{ $totalMaterias }}</div>
                                    <div class="card-label-compact text-dark fw-semibold mb-1">Materias asignadas</div>
                                    <div class="card-subtext-compact text-muted">este ciclo</div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Card 2: Estudiantes (Roja) -->
                <div class="col-12 col-md-6">
                    <div class="card border-0 shadow rounded-4 h-100 dashboard-card-compact card-estudiantes" style="min-height: 220px;">
                        <div class="card-body d-flex flex-column justify-content-between p-4">
                            <div class="icon-square-compact icon-glass-white">
                                <i class="fa-solid fa-users"></i>
                            </div>
                            <div class="mt-3">
                                <div class="card-number-compact text-white">{{ $totalEstudiantes }}</div>
                                <div class="card-label-compact text-white fw-semibold mb-1">Estudiantes a cargo</div>
                                <div class="card-subtext-compact text-white-50">inscritos</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 3: Clases (Teal elegante) -->
                <div class="col-12 col-md-6">
                    <div class="card border-0 shadow rounded-4 h-100 dashboard-card-compact card-clases" style="min-height: 220px;">
                        <div class="card-body d-flex flex-column justify-content-between p-4">
                            <div class="icon-square-compact icon-glass-white">
                                <i class="fa-solid fa-calendar-week"></i>
                            </div>
                            <div class="mt-3">
                                <div class="card-number-compact text-white">{{ $clasesSemana }}</div>
                                <div class="card-label-compact text-white fw-semibold mb-1">Clases esta semana</div>
                                <div class="card-subtext-compact text-white-50">sesiones</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 4: Evaluaciones (Amber cálido) -->
                <div class="col-12 col-md-6">
                    <div class="card border-0 shadow-sm rounded-4 h-100 dashboard-card-compact card-evaluaciones" style="min-height: 220px;">
                        <div class="card-body d-flex flex-column justify-content-between p-4">
                            <div class="icon-square-compact icon-glass-dark">
                                <i class="fa-solid fa-clipboard-check"></i>
                            </div>
                            <div class="mt-3">
                                <div class="card-number-compact text-dark">{{ $evaluacionesPendientes }}</div>
                                <div class="card-label-compact text-dark fw-semibold mb-1">Evaluaciones pendientes</div>
                                <div class="card-subtext-compact text-dark opacity-75">atención</div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>
@endsection