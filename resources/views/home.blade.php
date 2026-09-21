@extends('layouts.app')

@section('title', 'Inicio')

@section('content')
<div class="container-fluid p-4">

<div class="container-fluid px-3 px-md-4 py-4">

    <div class="mb-4 card shadow-sm rounded-4">
        <div class="card-body">
            <h4 class="fw-semibold mb-1"> Bienvenido, {{ $usuario->name }} </h4>
            <p class="text-muted mb-0 px-1"> Este es tu espacio de trabajo en NEXUM. </p>
        </div>
    </div>


    @if($usuario->hasRole('SUPER_ADMIN'))

        <div class="row g-4">
            <div class="col-12 col-xl-8">

                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-4">

                            <div class="me-3">
                                <div class="bg-danger-subtle rounded-circle
                                            d-flex align-items-center justify-content-center"
                                     style="width: 58px; height: 58px;">

                                    <i class="fa-solid fa-user-shield fs-4 text-danger"></i>
                                </div>
                            </div>

                            <div>
                                <h5 class="mb-1 fw-semibold">
                                    Panel del Super Administrador
                                </h5>
                                <span class="badge bg-success-subtle text-success border border-success">
                                    <i class="bi bi-patch-check-fill me-1"></i>
                                    Super Usuario
                                </span>
                            </div>
                        </div>


                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="border rounded-3 p-3 h-100">
                                    <small class="text-muted d-block mb-1">
                                        Nombre
                                    </small>
                                    <span class="fw-medium">
                                        {{ $usuario->name }}
                                    </span>
                                </div>
                            </div>


                            <div class="col-md-6">
                                <div class="border rounded-3 p-3 h-100">
                                    <small class="text-muted d-block mb-1">
                                        Correo electrónico
                                    </small>
                                    <span class="fw-medium">
                                        {{ $usuario->email }}
                                    </span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="border rounded-3 p-3 h-100">
                                    <small class="text-muted d-block mb-1">
                                        Documento de identidad
                                    </small>
                                    <span class="fw-medium">
                                        {{ $usuario->documento_identidad ?? 'No registrado' }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded-3 p-3 h-100">
                                    <small class="text-muted d-block mb-1">
                                        Celular
                                    </small>
                                    <span class="fw-medium">
                                        {{ $usuario->celular ?? 'No registrado' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            {{-- Acciones rápidas --}}
            <div class="col-12 col-xl-4">

                <div class="card border-0 shadow-sm rounded-4 h-100">

                    <div class="card-body p-4">

                        <h5 class="fw-semibold mb-1">
                            Acciones rápidas
                        </h5>

                        <p class="text-muted small mb-4">
                            Administración del sistema
                        </p>


                        <div class="d-grid gap-3">

                            <a href="{{ route('superadmin.createuser') }}"
                               class="btn btn-outline-dark text-start p-3 rounded-3 portal-hover">
                                <i class="fa-solid fa-user-plus me-2"></i>
                                Crear usuario
                            </a>


                            <a href="{{ route('superadmin.panel-administrativo') }}"
                               class="btn btn-outline-dark text-start p-3 rounded-3 portal-hover">
                                <i class="fa-solid fa-users-gear me-2"></i>
                                Administrar usuarios
                            </a>


                            <a href="{{ route('superadmin.create-rol') }}"
                               class="btn btn-outline-dark text-start p-3 rounded-3 portal-hover">
                                <i class="fa-solid fa-user-tag me-2"></i>
                                Administrar roles
                            </a>

                        </div>

                    </div>

                </div>

            </div>

        </div>


    {{-- ============================================================
        ESTUDIANTE
    ============================================================= --}}
    @elseif($usuario->hasRole('ESTUDIANTE'))

        <div class="row g-4">

            {{-- Información académica --}}
            <div class="col-12 col-xl-8">

                <div class="card border-0 shadow-sm rounded-4 h-100 portal-hover">

                    <div class="card-body p-4">

                        <div class="d-flex align-items-center mb-4">

                            <div class="me-3">

                                <div class="bg-danger-subtle rounded-circle
                                            d-flex align-items-center justify-content-center"
                                     style="width: 58px; height: 58px;">

                                    <i class="fa-solid fa-graduation-cap fs-4 text-danger"></i>

                                </div>

                            </div>

                            <div>

                                <h5 class="mb-1 fw-semibold">
                                    Bienvenido a NEXUM
                                </h5>

                                <span class="badge bg-success-subtle text-success border border-success">
                                    <i class="bi bi-mortarboard-fill me-1"></i>
                                    Estudiante
                                </span>

                            </div>

                        </div>


                        @if($usuario->alumno)

                            <div class="row g-3">

                                {{-- Código estudiante --}}
                                <div class="col-md-6">

                                    <div class="border rounded-3 p-3 h-100">

                                        <small class="text-muted d-block mb-1">
                                            Código de estudiante
                                        </small>

                                        <span class="fw-semibold">
                                            {{ $usuario->alumno->codigo_estudiante }}
                                        </span>

                                    </div>

                                </div>


                                {{-- Correo institucional --}}
                                <div class="col-md-6">

                                    <div class="border rounded-3 p-3 h-100">

                                        <small class="text-muted d-block mb-1">
                                            Correo institucional
                                        </small>

                                        <span class="fw-medium">
                                            {{ $usuario->alumno->correo_institucional }}
                                        </span>

                                    </div>

                                </div>


                                {{-- Carrera --}}
                                <div class="col-12">

                                    <div class="border rounded-3 p-3">

                                        <small class="text-muted d-block mb-1">
                                            Carrera
                                        </small>

                                        <span class="fw-semibold fs-5">
                                            {{ $usuario->alumno->carrera->nombre ?? 'No registrada' }}
                                        </span>

                                    </div>

                                </div>


                                {{-- Tipo de arancel --}}
                                <div class="col-md-6">

                                    <div class="border rounded-3 p-3 h-100">

                                        <small class="text-muted d-block mb-1">
                                            Tipo de arancel
                                        </small>

                                        <span class="fw-medium">
                                            {{ $usuario->alumno->tipo_arancel }}
                                        </span>

                                    </div>

                                </div>


                                {{-- Estado --}}
                                <div class="col-md-6">

                                    <div class="border rounded-3 p-3 h-100">

                                        <small class="text-muted d-block mb-1">
                                            Estado académico
                                        </small>

                                        @if($usuario->alumno->estado_carrera === 'ACTIVO')

                                            <span class="badge bg-success-subtle text-success border border-success">
                                                <i class="bi bi-check-circle-fill me-1"></i>
                                                Activo
                                            </span>

                                        @else

                                            <span class="badge bg-secondary-subtle text-secondary border">
                                                {{ $usuario->alumno->estado_carrera }}
                                            </span>

                                        @endif

                                    </div>

                                </div>

                            </div>

                        @else

                            <div class="alert alert-warning border-0 rounded-3">

                                <i class="bi bi-exclamation-triangle me-2"></i>

                                No se encontró información académica asociada a este usuario.

                            </div>

                        @endif

                    </div>

                </div>

            </div>


            {{-- Rutas del estudiante --}}
            <div class="col-12 col-xl-4">

                <div class="card border-0 shadow-sm rounded-4 h-100">

                    <div class="card-body p-4">

                        <h5 class="fw-semibold mb-1">
                            Mi portal
                        </h5>

                        <p class="text-muted small mb-4">
                            Accede rápidamente a tus servicios académicos.
                        </p>


                        <div class="d-grid gap-3">

                            <a href="#"
                               class="btn btn-outline-dark text-start p-3 rounded-3 portal-hover">

                                <i class="fa-solid fa-book-open me-2"></i>

                                Pensum

                            </a>


                            <a href="#"
                               class="btn btn-outline-dark text-start p-3 rounded-3 portal-hover">

                                <i class="fa-solid fa-graduation-cap me-2"></i>

                                Record académico

                            </a>


                            <a href="#"
                               class="btn btn-outline-dark text-start p-3 rounded-3 portal-hover">

                                <i class="fa-solid fa-pen-to-square me-2"></i>

                                Inscripción

                            </a>


                            <a href="#"
                               class="btn btn-outline-dark text-start p-3 rounded-3 portal-hover">

                                <i class="fa-solid fa-file-invoice-dollar me-2"></i>

                                Record financiero

                            </a>

                        </div>

                    </div>

                </div>

            </div>


            {{-- Accesos inferiores --}}
            <div class="col-12">

                <div class="row g-4">

                    <div class="col-md-4">

                        <a href="#"
                           class="text-decoration-none text-dark">

                            <div class="card border-0 shadow-sm rounded-4 portal-hover h-100">

                                <div class="card-body p-4">

                                    <i class="fa-solid fa-link fs-3 text-muted mb-3"></i>

                                    <h6 class="fw-semibold">
                                        Enlaces
                                    </h6>

                                    <p class="text-muted small mb-0">
                                        Accede a tus cursos virtuales.
                                    </p>

                                </div>

                            </div>

                        </a>

                    </div>


                    <div class="col-md-4">

                        <a href="#"
                           class="text-decoration-none text-dark">

                            <div class="card border-0 shadow-sm rounded-4 portal-hover h-100">

                                <div class="card-body p-4">

                                    <i class="fa-solid fa-clipboard-check fs-3 text-muted mb-3"></i>

                                    <h6 class="fw-semibold">
                                        Evaluación del desempeño
                                    </h6>

                                    <p class="text-muted small mb-0">
                                        Consulta y gestiona tus evaluaciones docentes.
                                    </p>

                                </div>

                            </div>

                        </a>

                    </div>


                    <div class="col-md-4">

                        <a href="#"
                           class="text-decoration-none text-dark">

                            <div class="card border-0 shadow-sm rounded-4 portal-hover h-100">

                                <div class="card-body p-4">

                                    <i class="fa-solid fa-folder-open fs-3 text-muted mb-3"></i>

                                    <h6 class="fw-semibold">
                                        Recursos bibliográficos
                                    </h6>

                                    <p class="text-muted small mb-0">
                                        Consulta los recursos disponibles.
                                    </p>

                                </div>

                            </div>

                        </a>

                    </div>

                </div>

            </div>

        </div>

    @endif

</div>

@endsection