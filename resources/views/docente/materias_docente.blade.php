@extends('layouts.app')

@section('title', 'Mis Materias - Docente | NEXUM')

@section('content')
<div class="materias-wrapper">
    <div class="container-fluid p-4 p-lg-5">
        <div class="mx-auto" style="max-width: 900px;">

            <!-- ENCABEZADO -->
            <div class="d-flex align-items-center mb-4">
                <div class="bg-white rounded-circle p-3 me-3 shadow-sm">
                    <i class="fa-solid fa-chalkboard-user text-danger fs-3"></i>
                </div>
                <div>
                    <h1 class="display-6 fw-bold mb-1" style="letter-spacing: -0.5px;">Tus Materias</h1>
                    <p class="text-muted mb-0">Gestiona tus cursos asignados del ciclo actual.</p>
                </div>
            </div>

            <!-- GRID DE MATERIAS -->
            <div class="row g-3 ps-2 ps-lg-4">
                @forelse($materias as $materia)
                <div class="col-12 col-md-6">
                    <div class="card border-0 shadow-sm rounded-4 h-100 materia-card-minimal">
                        <div class="card-body p-4 d-flex flex-column">

                            <!-- Header: código como pill VERDE a la izquierda -->
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="badge bg-success text-white px-2 py-1 rounded-pill small">
                                    {{ $materia->codigo }}
                                </span>
                            </div>

                            <!-- Título grande -->
                            <h2 class="materia-nombre mb-2">{{ $materia->nombre }}</h2>

                            <!-- Descripción breve -->
                            <p class="text-muted small mb-3">{{ $materia->descripcion }}</p>

                            <!-- Stats en 2 filas: cortos arriba, horario abajo -->
                            <div class="materia-stats-stack mb-4 mt-auto">

                                <!-- Fila 1: Alumnos y Aula (stats cortos) -->
                                <div class="d-flex gap-3 mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="fa-solid fa-users text-danger small"></i>
                                        <span class="text-dark fw-medium small">{{ $materia->estudiantes }} alumnos</span>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="fa-solid fa-location-dot text-danger small"></i>
                                        <span class="text-dark fw-medium small">{{ $materia->aula }}</span>
                                    </div>
                                </div>

                                <!-- Fila 2: Horario (con espacio propio, se ajusta a múltiples líneas) -->
                                <div class="stat-horario d-flex align-items-start gap-2">
                                    <i class="fa-solid fa-clock text-danger small flex-shrink-0 mt-1"></i>
                                    <span class="text-dark fw-medium small lh-sm">
                                        {{ $materia->horario }}
                                    </span>
                                </div>
                            </div>

                            <!-- Acciones -->
                            <div class="d-grid gap-2 d-flex">
                                <a href="{{ route('docente.asistencia', $materia->id) }}" class="btn btn-asistencia py-2 rounded-3 flex-fill d-flex align-items-center justify-content-center gap-2">
                                    <i class="fa-solid fa-clipboard-check"></i>
                                    <span>Asistencia</span>
                                </a>
                                <a href="{{ route('docente.notas', $materia->id) }}" class="btn btn-notas py-2 rounded-3 flex-fill d-flex align-items-center justify-content-center gap-2">
                                    <i class="fa-solid fa-file-pen"></i>
                                    <span>Notas</span>
                                </a>
                            </div>

                        </div>
                    </div>
                </div>
                @empty
                <!-- Estado vacío -->
                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body p-5 text-center">
                            <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3 bg-danger-soft"
                                style="width: 80px; height: 80px;">
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
</div>
@endsection