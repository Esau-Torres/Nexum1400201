@extends('layouts.app')

@section('title', 'Inicio')

@section('content')

{{-- Lógica CSS Flat Design Exclusiva para esta vista --}}
<style>
    .flat-card {
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        box-shadow: none !important;
        overflow: hidden;
    }
    .flat-header {
        background-color: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
    }
    .flat-info-box {
        background-color: #f8fafc;
        border: 1px solid #f1f5f9;
        border-radius: 12px;
        padding: 1.25rem;
        height: 100%;
        transition: background-color 0.2s ease;
    }
    .flat-info-box:hover {
        background-color: #f1f5f9;
    }
    .flat-avatar {
        width: 72px;
        height: 72px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
        font-weight: 700;
        color: #ffffff;
    }
    .flat-badge {
        border: none !important;
        font-weight: 500;
        padding: 0.5em 1em;
        border-radius: 9999px;
    }
    .flat-btn {
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        color: #334155;
        border-radius: 12px;
        transition: all 0.2s ease;
        box-shadow: none !important;
    }
    .flat-btn:hover {
        background-color: #f8fafc;
        border-color: #cbd5e1;
        color: #0f172a;
        transform: translateY(-2px);
    }
    .text-slate-500 { color: #65648b; }
    .text-slate-800 { color: #1e293b; }
</style>

<div class="container-fluid px-3 px-md-4 py-4">

    {{-- Bienvenida plana --}}
    <div class="flat-card mb-4">
        <div class="card-body p-4 flat-header border-bottom-0">
            <h4 class="fw-bold mb-1 text-slate-800">BIENVENIDOS AL SISTEMA ACADÉMICO UMA</h4>
            <p class="text-slate-500 mb-0">Este es tu espacio para gestionar tu información y tareas.</p>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12 col-xl-8">

            {{-- ========================================================================= --}}
            {{-- 1. VISTA EXCLUSIVA ESTUDIANTE --}}
            {{-- ========================================================================= --}}
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

                                if ($cargo->id_ciclo_lectivo != $cicloActivo->ciclo_lectivo_id) {
                                    $bloquearAcceso = true;
                                    $mensajeBloqueo = 'Tienes saldos pendientes de ciclos anteriores. Para continuar, debes estar 100% solvente.';
                                    $tipoAutopay = 'all';
                                    break;
                                }

                                if (str_contains($nombreArancel, 'MATRÍCULA') || str_contains($nombreArancel, 'MATRICULA') || str_contains($nombreArancel, 'INSCRIPCI')) {
                                    $bloquearAcceso = true;
                                    $mensajeBloqueo = 'Para habilitar tu portal estudiantil, es indispensable cancelar el arancel de Matrícula.';
                                    $tipoAutopay = 'inicio';
                                    break;
                                }

                                if ((str_contains($nombreArancel, 'CUOTA') || str_contains($nombreArancel, 'MENSUAL')) && strcasecmp($cargo->mes_arancel, $mesPrimeraCuota) === 0) {
                                    $bloquearAcceso = true;
                                    $mensajeBloqueo = 'Es necesario realizar el pago de tu primera mensualidad para activar tu acceso al ciclo actual.';
                                    $tipoAutopay = 'inicio';
                                    break;
                                }

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

                @if($bloquearAcceso)
                    <!-- PANTALLA DE BLOQUEO DE FONDO -->
                    <div class="d-flex justify-content-center align-items-center" style="min-height: 60vh;">
                        <div class="text-center text-muted">
                            <i class="fa-solid fa-lock" style="font-size: 4rem; color: #e2e8f0;"></i>
                            <h3 class="mt-3 text-slate-500" style="opacity: 0.5;">Portal Bloqueado</h3>
                        </div>
                    </div>

                    <!-- MODAL ESTRICTO DE SOLVENCIA -->
                    <div class="modal fade" id="modalSolvenciaEstricta" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 rounded-4" style="box-shadow: 0 10px 25px rgba(0,0,0,0.1);">
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
                @else
                    @if(isset($cargosPendientes) && $cargosPendientes->count() > 0)
                        <!-- MODAL RECORDATORIO AMIGABLE -->
                        <div class="modal fade" id="modalRecordatorioSolvente" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 rounded-4" style="box-shadow: 0 10px 25px rgba(0,0,0,0.1);">
                                    <div class="modal-header text-white border-0" style="background-color: #dc3545; border-top-left-radius: 1rem; border-top-right-radius: 1rem;">
                                        <h5 class="modal-title fw-bold">
                                            <i class="fa-solid fa-bell me-2"></i>Recordatorio de Pago
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body p-4 text-center bg-white">
                                        <div class="mb-3">
                                            <div class="bg-danger bg-opacity-10 rounded-circle d-inline-flex justify-content-center align-items-center" style="width: 80px; height: 80px;">
                                                <i class="fa-solid fa-calendar-days text-danger" style="font-size: 2.5rem;"></i>
                                            </div>
                                        </div>
                                        <h4 class="fw-bold text-dark mb-3">¡Hola, {{ explode(' ', $usuario->name)[0] }}!</h4>
                                        <p class="text-muted mb-4" style="font-size: 0.95rem;">
                                            Te recordamos realizar tu pago <strong>antes del 10 de cada mes</strong> para mantener tu estado de cuenta al día.
                                        </p>
                                        <button type="button" class="btn text-white fw-bold px-4 py-2 w-100" style="background-color: #dc3545; border-radius: 8px;" data-bs-dismiss="modal">
                                            <i class="fa-solid fa-thumbs-up me-2"></i> Entendido
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <script>
                            document.addEventListener('DOMContentLoaded', function () {
                                new bootstrap.Modal(document.getElementById('modalRecordatorioSolvente')).show();
                            });
                        </script>
                    @endif

                    <!-- DASHBOARD ESTUDIANTE: DISEÑO FLAT EXCLUSIVO -->
                    <div class="flat-card mb-4">
                        {{-- Cabecera del Perfil Estudiantil --}}
                        <div class="flat-header p-4 p-md-5">
                            <div class="d-flex flex-column flex-md-row align-items-center align-items-md-start gap-4">
                                <div class="flat-avatar" style="background-color: var(--color-accent);">
                                    {{ strtoupper(substr($usuario->name, 0, 2)) }}
                                </div>
                                <div class="text-center text-md-start">
                                    <h3 class="fw-bold mb-1 text-slate-800">{{ $usuario->name }}</h3>
                                    <p class="text-slate-500 mb-3">{{ $usuario->alumno->correo_institucional ?? $usuario->email }}</p>

                                    <div class="d-flex flex-wrap justify-content-center justify-content-md-start gap-2">
                                        <span class="badge bg-danger text-white flat-badge">
                                            <i class="bi bi-mortarboard-fill me-1"></i> Estudiante
                                        </span>
                                        @if($usuario->estado === 1)
                                            <span class="badge bg-success text-white flat-badge">
                                                Cuenta Activa
                                            </span>
                                        @else
                                            <span class="badge bg-secondary text-white flat-badge">
                                                Cuenta Inactiva
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Cuadrícula de Información Clave --}}
                        <div class="p-4 p-md-5">
                            <h5 class="fw-bold text-slate-800 mb-4">Información Académica</h5>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="flat-info-box">
                                        <span class="d-block text-slate-500 small fw-bold text-uppercase mb-1">Facultad</span>
                                        <span class="d-block fs-6 text-slate-800 fw-medium">
                                            {{ $usuario->alumno->carrera->facultad->nombre ?? 'No registrada' }}
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="flat-info-box">
                                        <span class="d-block text-slate-500 small fw-bold text-uppercase mb-1">Carrera</span>
                                        <span class="d-block fs-6 text-slate-800 fw-medium">
                                            {{ $usuario->alumno->carrera->nombre ?? 'No registrada' }}
                                        </span>
                                    </div>
                                </div>

                                @php $beneficioActivo = $usuario->alumno->beneficioCicloActual; @endphp
                                <div class="col-md-4">
                                    <div class="flat-info-box">
                                        <span class="d-block text-slate-500 small fw-bold text-uppercase mb-1">Condición Beca</span>
                                        @if($beneficioActivo)
                                            <span class="d-flex align-items-center text-success fw-bold fs-6">
                                                <i class="bi bi-check-circle-fill me-2"></i> Becado
                                            </span>
                                            <span class="d-block text-slate-500 small mt-1">{{ $beneficioActivo->nombre_convenio }}</span>
                                        @else
                                            <span class="d-block text-slate-800 fw-medium fs-6">Estudiante Regular</span>
                                            <span class="d-block text-slate-500 small mt-1">Sin beneficios activos</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="flat-info-box">
                                        <span class="d-block text-slate-500 small fw-bold text-uppercase mb-1">Código Estudiante</span>
                                        <span class="d-block fs-6 text-slate-800 fw-bold font-monospace">
                                            {{ $usuario->alumno->codigo_estudiante }}
                                        </span>
                                        <span class="d-block text-slate-500 small mt-1">
                                            Sede: {{ $usuario->regionalactivo->sede ?? 'No asignada' }}
                                        </span>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="flat-info-box">
                                        <span class="d-block text-slate-500 small fw-bold text-uppercase mb-1">Estado Expediente</span>
                                        @if($usuario->alumno->documentos)
                                            @if($usuario->alumno->documentos->estado_documentos === 'APROBADO')
                                                <span class="d-block text-success fw-medium fs-6">Documentos Aprobados</span>
                                            @elseif($usuario->alumno->documentos->estado_documentos === 'PENDIENTE')
                                                <span class="d-block text-warning fw-medium fs-6">En Revisión</span>
                                            @else
                                                <span class="d-block text-danger fw-medium fs-6">{{ $usuario->alumno->documentos->estado_documentos }}</span>
                                            @endif
                                        @else
                                            <span class="d-block text-slate-800 fw-medium fs-6">Sin Cargar</span>
                                        @endif

                                        <span class="d-block text-slate-500 small mt-1">Carrera: {{ ucfirst(strtolower($usuario->alumno->estado_carrera)) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

            @else
                {{-- ========================================================================= --}}
                {{-- 2. VISTA GENÉRICA REDISEÑADA (ADMIN, MANTENIMIENTO, OTROS) --}}
                {{-- ========================================================================= --}}
                <div class="flat-card mb-4">
                    <div class="p-4 p-md-5">
                        <div class="d-flex flex-column flex-md-row align-items-center align-items-md-start gap-4 mb-4 pb-4 border-bottom">
                            <div class="flat-avatar shadow-sm" style="background-color: var(--color-accent); width: 90px; height: 90px; font-size: 2.2rem;">
                                {{ strtoupper(substr($usuario->name, 0, 2)) }}
                            </div>
                            <div class="text-center text-md-start w-100">
                                <h3 class="fw-bold mb-1 text-slate-800 fs-2">{{ $usuario->name }}</h3>
                                <p class="text-slate-500 mb-3 fs-5">{{ $usuario->email }}</p>

                                <div class="d-flex flex-wrap justify-content-center justify-content-md-start gap-2">
                                    @forelse($usuario->roles as $rol)
                                        @php
                                            $nombresRoles = [
                                                'SUPER_ADMIN' => 'Administrador de TI',
                                                'ADMIN_ACADEMICO' => 'Administrador Académico',
                                                'ADMIN_FINANCIERO' => 'Administrador Financiero',
                                                'CAJERO' => 'Cajero',
                                                'COORDINADOR_FACULTAD' => 'Coordinador de Facultad',
                                                'ESTUDIANTE' => 'Estudiante',
                                                'DOCENTE' => 'Docente'
                                            ];
                                            $rolAmigable = $nombresRoles[$rol->nombre] ?? $rol->nombre;
                                        @endphp
                                        <span class="badge bg-light text-dark border flat-badge px-3 py-2 fs-6">
                                            <i class="fa-solid fa-user-tag text-danger me-2"></i>{{ $rolAmigable }}
                                        </span>
                                    @empty
                                        <span class="badge bg-light text-secondary border flat-badge px-3 py-2 fs-6">
                                            Sin roles asignados
                                        </span>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="flat-info-box d-flex align-items-center gap-3">
                                    <div class="rounded-circle bg-white shadow-sm d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                        <i class="fa-solid fa-id-card text-danger fs-5"></i>
                                    </div>
                                    <div>
                                        <span class="d-block text-slate-500 small fw-bold text-uppercase mb-1">Documento de Identidad</span>
                                        <span class="d-block text-slate-800 fw-bold fs-6">{{ $usuario->documento_identidad ?? 'No registrado' }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="flat-info-box d-flex align-items-center gap-3">
                                    <div class="rounded-circle bg-white shadow-sm d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                        <i class="fa-solid fa-mobile-screen text-danger fs-5"></i>
                                    </div>
                                    <div>
                                        <span class="d-block text-slate-500 small fw-bold text-uppercase mb-1">Celular de Contacto</span>
                                        <span class="d-block text-slate-800 fw-bold fs-6">{{ $usuario->celular ?? 'No registrado' }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="flat-info-box d-flex align-items-center gap-3">
                                    <div class="rounded-circle bg-white shadow-sm d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                        <i class="fa-solid fa-building-user text-danger fs-5"></i>
                                    </div>
                                    <div>
                                        <span class="d-block text-slate-500 small fw-bold text-uppercase mb-1">Sede Operativa</span>
                                        <span class="d-block text-slate-800 fw-bold fs-6">{{ $usuario->regionalactivo->sede ?? 'No asignada' }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="flat-info-box d-flex align-items-center gap-3">
                                    <div class="rounded-circle bg-white shadow-sm d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                        @if($usuario->estado === 1)
                                            <i class="fa-solid fa-circle-check text-success fs-5"></i>
                                        @else
                                            <i class="fa-solid fa-circle-xmark text-secondary fs-5"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <span class="d-block text-slate-500 small fw-bold text-uppercase mb-1">Estado de Cuenta</span>
                                        @if($usuario->estado === 1)
                                            <span class="d-block text-success fw-bold fs-6">Activo en sistema</span>
                                        @else
                                            <span class="d-block text-secondary fw-bold fs-6">Inactivo en sistema</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- ========================================================================= --}}
            {{-- 3. DOCENTE (Se preserva la lógica HTML interna) --}}
            {{-- ========================================================================= --}}
            @if($usuario->hasRole('DOCENTE') && $usuario->docente)
                <div class="flat-card mb-4">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-4">
                            <div class="me-3">
                                <div class="bg-danger-subtle rounded-circle d-flex align-items-center justify-content-center"
                                     style="width: 58px; height: 58px;">
                                    <i class="fa-solid fa-chalkboard-user fs-4 text-danger"></i>
                                </div>
                            </div>
                            <div>
                                <h5 class="mb-1 fw-bold text-slate-800">Información Docente</h5>
                                <span class="badge bg-success-subtle text-success border border-success">
                                    <i class="bi bi-person-badge-fill me-1"></i>Docente
                                </span>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="flat-info-box p-3">
                                    <small class="text-slate-500 fw-bold text-uppercase d-block mb-1">Código de empleado</small>
                                    <span class="fw-bold text-slate-800 font-monospace">
                                        {{ $usuario->docente->codigo_empleado }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="flat-info-box p-3">
                                    <small class="text-slate-500 fw-bold text-uppercase d-block mb-1">Correo institucional</small>
                                    <span class="fw-medium text-slate-800">{{ $usuario->docente->correo_institucional }}</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="flat-info-box p-3">
                                    <small class="text-slate-500 fw-bold text-uppercase d-block mb-1">Estado laboral</small>
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

        {{-- ========================================================================= --}}
        {{-- COLUMNA LATERAL: ACCIONES RÁPIDAS (DISEÑO FLAT REFERENCIANDO RUTAS) --}}
        {{-- ========================================================================= --}}
        <div class="col-12 col-xl-4">
            <div class="flat-card h-100 p-4">
                <h5 class="fw-bold text-slate-800 mb-1">Acciones rápidas</h5>
                <p class="text-slate-500 small mb-4">
                    Accede a los módulos disponibles según tus roles.
                </p>

                <div class="d-grid gap-3" style="max-height: 700px; overflow-y: auto; padding-right: 5px;">

                    {{-- SUPER_ADMIN --}}
                    @if(auth()->user()->hasRole('SUPER_ADMIN'))
                        <a href="{{ route('superadmin.createuser') }}" class="btn flat-btn text-start p-3 d-flex align-items-center">
                            <div class="bg-light rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fa-solid fa-user text-danger fs-6"></i>
                            </div>
                            <div>
                                <span class="fw-bold d-block text-slate-800">Crear Usuario</span>
                                <span class="small text-slate-500">Ingreso de usuarios</span>
                            </div>
                        </a>
                        <a href="{{ route('superadmin.panel-administrativo') }}" class="btn flat-btn text-start p-3 d-flex align-items-center">
                            <div class="bg-light rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fa-solid fa-gauge text-danger fs-6"></i>
                            </div>
                            <div>
                                <span class="fw-bold d-block text-slate-800">Panel Administrativo</span>
                                <span class="small text-slate-500">Administrar usuarios</span>
                            </div>
                        </a>
                        <a href="{{ route('superadmin.roles.manageroles') }}" class="btn flat-btn text-start p-3 d-flex align-items-center">
                            <div class="bg-light rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fa-solid fa-sliders text-danger fs-6"></i>
                            </div>
                            <div>
                                <span class="fw-bold d-block text-slate-800">Gestión de Roles</span>
                                <span class="small text-slate-500">Administrar roles</span>
                            </div>
                        </a>
                        <a href="{{ route('superadmin.manage-ruler') }}" class="btn flat-btn text-start p-3 d-flex align-items-center">
                            <div class="bg-light rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fa-solid fa-gavel text-danger fs-6"></i>
                            </div>
                            <div>
                                <span class="fw-bold d-block text-slate-800">Gestionar Reglas</span>
                                <span class="small text-slate-500">Reglas académicas</span>
                            </div>
                        </a>
                    @endif

                    {{-- ADMIN_ACADEMICO --}}
                    @if(auth()->user()->hasRole('ADMIN_ACADEMICO'))
                        <a href="{{ route('admin-academico.create-student') }}" class="btn flat-btn text-start p-3 d-flex align-items-center">
                            <div class="bg-light rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fa-solid fa-user-plus text-danger fs-6"></i>
                            </div>
                            <div>
                                <span class="fw-bold d-block text-slate-800">Nuevo Alumno</span>
                                <span class="small text-slate-500">Crear usuarios</span>
                            </div>
                        </a>
                        <a href="{{ route('admin-academico.admin.academico.solicitudes.approve-student') }}" class="btn flat-btn text-start p-3 d-flex align-items-center">
                            <div class="bg-light rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fa-solid fa-user-check text-danger fs-6"></i>
                            </div>
                            <div>
                                <span class="fw-bold d-block text-slate-800">Aceptar Alumno</span>
                                <span class="small text-slate-500">Alumnos en espera</span>
                            </div>
                        </a>
                        <a href="{{ route('admin-academico.alumnos.modify-student') }}" class="btn flat-btn text-start p-3 d-flex align-items-center">
                            <div class="bg-light rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fa-solid fa-chart-pie text-danger fs-6"></i>
                            </div>
                            <div>
                                <span class="fw-bold d-block text-slate-800">Gestionar Alumno</span>
                                <span class="small text-slate-500">Dar de baja a estudiantes</span>
                            </div>
                        </a>
                        <a href="{{ route('admin-academico.admin-academico.beneficios.modify-benefit-student') }}" class="btn flat-btn text-start p-3 d-flex align-items-center">
                            <div class="bg-light rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fa-solid fa-graduation-cap text-danger fs-6"></i>
                            </div>
                            <div>
                                <span class="fw-bold d-block text-slate-800">Gestionar Becas</span>
                                <span class="small text-slate-500">Beneficios de estudiantes</span>
                            </div>
                        </a>
                        <a href="{{ route('admin-academico.pensum.pensum') }}" class="btn flat-btn text-start p-3 d-flex align-items-center">
                            <div class="bg-light rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fa-solid fa-book-open text-danger fs-6"></i>
                            </div>
                            <div>
                                <span class="fw-bold d-block text-slate-800">Gestionar Pensum</span>
                                <span class="small text-slate-500">Planes de estudio</span>
                            </div>
                        </a>
                        <a href="{{ route('admin-academico.materias.materias') }}" class="btn flat-btn text-start p-3 d-flex align-items-center">
                            <div class="bg-light rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fa-solid fa-school text-danger fs-6"></i>
                            </div>
                            <div>
                                <span class="fw-bold d-block text-slate-800">Gestionar Materias</span>
                                <span class="small text-slate-500">Crear o editar</span>
                            </div>
                        </a>
                    @endif

                    {{-- ADMIN_FINANCIERO --}}
                    @if(auth()->user()->hasRole('ADMIN_FINANCIERO'))
                        <a href="{{ route('adfinanciero.conceptospagos.index') }}" class="btn flat-btn text-start p-3 d-flex align-items-center">
                            <div class="bg-light rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fa-solid fa-tags text-danger fs-6"></i>
                            </div>
                            <div>
                                <span class="fw-bold d-block text-slate-800">Aranceles</span>
                                <span class="small text-slate-500">Gestión de aranceles</span>
                            </div>
                        </a>
                        <a href="{{ route('adfinanciero.cargos.create') }}" class="btn flat-btn text-start p-3 d-flex align-items-center">
                            <div class="bg-light rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fa-solid fa-file-circle-plus text-danger fs-6"></i>
                            </div>
                            <div>
                                <span class="fw-bold d-block text-slate-800">Generar Cargos</span>
                                <span class="small text-slate-500">Asignar nueva cuota</span>
                            </div>
                        </a>
                    @endif

                    {{-- CAJERO --}}
                    @if(auth()->user()->hasRole('CAJERO'))
                        <a href="{{ route('cajero.deuda.index') }}" class="btn flat-btn text-start p-3 d-flex align-items-center">
                            <div class="bg-light rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fa-solid fa-file-invoice-dollar text-danger fs-6"></i>
                            </div>
                            <div>
                                <span class="fw-bold d-block text-slate-800">Deuda Pendiente</span>
                                <span class="small text-slate-500">Liquidar cargos</span>
                            </div>
                        </a>
                        <a href="{{ route('cajero.ventanilla.index') }}" class="btn flat-btn text-start p-3 d-flex align-items-center">
                            <div class="bg-light rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fa-solid fa-cash-register text-danger fs-6"></i>
                            </div>
                            <div>
                                <span class="fw-bold d-block text-slate-800">Consultas Ventanilla</span>
                                <span class="small text-slate-500">Aranceles directos</span>
                            </div>
                        </a>
                        <a href="{{ route('cajero.pagos.create') }}" class="btn flat-btn text-start p-3 d-flex align-items-center">
                            <div class="bg-light rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fa-solid fa-money-bill-transfer text-danger fs-6"></i>
                            </div>
                            <div>
                                <span class="fw-bold d-block text-slate-800">Cobrar</span>
                                <span class="small text-slate-500">Registro de pagos</span>
                            </div>
                        </a>
                    @endif

                    {{-- COORDINADOR FACULTAD --}}
                    @if(auth()->user()->hasRole('COORDINADOR_FACULTAD'))
                        <a href="{{ route('coordinador-facultad.horarios-grupohorario.index') }}" class="btn flat-btn text-start p-3 d-flex align-items-center">
                            <div class="bg-light rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fa-solid fa-calendar text-danger fs-6"></i>
                            </div>
                            <div>
                                <span class="fw-bold d-block text-slate-800">Horarios</span>
                                <span class="small text-slate-500">Administrar grupos</span>
                            </div>
                        </a>
                        <a href="{{ route('coordinador-facultad.secciones.index') }}" class="btn flat-btn text-start p-3 d-flex align-items-center">
                            <div class="bg-light rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fa-solid fa-people-group text-danger fs-6"></i>
                            </div>
                            <div>
                                <span class="fw-bold d-block text-slate-800">Secciones</span>
                                <span class="small text-slate-500">Gestión de clases</span>
                            </div>
                        </a>
                        <a href="{{ route('coordinador-facultad.dashboard') }}" class="btn flat-btn text-start p-3 d-flex align-items-center">
                            <div class="bg-light rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fa-solid fa-chart-line text-danger fs-6"></i>
                            </div>
                            <div>
                                <span class="fw-bold d-block text-slate-800">Dashboard</span>
                                <span class="small text-slate-500">Proyección de secciones</span>
                            </div>
                        </a>
                    @endif

                    {{-- ESTUDIANTE --}}
                    @if(auth()->user()->hasRole('ESTUDIANTE'))
                        <a href="{{ route('student.pensum.index') }}" class="btn flat-btn text-start p-3 d-flex align-items-center">
                            <div class="bg-light rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fa-solid fa-book-open text-danger fs-6"></i>
                            </div>
                            <div>
                                <span class="fw-bold d-block text-slate-800">Pensum</span>
                                <span class="small text-slate-500">Plan académico</span>
                            </div>
                        </a>
                        <a href="{{ route('student.inscripcion.index') }}" class="btn flat-btn text-start p-3 d-flex align-items-center">
                            <div class="bg-light rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fa-solid fa-pen-to-square text-danger fs-6"></i>
                            </div>
                            <div>
                                <span class="fw-bold d-block text-slate-800">Inscripción</span>
                                <span class="small text-slate-500">En línea</span>
                            </div>
                        </a>
                        <a href="{{ route('student.record.index') }}" class="btn flat-btn text-start p-3 d-flex align-items-center">
                            <div class="bg-light rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fa-solid fa-graduation-cap text-danger fs-6"></i>
                            </div>
                            <div>
                                <span class="fw-bold d-block text-slate-800">Record Académico</span>
                                <span class="small text-slate-500">Historial de notas</span>
                            </div>
                        </a>
                        <a href="{{ route('adfinanciero.cargos.index') }}" class="btn flat-btn text-start p-3 d-flex align-items-center">
                            <div class="bg-light rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fa-solid fa-file-invoice-dollar text-danger fs-6"></i>
                            </div>
                            <div>
                                <span class="fw-bold d-block text-slate-800">Record Financiero</span>
                                <span class="small text-slate-500">Credenciales de pago</span>
                            </div>
                        </a>
                    @endif

                    {{-- DOCENTE --}}
                    @if(auth()->user()->hasRole('DOCENTE'))
                        <a href="{{ route('docente.materias') }}" class="btn flat-btn text-start p-3 d-flex align-items-center">
                            <div class="bg-light rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fa-solid fa-chalkboard-user text-danger fs-6"></i>
                            </div>
                            <div>
                                <span class="fw-bold d-block text-slate-800">Materias</span>
                                <span class="small text-slate-500">Cursos asignados</span>
                            </div>
                        </a>
                        <a href="{{ route('docente.prueba.notas.docentes') }}" class="btn flat-btn text-start p-3 d-flex align-items-center">
                            <div class="bg-light rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fa-solid fa-file-pen text-danger fs-6"></i>
                            </div>
                            <div>
                                <span class="fw-bold d-block text-slate-800">Prueba de Notas</span>
                                <span class="small text-slate-500">Evaluaciones</span>
                            </div>
                        </a>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
