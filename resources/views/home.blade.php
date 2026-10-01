@extends('layouts.app')

@section('title', 'Inicio')

@section('content')
<div class="container-fluid px-3 px-md-4 py-4">
    <div class="mb-4 card shadow-sm rounded-4">
        <div class="card-body">
            <h4 class="fw-semibold mb-1">BIENVENIDOS AL SISTEMA ACADEMICO UMA</h4>
            <p class="text-muted mb-0 px-1">
                Este es tu espacio para realizar tus primeras tareas.
            </p>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12 col-xl-8">

            {{-- Datos generales (común) --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-4">
                        <div class="me-3">
                            <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-sm"
                                 style="width: 58px; height: 58px; background-color: var(--color-accent); font-size: 1.5rem;">
                                {{ strtoupper(substr($usuario->name, 0, 2)) }}
                            </div>
                        </div>
                        <div>
                            <h5 class="mb-1 fw-semibold">{{ $usuario->name }}</h5>
                            <div class="d-flex flex-wrap gap-1">
                                @forelse($usuario->roles as $rol)
                                    <span class="badge bg-danger-subtle text-danger border border-danger">
                                        <i class="bi bi-shield-fill me-1"></i>{{ $rol->nombre }}
                                    </span>
                                @empty
                                    <span class="badge bg-secondary-subtle text-secondary border">
                                        Sin roles asignados
                                    </span>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="border rounded-3 p-3 h-100">
                                <small class="text-muted d-block mb-1">Nombre completo</small>
                                <span class="fw-medium">{{ $usuario->name }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded-3 p-3 h-100">
                                <small class="text-muted d-block mb-1">Correo electrónico</small>
                                <span class="fw-medium">{{ $usuario->email }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded-3 p-3 h-100">
                                <small class="text-muted d-block mb-1">Documento de identidad</small>
                                <span class="fw-medium">{{ $usuario->documento_identidad ?? 'No registrado' }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded-3 p-3 h-100">
                                <small class="text-muted d-block mb-1">Celular</small>
                                <span class="fw-medium">{{ $usuario->celular ?? 'No registrado' }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded-3 p-3 h-100">
                                <small class="text-muted d-block mb-1">Sede UMA</small>
                                <span class="fw-medium">{{ $usuario->regionalactivo->sede ?? 'No asignada' }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded-3 p-3 h-100">
                                <small class="text-muted d-block mb-1">Estado de cuenta</small>
                                @if($usuario->estado === 1)
                                    <span class="badge bg-success-subtle text-success border border-success">
                                        <i class="bi bi-check-circle-fill me-1"></i>Activo
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger">
                                        <i class="bi bi-x-circle-fill me-1"></i>Inactivo
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ESTUDIANTE --}}
            @if($usuario->hasRole('ESTUDIANTE') && $usuario->alumno)
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-4">
                            <div class="me-3">
                                <div class="bg-danger-subtle rounded-circle d-flex align-items-center justify-content-center"
                                     style="width: 58px; height: 58px;">
                                    <i class="fa-solid fa-graduation-cap fs-4 text-danger"></i>
                                </div>
                            </div>
                            <div>
                                <h5 class="mb-1 fw-semibold">Información Académica</h5>
                                <span class="badge bg-success-subtle text-success border border-success">
                                    <i class="bi bi-mortarboard-fill me-1"></i>Estudiante
                                </span>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="border rounded-3 p-3 h-100">
                                    <small class="text-muted d-block mb-1">Código de estudiante</small>
                                    <span class="fw-semibold">{{ $usuario->alumno->codigo_estudiante }}</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded-3 p-3 h-100">
                                    <small class="text-muted d-block mb-1">Correo institucional</small>
                                    <span class="fw-medium">{{ $usuario->alumno->correo_institucional }}</span>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="border rounded-3 p-3">
                                    <small class="text-muted d-block mb-1">Carrera</small>
                                    <span class="fw-semibold fs-5">
                                        {{ $usuario->alumno->carrera->nombre ?? 'No registrada' }}
                                    </span>
                                    @if($usuario->alumno->carrera->facultad)
                                        <small class="text-muted d-block mt-1">
                                            Facultad: {{ $usuario->alumno->carrera->facultad->nombre }}
                                        </small>
                                    @endif
                                </div>
                            </div>

                            @php $beneficioActivo = $usuario->alumno->beneficioCicloActual; @endphp

                            @if($beneficioActivo)
                                <div class="col-md-6">
                                    <div class="border rounded-3 p-3 h-100">
                                        <small class="text-muted d-block mb-1">Beneficio activo</small>
                                        <span class="badge bg-danger-subtle text-danger border border-danger">
                                            <i class="bi bi-cash-coin me-1"></i>
                                            {{ $beneficioActivo->tipo_beneficio->label() }}
                                        </span>
                                        <small class="text-muted d-block mt-2">
                                            Ciclo: {{ $beneficioActivo->cicloLectivo->nombre_ciclo ?? '—' }}
                                        </small>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="border rounded-3 p-3 h-100">
                                        <small class="text-muted d-block mb-1">Convenio / Beca</small>
                                        <span class="fw-medium">{{ $beneficioActivo->nombre_convenio }}</span>
                                        <div class="mt-2 d-flex gap-2 flex-wrap">
                                            <span class="badge bg-secondary-subtle text-secondary border small">
                                                Estudiante: {{ $beneficioActivo->porcentaje_estudiante }}%
                                            </span>
                                            <span class="badge bg-secondary-subtle text-secondary border small">
                                                UMA: {{ $beneficioActivo->porcentaje_universidad }}%
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="col-md-6">
                                <div class="border rounded-3 p-3 h-100">
                                    <small class="text-muted d-block mb-1">Estado académico</small>
                                    @if($usuario->alumno->estado_carrera === 'ACTIVO')
                                        <span class="badge bg-success-subtle text-success border border-success">
                                            <i class="bi bi-check-circle-fill me-1"></i>Activo
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border">
                                            {{ $usuario->alumno->estado_carrera }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="border rounded-3 p-3 h-100">
                                    <small class="text-muted d-block mb-1">Estado de documentos</small>
                                    @if($usuario->alumno->documentos)
                                        @if($usuario->alumno->documentos->estado_documentos === 'APROBADO')
                                            <span class="badge bg-success-subtle text-success border border-success">
                                                <i class="bi bi-check-circle-fill me-1"></i>Aprobado
                                            </span>
                                        @elseif($usuario->alumno->documentos->estado_documentos === 'PENDIENTE')
                                            <span class="badge bg-warning-subtle text-warning border border-warning">
                                                <i class="bi bi-clock-fill me-1"></i>Pendiente
                                            </span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger border border-danger">
                                                <i class="bi bi-x-circle-fill me-1"></i>
                                                {{ $usuario->alumno->documentos->estado_documentos }}
                                            </span>
                                        @endif
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border">
                                            Sin documentos cargados
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- DOCENTE --}}
            @if($usuario->hasRole('DOCENTE') && $usuario->docente)
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-4">
                            <div class="me-3">
                                <div class="bg-danger-subtle rounded-circle d-flex align-items-center justify-content-center"
                                     style="width: 58px; height: 58px;">
                                    <i class="fa-solid fa-chalkboard-user fs-4 text-danger"></i>
                                </div>
                            </div>
                            <div>
                                <h5 class="mb-1 fw-semibold">Información Docente</h5>
                                <span class="badge bg-success-subtle text-success border border-success">
                                    <i class="bi bi-person-badge-fill me-1"></i>Docente
                                </span>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="border rounded-3 p-3 h-100">
                                    <small class="text-muted d-block mb-1">Código de empleado</small>
                                    <span class="fw-semibold font-monospace">
                                        {{ $usuario->docente->codigo_empleado }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded-3 p-3 h-100">
                                    <small class="text-muted d-block mb-1">Correo institucional</small>
                                    <span class="fw-medium">{{ $usuario->docente->correo_institucional }}</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded-3 p-3 h-100">
                                    <small class="text-muted d-block mb-1">Estado laboral</small>
                                    @if(strtoupper($usuario->docente->estado) === 'ACTIVO')
                                        <span class="badge bg-success-subtle text-success border border-success">
                                            <i class="bi bi-check-circle-fill me-1"></i>Activo
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border">
                                            {{ $usuario->docente->estado }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- COLUMNA LATERAL --}}
        <div class="col-12 col-xl-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-1">Acciones rápidas</h5>
                    <p class="text-muted small mb-4">
                        Accede a los módulos disponibles según tus roles.
                    </p>

                    <div class="d-grid gap-3">
                        @if($usuario->hasRole('SUPER_ADMIN'))
                            <a href="{{ route('superadmin.createuser') }}" class="btn btn-outline-dark text-start p-3 rounded-3 portal-hover">
                                <i class="fa-solid fa-user-plus me-2"></i> Crear usuario
                            </a>
                            <a href="{{ route('superadmin.panel-administrativo') }}" class="btn btn-outline-dark text-start p-3 rounded-3 portal-hover">
                                <i class="fa-solid fa-users-gear me-2"></i> Administrar usuarios
                            </a>
                            <a href="{{ route('superadmin.roles.manageroles') }}" class="btn btn-outline-dark text-start p-3 rounded-3 portal-hover">
                                <i class="fa-solid fa-user-tag me-2"></i> Administrar roles
                            </a>
                            <a href="{{ route('superadmin.manage-ruler') }}" class="btn btn-outline-dark text-start p-3 rounded-3 portal-hover">
                                <i class="fa-solid fa-ruler me-2"></i> Gestionar reglas del sistema
                            </a>
                        @endif

                        @if($usuario->hasRole('ESTUDIANTE'))
                            <a href="#" class="btn btn-outline-dark text-start p-3 rounded-3 portal-hover">
                                <i class="fa-solid fa-book-open me-2"></i> Calificaciones
                            </a>
                            <a href="#" class="btn btn-outline-dark text-start p-3 rounded-3 portal-hover">
                                <i class="fa-solid fa-graduation-cap me-2"></i> Record académico
                            </a>
                            <a href="#" class="btn btn-outline-dark text-start p-3 rounded-3 portal-hover">
                                <i class="fa-solid fa-pen-to-square me-2"></i> Realizar pagos
                            </a>
                            <a href="#" class="btn btn-outline-dark text-start p-3 rounded-3 portal-hover">
                                <i class="fa-solid fa-file-invoice-dollar me-2"></i> Record financiero
                            </a>
                        @endif

                        @if($usuario->hasRole('DOCENTE'))
                            <a href="#" class="btn btn-outline-dark text-start p-3 rounded-3 portal-hover">
                                <i class="fa-solid fa-chalkboard me-2"></i> Mis asignaturas
                            </a>
                            <a href="#" class="btn btn-outline-dark text-start p-3 rounded-3 portal-hover">
                                <i class="fa-solid fa-clipboard-list me-2"></i> Registro de notas
                            </a>
                        @endif

                        @if($usuario->hasRole('ADMIN_ACADEMICO'))
                            <a href="{{ route('admin-academico.create-student') }}" class="btn btn-outline-dark text-start p-3 rounded-3 portal-hover">
                                <i class="fa-solid fa-user-plus me-2"></i> Crear usuario
                            </a>
                            <a href="{{ route('admin-academico.admin.academico.solicitudes.approve-student') }}" class="btn btn-outline-dark text-start p-3 rounded-3 portal-hover">
                                <i class="fa-solid fa-check me-2"></i> Solicitud de aprobación de cuentas
                            </a>
                            <a href="{{ route('admin-academico.modify-student') }}" class="btn btn-outline-dark text-start p-3 rounded-3 portal-hover">
                                <i class="fa-solid fa-users-gear me-2"></i> Administrar usuarios
                            </a>
                            <a href="{{ route('admin-academico.admin-academico.beneficios.modify-benefit-student') }}" class="btn btn-outline-dark text-start p-3 rounded-3 portal-hover">
                                <i class="fa-solid fa-user-tag me-2"></i> Gestionar Becas
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection