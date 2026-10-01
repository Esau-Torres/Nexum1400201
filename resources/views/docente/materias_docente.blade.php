@extends('layouts.app')

@section('title', 'Mis Materias - Docente | NEXUM')

@section('content')

@php
// ============================================
// DATOS QUEMADOS (temporal)
// En el futuro estos datos vendrán del controlador
// desde la base de datos según el docente autenticado.
// ============================================
$materias = $materias ?? [
[
'id' => 1,
'nombre' => 'Programación I',
'grupo' => 'A',
'descripcion' => 'Fundamentos de programación, algoritmos y estructuras de datos básicas aplicadas a la resolución de problemas computacionales.',
'estudiantes' => 28,
'aula' => 'Aula 12 · Edificio Central',
'horario' => 'Lun - Mié · 07:00 - 08:30',
],
[
'id' => 2,
'nombre' => 'Base de Datos',
'grupo' => 'B',
'descripcion' => 'Diseño, implementación y administración de bases de datos relacionales utilizando SQL y modelado entidad-relación.',
'estudiantes' => 32,
'aula' => 'Aula 08 · Edificio TIC',
'horario' => 'Mar - Jue · 09:00 - 10:30',
],
[
'id' => 3,
'nombre' => 'Desarrollo Web',
'grupo' => 'A',
'descripcion' => 'Creación de aplicaciones web modernas con tecnologías frontend y backend, incluyendo HTML5, CSS3, JavaScript y frameworks actuales.',
'estudiantes' => 25,
'aula' => 'Aula 15 · Edificio TIC',
'horario' => 'Mié - Vie · 11:00 - 12:30',
],
];
@endphp

<div class="container-fluid p-4 p-lg-5">
    <div class="mx-auto" style="max-width: 1200px;">

        <!-- ============================================ -->
        <!-- ENCABEZADO                                   -->
        <!-- ============================================ -->
        <div class="text-left mb-5">
            <h1 class="display-5 fw-bold mb-2" style="letter-spacing: -0.5px;">Tus Materias</h1>
            <p class="text-muted fs-6 mb-0">Gestiona tus materias asignadas del ciclo actual</p>
        </div>

        <!-- ============================================ -->
        <!-- GRID DE MATERIAS (DINÁMICO)                  -->
        <!-- ============================================ -->
        <div class="row g-4">

            @forelse($materias as $materia)
            <div class="col-12 col-md-6">
                <div class="card border-0 shadow-sm rounded-4 materia-large-card h-100">
                    <div class="card-body p-4 p-lg-5">
                        <!-- Título + Grupo -->
                        <div class="mb-3">
                            <h3 class="fw-bold mb-2">{{ $materia['nombre'] }}</h3>
                            <span class="badge bg-danger rounded-pill">Grupo {{ $materia['grupo'] }}</span>
                        </div>

                        <!-- Descripción -->
                        <p class="materia-description">
                            {{ $materia['descripcion'] }}
                        </p>

                        <!-- Stats -->
                        <div class="materia-stats mb-4">
                            <div class="d-flex align-items-center mb-2">
                                <i class="fa-solid fa-users me-2 text-danger"></i>
                                <span class="small"><strong>{{ $materia['estudiantes'] }}</strong> estudiantes inscritos</span>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <i class="fa-solid fa-location-dot me-2 text-danger"></i>
                                <span class="small">{{ $materia['aula'] }}</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <i class="fa-solid fa-clock me-2 text-danger"></i>
                                <span class="small">{{ $materia['horario'] }}</span>
                            </div>
                        </div>

                        <!-- Acciones (botones grandes) -->
                        <div class="d-flex flex-column flex-sm-row gap-2">
                            <a href="#" class="btn btn-outline-danger rounded-3 flex-fill materia-btn-large">
                                <i class="fa-solid fa-clipboard-check me-2"></i>Asistencia
                            </a>
                            <a href="{{ route('docente.notas', $materia['id']) }}" class="btn btn-outline-danger rounded-3 flex-fill materia-btn-large">
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