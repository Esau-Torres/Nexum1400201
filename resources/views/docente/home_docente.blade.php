@extends('layouts.app')

@section('title', 'Inicio - Docente | NEXUM')

@section('content')
<div class="container-fluid p-4 p-lg-5">
    <div class="mx-auto" style="max-width: 1400px;">

        <!-- ============================================ -->
        <!-- ENCABEZADO SIMPLE                            -->
        <!-- ============================================ -->
        <div class="mb-5">
            <p class="text-muted small text-uppercase fw-semibold mb-2" style="letter-spacing: 1px;">Panel del Docente</p>
            <h1 class="display-4 fw-bold mb-3" style="letter-spacing: -0.5px;">
                Hola, Ing. {{ auth()->user()->name }}
                <i class="fa-solid fa-hand-sparkles text-danger ms-2"></i>
            </h1>
            <div class="d-flex flex-wrap gap-2">
                <span class="badge rounded-pill px-3 py-2 periodo-badge">
                    <i class="fa-solid fa-graduation-cap me-1"></i>Ciclo 02-2026
                </span>
                <span class="badge rounded-pill px-3 py-2 periodo-badge">
                    <i class="fa-solid fa-chart-line me-1"></i>Período 2 — 35%
                </span>
            </div>
        </div>

        <!-- ============================================ -->
        <!-- GRID DE TARJETAS RESUMEN (más llamativas)    -->
        <!-- ============================================ -->
        <div class="row g-3 g-lg-4 mb-5">
            <!-- Card 1: Materias (blanca) -->
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm rounded-4 stat-card stat-card-light h-100">
                    <div class="card-body p-4">
                        <div class="stat-icon-circle mb-3">
                            <i class="fa-solid fa-chalkboard-user"></i>
                        </div>
                        <div class="stat-label-small">Materias asignadas</div>
                        <div class="stat-value-big">3</div>
                        <div class="stat-subtext">este ciclo</div>
                    </div>
                </div>
            </div>

            <!-- Card 2: Estudiantes (ROJA - destacada) -->
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow rounded-4 stat-card stat-card-primary h-100">
                    <div class="card-body p-4 text-white">
                        <div class="stat-icon-circle-white mb-3">
                            <i class="fa-solid fa-users"></i>
                        </div>
                        <div class="stat-label-small text-white-50">Estudiantes a cargo</div>
                        <div class="stat-value-big text-white">85</div>
                        <div class="stat-subtext text-white-50">en 3 secciones</div>
                    </div>
                </div>
            </div>

            <!-- Card 3: Clases semana (blanca) -->
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm rounded-4 stat-card stat-card-light h-100">
                    <div class="card-body p-4">
                        <div class="stat-icon-circle mb-3">
                            <i class="fa-solid fa-calendar-week"></i>
                        </div>
                        <div class="stat-label-small">Clases esta semana</div>
                        <div class="stat-value-big">6</div>
                        <div class="stat-subtext">sesiones programadas</div>
                    </div>
                </div>
            </div>

            <!-- Card 4: Pendientes (AMARILLA suave - alerta) -->
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm rounded-4 stat-card stat-card-warning h-100">
                    <div class="card-body p-4">
                        <div class="stat-icon-circle-warning mb-3">
                            <i class="fa-solid fa-bell"></i>
                        </div>
                        <div class="stat-label-small">Evaluaciones pendientes</div>
                        <div class="stat-value-big">2</div>
                        <div class="stat-subtext">requieren tu atención</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================ -->
        <!-- MIS MATERIAS                                 -->
        <!-- ============================================ -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1">
                    <i class="fa-solid fa-book text-danger me-2"></i>
                    Mis Materias
                </h3>
                <p class="text-muted small mb-0">Gestiona tus clases del ciclo actual</p>
            </div>
            <a href="#" class="text-decoration-none small text-danger fw-semibold">
                Ver todas <i class="fa-solid fa-arrow-right ms-1"></i>
            </a>
        </div>

        <div class="row g-3">
            <!-- Materia 1: Programación I -->
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4 materia-card">
                    <div class="card-body p-4">
                        <div class="row align-items-center g-3">
                            <!-- Info materia -->
                            <div class="col-md-6">
                                <div class="d-flex align-items-center mb-2">
                                    <span class="badge rounded-pill bg-danger me-2">Grupo A</span>
                                    <small class="text-muted fw-medium">INF-101</small>
                                </div>
                                <h5 class="fw-bold mb-2">Programación I</h5>
                                <div class="d-flex flex-wrap gap-3 small text-muted">
                                    <span>
                                        <i class="fa-solid fa-users me-1 text-danger"></i>
                                        <strong class="text-dark">28</strong> estudiantes
                                    </span>
                                    <span>
                                        <i class="fa-solid fa-location-dot me-1 text-danger"></i>
                                        Aula 12
                                    </span>
                                </div>
                            </div>

                            <!-- Horario -->
                            <div class="col-md-2 text-center">
                                <div class="horario-badge">
                                    <div class="fw-semibold small">Lun - Mié</div>
                                    <div class="text-muted" style="font-size: 0.75rem;">07:00 - 08:30</div>
                                </div>
                            </div>

                            <!-- Acciones -->
                            <div class="col-md-4">
                                <div class="d-flex flex-wrap gap-2 justify-content-md-end">
                                    <a href="#" class="btn btn-sm btn-outline-danger rounded-3 flex-fill flex-md-grow-0">
                                        <i class="fa-solid fa-clipboard-check me-1"></i>
                                        <span class="d-none d-xl-inline">Asistencia</span>
                                    </a>
                                    <a href="#" class="btn btn-sm btn-outline-danger rounded-3 flex-fill flex-md-grow-0">
                                        <i class="fa-solid fa-file-pen me-1"></i>
                                        <span class="d-none d-xl-inline">Notas</span>
                                    </a>
                                    <a href="#" class="btn btn-sm btn-outline-danger rounded-3 flex-fill flex-md-grow-0">
                                        <i class="fa-solid fa-users me-1"></i>
                                        <span class="d-none d-xl-inline">Estudiantes</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Materia 2: Base de Datos -->
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4 materia-card">
                    <div class="card-body p-4">
                        <div class="row align-items-center g-3">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center mb-2">
                                    <span class="badge rounded-pill bg-danger me-2">Grupo B</span>
                                    <small class="text-muted fw-medium">INF-205</small>
                                </div>
                                <h5 class="fw-bold mb-2">Base de Datos</h5>
                                <div class="d-flex flex-wrap gap-3 small text-muted">
                                    <span>
                                        <i class="fa-solid fa-users me-1 text-danger"></i>
                                        <strong class="text-dark">32</strong> estudiantes
                                    </span>
                                    <span>
                                        <i class="fa-solid fa-location-dot me-1 text-danger"></i>
                                        Aula 08
                                    </span>
                                </div>
                            </div>

                            <div class="col-md-2 text-center">
                                <div class="horario-badge">
                                    <div class="fw-semibold small">Mar - Jue</div>
                                    <div class="text-muted" style="font-size: 0.75rem;">09:00 - 10:30</div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="d-flex flex-wrap gap-2 justify-content-md-end">
                                    <a href="#" class="btn btn-sm btn-outline-danger rounded-3 flex-fill flex-md-grow-0">
                                        <i class="fa-solid fa-clipboard-check me-1"></i>
                                        <span class="d-none d-xl-inline">Asistencia</span>
                                    </a>
                                    <a href="#" class="btn btn-sm btn-outline-danger rounded-3 flex-fill flex-md-grow-0">
                                        <i class="fa-solid fa-file-pen me-1"></i>
                                        <span class="d-none d-xl-inline">Notas</span>
                                    </a>
                                    <a href="#" class="btn btn-sm btn-outline-danger rounded-3 flex-fill flex-md-grow-0">
                                        <i class="fa-solid fa-users me-1"></i>
                                        <span class="d-none d-xl-inline">Estudiantes</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Materia 3: Desarrollo Web -->
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4 materia-card">
                    <div class="card-body p-4">
                        <div class="row align-items-center g-3">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center mb-2">
                                    <span class="badge rounded-pill bg-danger me-2">Grupo A</span>
                                    <small class="text-muted fw-medium">INF-310</small>
                                </div>
                                <h5 class="fw-bold mb-2">Desarrollo Web</h5>
                                <div class="d-flex flex-wrap gap-3 small text-muted">
                                    <span>
                                        <i class="fa-solid fa-users me-1 text-danger"></i>
                                        <strong class="text-dark">25</strong> estudiantes
                                    </span>
                                    <span>
                                        <i class="fa-solid fa-location-dot me-1 text-danger"></i>
                                        Aula 15
                                    </span>
                                </div>
                            </div>

                            <div class="col-md-2 text-center">
                                <div class="horario-badge">
                                    <div class="fw-semibold small">Mié - Vie</div>
                                    <div class="text-muted" style="font-size: 0.75rem;">11:00 - 12:30</div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="d-flex flex-wrap gap-2 justify-content-md-end">
                                    <a href="#" class="btn btn-sm btn-outline-danger rounded-3 flex-fill flex-md-grow-0">
                                        <i class="fa-solid fa-clipboard-check me-1"></i>
                                        <span class="d-none d-xl-inline">Asistencia</span>
                                    </a>
                                    <a href="#" class="btn btn-sm btn-outline-danger rounded-3 flex-fill flex-md-grow-0">
                                        <i class="fa-solid fa-file-pen me-1"></i>
                                        <span class="d-none d-xl-inline">Notas</span>
                                    </a>
                                    <a href="#" class="btn btn-sm btn-outline-danger rounded-3 flex-fill flex-md-grow-0">
                                        <i class="fa-solid fa-users me-1"></i>
                                        <span class="d-none d-xl-inline">Estudiantes</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection