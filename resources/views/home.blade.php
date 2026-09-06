@extends('layouts.app')

@section('title', 'Dashboard - Home')

@section('content')
<div class="container-fluid p-4">

    <!-- Mensajes de estado -->
    @if (session('status') == 'two-factor-authentication-enabled')
        <div class="alert alert-warning border-0 shadow-sm rounded-4 p-3 mb-4">
            <div class="d-flex align-items-center">
                <i class="fa-solid fa-shield-halved fs-4 me-3 text-warning"></i>
                <div>
                    <strong>Autenticación en dos pasos habilitada.</strong>
                    <p class="mb-0 small">Escanea el código QR a continuación para configurarla.</p>
                </div>
            </div>
        </div>
    @elseif (session('status') == 'two-factor-authentication-confirmed')
        <div class="alert alert-success border-0 shadow-sm rounded-4 p-3 mb-4">
            <div class="d-flex align-items-center">
                <i class="fa-solid fa-circle-check fs-4 me-3 text-success"></i>
                <div>
                    <strong>Código 2FA confirmado correctamente.</strong>
                    <p class="mb-0 small">La cuenta ahora está protegida.</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Encabezado de página -->
    <div class="mb-4">
        <h1 class="h3 fw-bold mb-1">Dashboard</h1>
        <p class="text-muted mb-0">Bienvenido de vuelta. Aquí tienes un resumen de tu cuenta.</p>
    </div>

    <div class="row g-4">
        <!-- Columna Izquierda -->
        <div class="col-lg-6">
            <!-- Tarjeta de Resumen General -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle me-3" 
                             style="width: 40px; height: 40px; background-color: #fff5f5;">
                            <i class="fa-solid fa-chart-line text-danger"></i>
                        </div>
                        <h5 class="fw-bold mb-0 text-danger">Resumen General</h5>
                    </div>
                    
                    <p class="text-muted small mb-4">Has iniciado sesión correctamente en el sistema modular.</p>
                    
                    <div class="d-flex justify-content-between align-items-center py-3 border-bottom">
                        <span class="text-muted small">Correo electrónico</span>
                        <span class="fw-semibold">{{ auth()->user()->email }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-3">
                        <span class="text-muted small">Estado 2FA</span>
                        @if (auth()->user()->two_factor_secret)
                            <span class="badge bg-danger px-3 py-2">
                                <i class="fa-solid fa-shield-halved me-1"></i>Habilitado
                            </span>
                        @else
                            <span class="badge bg-secondary px-3 py-2">
                                <i class="fa-solid fa-shield me-1"></i>Deshabilitado
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Tarjeta Especial de Monitoreo -->
            <div class="card border-0 shadow-sm rounded-4" style="background-color: #fff9e1;">
                <div class="card-body p-4" x-data="{ count: 0 }">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex align-items-center">
                            <div class="d-inline-flex align-items-center justify-content-center rounded-circle me-3" 
                                 style="width: 40px; height: 40px; background-color: rgba(220, 53, 69, 0.1);">
                                <i class="fa-solid fa-bolt text-danger"></i>
                            </div>
                            <h6 class="fw-bold mb-0 text-danger">Módulo de Monitoreo</h6>
                        </div>
                        <span class="badge bg-dark px-3 py-2">Especial</span>
                    </div>
                    
                    <p class="small text-muted mb-3">Canal reactivo de pruebas en memoria:</p>
                    
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="display-4 fw-bold text-dark" x-text="count"></div>
                            <small class="text-muted">Eventos registrados</small>
                        </div>
                        <button class="btn btn-danger btn-lg" @click="count++">
                            <i class="fa-solid fa-plus me-2"></i>Incrementar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Columna Derecha -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle me-3" 
                             style="width: 40px; height: 40px; background-color: #fff5f5;">
                            <i class="fa-solid fa-lock text-danger"></i>
                        </div>
                        <h5 class="fw-bold mb-0">Seguridad y 2FA</h5>
                    </div>
                    
                    <p class="text-muted small mb-4">Protege tu cuenta vinculando una app como Google Authenticator o Authy.</p>

                    @if (! auth()->user()->two_factor_secret)
                        <!-- Habilitar 2FA -->
                        <div class="text-center py-4">
                            <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" 
                                 style="width: 80px; height: 80px; background-color: #fff5f5;">
                                <i class="fa-solid fa-shield-halved fs-1 text-danger"></i>
                            </div>
                            <h6 class="fw-bold mb-2">Activa la autenticación en dos pasos</h6>
                            <p class="text-muted small mb-4">Añade una capa extra de seguridad a tu cuenta</p>
                            
                            <form method="POST" action="{{ url('/user/two-factor-authentication') }}">
                                @csrf
                                <button type="submit" class="btn btn-danger btn-lg w-100 py-3">
                                    <i class="fa-solid fa-shield-halved me-2"></i>Habilitar 2FA
                                </button>
                            </form>
                        </div>
                    @else
                        <!-- 2FA ya habilitado -->
                        @if (! auth()->user()->two_factor_confirmed_at)
                            <!-- Pendiente de confirmación -->
                            <div class="text-center py-4 mb-4 border-bottom">
                                <div class="alert alert-info border-0 rounded-4 mb-4">
                                    <i class="fa-solid fa-circle-info me-2"></i>
                                    <strong>Paso 1:</strong> Escanea el código QR con tu app de autenticación
                                </div>
                                
                                <div class="d-inline-block bg-white p-3 rounded-4 shadow-sm mb-4">
                                    {!! auth()->user()->twoFactorQrCodeSvg() !!}
                                </div>

                                <form method="POST" action="{{ url('/user/confirmed-two-factor-authentication') }}">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label fw-medium small">Paso 2: Ingresa el código de verificación</label>
                                        <div class="input-group mx-auto" style="max-width: 300px;">
                                            <input type="text" name="code" class="form-control form-control-lg text-center font-monospace" 
                                                   placeholder="123456" required autofocus>
                                            <button type="submit" class="btn btn-danger">
                                                <i class="fa-solid fa-check me-1"></i>Confirmar
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        @else
                            <!-- 2FA confirmado -->
                            <div class="alert alert-success border-0 rounded-4 mb-4">
                                <div class="d-flex align-items-center">
                                    <i class="fa-solid fa-circle-check fs-4 me-3"></i>
                                    <div>
                                        <strong>2FA Activo y Confirmado</strong>
                                        <p class="mb-0 small">Tu cuenta está protegida con autenticación en dos pasos.</p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Códigos de Recuperación -->
                        <div class="mb-4">
                            <div class="d-flex align-items-center mb-3">
                                <i class="fa-solid fa-key text-danger me-2"></i>
                                <h6 class="fw-semibold mb-0 small">Códigos de Recuperación</h6>
                            </div>
                            <div class="card border-0 shadow-sm rounded-4 p-3" style="background-color: #f8f9fa;">
                                <div class="font-monospace small" style="max-height: 150px; overflow-y: auto;">
                                    @foreach (json_decode(decrypt(auth()->user()->two_factor_recovery_codes), true) as $code)
                                        <div class="py-1 border-bottom">
                                            <i class="fa-solid fa-circle-small text-muted me-2"></i>{{ $code }}
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <p class="text-muted small mt-2">
                                <i class="fa-solid fa-triangle-exclamation me-1"></i>
                                Guarda estos códigos en un lugar seguro. Cada código solo puede usarse una vez.
                            </p>
                        </div>

                        <!-- Deshabilitar 2FA -->
                        <form method="POST" action="{{ url('/user/two-factor-authentication') }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger w-100 py-3">
                                <i class="fa-solid fa-shield-slash me-2"></i>Deshabilitar 2FA
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection