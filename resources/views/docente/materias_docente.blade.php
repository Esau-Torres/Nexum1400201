@extends('layouts.app')

@section('title', 'Mis Materias - Docente | NEXUM')

@section('content')
<div class="container-fluid p-4 p-lg-5">
    <div class="mx-auto" style="max-width: 1200px;">

        <!-- ENCABEZADO -->
        <div class="text-left mb-5">
            <h1 class="display-5 fw-bold mb-2" style="letter-spacing: -0.5px;">Tus Materias</h1>
            <p class="text-muted fs-6 mb-0">Gestiona tus materias asignadas del ciclo actual</p>
        </div>

        <!-- GRID DE MATERIAS -->
        <div class="row g-4">
            @forelse($materias as $materia)
            <div class="col-12 col-md-6">
                <div class="card border-0 shadow-sm rounded-4 materia-large-card h-100">
                    <div class="card-body p-4 p-lg-5">
                        <!-- Título + Grupo -->
                        <div class="mb-3">
                            <h3 class="fw-bold mb-2">{{ $materia->nombre }}</h3>
                            <div class="d-flex flex-wrap gap-2">
                                <span class="badge bg-danger rounded-pill">{{ $materia->grupo }}</span>
                                <span class="badge bg-light text-danger border rounded-pill">{{ $materia->codigo }}</span>
                            </div>
                        </div>

                        <!-- Descripción -->
                        <p class="materia-description">
                            {{ $materia->descripcion }}
                        </p>

                        <!-- Stats -->
                        <div class="materia-stats mb-4">
                            <div class="d-flex align-items-center mb-2">
                                <i class="fa-solid fa-users me-2 text-danger"></i>
                                <span class="small"><strong>{{ $materia->estudiantes }}</strong> estudiantes inscritos</span>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <i class="fa-solid fa-location-dot me-2 text-danger"></i>
                                <span class="small">{{ $materia->aula }}</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <i class="fa-solid fa-clock me-2 text-danger"></i>
                                <span class="small">{{ $materia->horario }}</span>
                            </div>
                        </div>

                        <!-- Acciones -->
                        <div class="d-flex flex-column flex-sm-row gap-2">
                            <a href="{{ route('docente.asistencia', $materia->id) }}" class="btn btn-outline-danger rounded-3 flex-fill materia-btn-large">
                                <i class="fa-solid fa-clipboard-check me-2"></i>Asistencia
                            </a>
                            <a href="{{ route('docente.notas', $materia->id) }}" class="btn btn-outline-danger rounded-3 flex-fill materia-btn-large">
                                <i class="fa-solid fa-file-pen me-2"></i>Notas
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <!-- Estado vacío: cuando el docente no tiene materias asignadas -->
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-5 text-center">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3"
                            style="width: 72px; height: 72px; background-color: #fff5f5;">
                            <i class="fa-solid fa-book-open fs-2 text-danger"></i>
                        </div>
                        <h5 class="fw-bold mb-2">No tienes materias asignadas</h5>
                        <p class="text-muted mb-0">Actualmente no tienes materias registradas para este ciclo.</p>
                    </div>
                </div>
            </div>
            @endforelse
        </div>

    </div>
</div>
@endsection