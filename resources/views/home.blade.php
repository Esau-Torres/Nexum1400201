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

                {{-- 1. LÓGICA DE VALIDACIÓN DE SOLVENCIA ESTRICTA --}}
                @php
                    $bloquearAcceso = false;
                    $mensajeBloqueo = '';
                    $tipoAutopay = '';

                    $cicloActivo = \Illuminate\Support\Facades\DB::table('ciclo_lectivo')->where('estado', 'ACTIVO')->first();

                    if ($cicloActivo) {
                        $hoy = \Carbon\Carbon::now();

                        // Buscamos TODOS los cargos pendientes
                        $cargosPendientes = \Illuminate\Support\Facades\DB::table('cargo_estudiante')
                            ->join('concepto_pago', 'cargo_estudiante.id_concepto', '=', 'concepto_pago.concepto_pago_id')
                            ->where('cargo_estudiante.id_alumno', $usuario->alumno->alumno_id)
                            ->where('cargo_estudiante.estado_cargo', 'PENDIENTE')
                            ->select('cargo_estudiante.*', 'concepto_pago.nombre as nombre_arancel')
                            ->orderBy('cargo_estudiante.fecha_vencimiento', 'asc')
                            ->get();

                        if ($cargosPendientes->count() > 0) {
                            $esCiclo01 = str_contains($cicloActivo->nombre_ciclo, '01') || $cicloActivo->numero_ciclo == '01';
                            $mesPrimeraCuota = $esCiclo01 ? 'Enero' : 'Junio';

                            foreach($cargosPendientes as $cargo) {
                                $nombreArancel = strtoupper($cargo->nombre_arancel);
                                $fechaVencimiento = \Carbon\Carbon::parse($cargo->fecha_vencimiento);

                                // REGLA A: ¿Debe algo de un ciclo pasado? (Prioridad Máxima)
                                if ($cargo->id_ciclo_lectivo != $cicloActivo->ciclo_lectivo_id) {
                                    $bloquearAcceso = true;
                                    $mensajeBloqueo = 'Tienes saldos pendientes de ciclos anteriores. Para continuar, debes estar 100% solvente.';
                                    $tipoAutopay = 'all';
                                    break;
                                }

                                // REGLA B: ¿Debe Matrícula del ciclo actual?
                                if (str_contains($nombreArancel, 'MATRÍCULA') || str_contains($nombreArancel, 'MATRICULA') || str_contains($nombreArancel, 'INSCRIPCI')) {
                                    $bloquearAcceso = true;
                                    $mensajeBloqueo = 'Para habilitar tu portal estudiantil, es indispensable cancelar el arancel de Matrícula.';
                                    $tipoAutopay = 'inicio';
                                    break;
                                }

                                // REGLA C: ¿Debe la primera cuota obligatoria del ciclo?
                                if ((str_contains($nombreArancel, 'CUOTA') || str_contains($nombreArancel, 'MENSUAL')) && strcasecmp($cargo->mes_arancel, $mesPrimeraCuota) === 0) {
                                    $bloquearAcceso = true;
                                    $mensajeBloqueo = 'Es necesario realizar el pago de tu primera mensualidad para activar tu acceso al ciclo actual.';
                                    $tipoAutopay = 'inicio';
                                    break;
                                }

                                // REGLA D: ¿Tiene cualquier otra cuota vencida (con mora)?
                                if ($hoy->gt($fechaVencimiento)) {
                                    $bloquearAcceso = true;
                                    $mensajeBloqueo = 'Tienes aranceles vencidos. Para mantener el acceso a tu portal debes ponerte al día con tus pagos.';
                                    $tipoAutopay = 'vencidos';
                                    break;
                                }
                            }
                        }
                    }
                @endphp

                {{-- ========================================================================= --}}
                {{-- 2. ESCENARIO DEUDOR: MOSTRAMOS EL MODAL Y OCULTAMOS EL DASHBOARD COMPLETO --}}
                {{-- ========================================================================= --}}
                @if($bloquearAcceso)
                    <!-- PANTALLA DE BLOQUEO DE FONDO (Para evitar que usen "Inspeccionar elemento") -->
                    <div class="d-flex justify-content-center align-items-center" style="min-height: 60vh;">
                        <div class="text-center text-muted">
                            <i class="fa-solid fa-lock" style="font-size: 4rem; color: #dee2e6;"></i>
                            <h3 class="mt-3 text-secondary" style="opacity: 0.5;">Portal Bloqueado</h3>
                        </div>
                    </div>

                    <!-- MODAL ESTRICTO DE SOLVENCIA -->
                    <div class="modal fade" id="modalSolvenciaEstricta" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow-lg rounded-4">
                                <div class="modal-header text-white" style="background-color: #dc3545; border-top-left-radius: 1rem; border-top-right-radius: 1rem;">
                                    <h5 class="modal-title fw-bold">
                                        <i class="fa-solid fa-triangle-exclamation me-2"></i>Aviso de Solvencia
                                    </h5>
                                </div>
                                <div class="modal-body p-4 text-center bg-light">
                                    <div class="mb-3">
                                        <i class="fa-solid fa-wallet text-danger" style="font-size: 3rem;"></i>
                                    </div>
                                    <h4 class="fw-bold text-dark mb-3">Acceso Restringido</h4>

                                    <!-- El mensaje cambia dinámicamente según la deuda del alumno -->
                                    <p class="text-muted mb-4" style="font-size: 0.95rem;">
                                        {{ $mensajeBloqueo }}
                                    </p>

                                    <div class="d-flex flex-column flex-sm-row justify-content-center gap-2 mt-2">
                                        <form action="{{ route('logout') }}" method="POST" class="m-0 w-100">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-secondary fw-bold w-100 px-4 py-2" style="border-radius: 8px;">
                                                <i class="fa-solid fa-right-from-bracket me-2"></i> Cerrar Sesión
                                            </button>
                                        </form>

                                        <!-- Lo enviamos directamente a la pasarela de pago -->
                                        <a href="{{ url('/adfinanciero/adfinanciero/cargosestudiante?autopay=') }}{{ $tipoAutopay }}" class="btn text-white fw-bold px-4 py-2 w-100" style="background-color: #dc3545; border-radius: 8px;">
                                            <i class="fa-solid fa-credit-card me-2"></i> Ir a Pagar
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            new bootstrap.Modal(document.getElementById('modalSolvenciaEstricta')).show();
                        });
                    </script>


                {{-- ========================================================================= --}}
                {{-- 3. ESCENARIO SOLVENTE: MOSTRAMOS EL PERFIL NORMAL                         --}}
                {{-- ========================================================================= --}}
                @else

                    {{-- Validamos que tenga cargos pendientes (futuros/no vencidos) para mostrarle el recordatorio --}}
                    @if(isset($cargosPendientes) && $cargosPendientes->count() > 0)
                        <!-- MODAL DE RECORDATORIO AMIGABLE (TIENE DEUDAS PERO NO VENCIDAS) -->
                        <div class="modal fade" id="modalRecordatorioSolvente" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow-lg rounded-4">
                                    <div class="modal-header text-white" style="background-color: #dc3545; border-top-left-radius: 1rem; border-top-right-radius: 1rem;">
                                        <h5 class="modal-title fw-bold">
                                            <i class="fa-solid fa-bell me-2"></i>Recordatorio de Pago
                                        </h5>
                                        <!-- Botón X para cerrar -->
                                        <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body p-4 text-center bg-light">
                                        <div class="mb-3">
                                            <div class="bg-danger bg-opacity-10 rounded-circle d-inline-flex justify-content-center align-items-center" style="width: 80px; height: 80px;">
                                                <i class="fa-solid fa-calendar-days text-danger" style="font-size: 2.5rem;"></i>
                                            </div>
                                        </div>
                                        <h4 class="fw-bold text-dark mb-3">¡Hola, {{ explode(' ', $usuario->name)[0] }}!</h4>
                                        <p class="text-muted mb-4" style="font-size: 0.95rem;">
                                            Te recordamos realizar tu pago <strong>antes del 10 de cada mes</strong> para mantener tu estado de cuenta al día y evitar recargos adicionales por mora. ¡Gracias por tu puntualidad!
                                        </p>

                                        <div class="d-flex justify-content-center mt-2">
                                            <button type="button" class="btn text-white fw-bold px-4 py-2 w-100" style="background-color: #dc3545; border-radius: 8px;" data-bs-dismiss="modal">
                                                <i class="fa-solid fa-thumbs-up me-2"></i> Entendido
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Script para disparar el modal al cargar la página -->
                        <script>
                            document.addEventListener('DOMContentLoaded', function () {
                                new bootstrap.Modal(document.getElementById('modalRecordatorioSolvente')).show();
                            });
                        </script>
                    @endif

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
                            <a href="{{ route('admin-academico.alumnos.modify-student') }}" class="btn btn-outline-dark text-start p-3 rounded-3 portal-hover">
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
