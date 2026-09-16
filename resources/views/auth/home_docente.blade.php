@extends('layouts.app')

@section('title', 'Mis Materias - NEXUM')

@section('content')
<div class="container-fluid p-4">

    <!-- Encabezado de página -->
    <div class="mb-4">
        <h1 class="h3 fw-bold mb-1">Mis Materias</h1>
        <p class="text-muted mb-0">Gestiona las materias asignadas y registra las calificaciones de tus estudiantes.</p>
    </div>

    <!-- Recordatorio General -->
    <div class="alert border-0 shadow-sm rounded-4 p-3 mb-4" style="background-color: #fff5f5; border-left: 4px solid #dc3545 !important;">
        <div class="d-flex align-items-start">
            <div class="d-inline-flex align-items-center justify-content-center rounded-circle me-3 flex-shrink-0"
                style="width: 40px; height: 40px; background-color: rgba(220, 53, 69, 0.1);">
                <i class="fa-solid fa-bell text-danger"></i>
            </div>
            <div class="flex-grow-1">
                <h6 class="fw-bold mb-1 text-danger">
                    <i class="fa-solid fa-triangle-exclamation me-1"></i>
                    Recordatorio Importante
                </h6>
                <p class="mb-0 small text-dark">
                    Recuerda registrar las calificaciones de tus estudiantes dentro de los plazos establecidos por las autoridades de la universidad.
                    El incumplimiento de los tiempos podría afectar el proceso académico de los estudiantes.
                </p>
            </div>
        </div>
    </div>

    <!-- Sección de Materias -->
    <div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h5 class="fw-bold mb-0">Materias Asignadas</h5>
            <small class="text-muted">Ciclo académico actual · 3 materias</small>
        </div>
    </div>

    <!-- Grid de Materias -->
    <div class="row g-4">
        <!-- Materia 1 -->
        <div class="col-12 col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle me-3"
                            style="width: 48px; height: 48px; background-color: #fff5f5;">
                            <i class="fa-solid fa-square-root-variable fs-5 text-danger"></i>
                        </div>
                        <div>
                            <span class="badge bg-light text-muted border small mb-1">MAT-101</span>
                            <h5 class="fw-bold mb-0">Matemáticas I</h5>
                        </div>
                    </div>

                    <p class="text-muted small mb-3">
                        Fundamentos de álgebra, cálculo diferencial e integral aplicados a la resolución de problemas académicos y profesionales.
                    </p>

                    <div class="d-flex flex-column gap-2 mb-4 pb-3 border-bottom">
                        <div class="d-flex align-items-center small">
                            <i class="fa-solid fa-users me-2 text-muted" style="width: 16px;"></i>
                            <span><strong>32</strong> estudiantes inscritos</span>
                        </div>
                        <div class="d-flex align-items-center small">
                            <i class="fa-solid fa-clock me-2 text-muted" style="width: 16px;"></i>
                            <span>Lun, Mié, Vie · 8:00 - 9:30 AM</span>
                        </div>
                        <div class="d-flex align-items-center small">
                            <i class="fa-solid fa-location-dot me-2 text-muted" style="width: 16px;"></i>
                            <span>Aula 204 · Edificio Central</span>
                        </div>
                    </div>

                    <a href="#" class="btn btn-danger w-100 py-2">
                        <i class="fa-solid fa-pen-to-square me-2"></i>Asignar Notas
                    </a>
                </div>
            </div>
        </div>

        <!-- Materia 2 -->
        <div class="col-12 col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle me-3"
                            style="width: 48px; height: 48px; background-color: #fff5f5;">
                            <i class="fa-solid fa-code fs-5 text-danger"></i>
                        </div>
                        <div>
                            <span class="badge bg-light text-muted border small mb-1">INF-205</span>
                            <h5 class="fw-bold mb-0">Programación Web</h5>
                        </div>
                    </div>

                    <p class="text-muted small mb-3">
                        Desarrollo de aplicaciones web modernas utilizando tecnologías frontend y backend, buenas prácticas y estándares actuales.
                    </p>

                    <div class="d-flex flex-column gap-2 mb-4 pb-3 border-bottom">
                        <div class="d-flex align-items-center small">
                            <i class="fa-solid fa-users me-2 text-muted" style="width: 16px;"></i>
                            <span><strong>28</strong> estudiantes inscritos</span>
                        </div>
                        <div class="d-flex align-items-center small">
                            <i class="fa-solid fa-clock me-2 text-muted" style="width: 16px;"></i>
                            <span>Mar, Jue · 10:00 - 11:30 AM</span>
                        </div>
                        <div class="d-flex align-items-center small">
                            <i class="fa-solid fa-location-dot me-2 text-muted" style="width: 16px;"></i>
                            <span>Lab. Cómputo 3 · Edificio TIC</span>
                        </div>
                    </div>

                    <a href="#" class="btn btn-danger w-100 py-2">
                        <i class="fa-solid fa-pen-to-square me-2"></i>Asignar Notas
                    </a>
                </div>
            </div>
        </div>

        <!-- Materia 3 -->
        <div class="col-12 col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle me-3"
                            style="width: 48px; height: 48px; background-color: #fff5f5;">
                            <i class="fa-solid fa-database fs-5 text-danger"></i>
                        </div>
                        <div>
                            <span class="badge bg-light text-muted border small mb-1">INF-301</span>
                            <h5 class="fw-bold mb-0">Base de Datos</h5>
                        </div>
                    </div>

                    <p class="text-muted small mb-3">
                        Diseño, implementación y administración de bases de datos relacionales y no relacionales para aplicaciones empresariales.
                    </p>

                    <div class="d-flex flex-column gap-2 mb-4 pb-3 border-bottom">
                        <div class="d-flex align-items-center small">
                            <i class="fa-solid fa-users me-2 text-muted" style="width: 16px;"></i>
                            <span><strong>25</strong> estudiantes inscritos</span>
                        </div>
                        <div class="d-flex align-items-center small">
                            <i class="fa-solid fa-clock me-2 text-muted" style="width: 16px;"></i>
                            <span>Lun, Mié · 2:00 - 3:30 PM</span>
                        </div>
                        <div class="d-flex align-items-center small">
                            <i class="fa-solid fa-location-dot me-2 text-muted" style="width: 16px;"></i>
                            <span>Lab. Cómputo 1 · Edificio TIC</span>
                        </div>
                    </div>

                    <a href="#" class="btn btn-danger w-100 py-2">
                        <i class="fa-solid fa-pen-to-square me-2"></i>Asignar Notas
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer informativo -->
    <div class="text-center mt-5 pt-3">
        <p class="text-muted small mb-0">
            <i class="fa-solid fa-circle-info me-1"></i>
            ¿Tienes alguna duda o inconveniente con tus materias? Contacta a coordinación académica.
        </p>
    </div>

</div>
@endsection