@extends('layouts.app')

@section('title', 'Home')

@section('content')

<div class="container-fluid px-0">

    <!-- Mensajes de estado -->
    @if (session('status') == 'two-factor-authentication-enabled')
        <div class="alert neu-special-card text-dark border-0 p-3 mb-4">
            Autenticación en dos pasos habilitada. Escanea el código QR a continuación para configurarla.
        </div>
    @elseif (session('status') == 'two-factor-authentication-confirmed')
        <div class="alert neu-card border-0 p-3 mb-4 text-success fw-semibold">
            Código 2FA confirmado correctamente. La cuenta ahora está protegida.
        </div>
    @endif

    <div class="row">
        <!-- Tarjeta de Bienvenida y Datos -->
        <div class="col-12">
            <div class="neu-special-card p-4">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div>
                        <h6 class="fw-bold mb-1" style="color: var(--color-accent);">Bienvenido, {{ auth()->user()->name }}</h6>
                        <p class="small text-muted m-0">
                            En el menu lateral puedes navegar en el sitio web de UMA Santa Ana para realizar tus consultas de control, 
                            puedes gestionar tu perfil, cambiar tu contraseña y habilitar la autenticación de dos factores (2FA) para mayor seguridad, si aun no has hecho.
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row g-4">
            <!-- resumen general de datos de usuario -->
            <div class="col-lg-6">
                <div class="neu-card p-4 mb-4">
                    <h5 class="fw-bold mb-3" style="color: var(--color-accent);">Resumen General</h5>
                    <p class="text-muted">Has iniciado sesión correctamente en el sistema modular.</p>
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Correo:</span>
                        <span class="fw-semibold">{{ auth()->user()->email }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Estado 2FA:</span>
                        @if (auth()->user()->two_factor_secret)
                            <span class="badge neu-badge-accent px-2 py-1">Habilitado</span>
                        @else
                            <span class="badge bg-secondary px-2 py-1">Deshabilitado</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="neu-card p-4 mb-4">
                    <h5 class="fw-bold mb-3" style="color: var(--color-accent);">Resumen General</h5>
                    <p class="text-muted">Has iniciado sesión correctamente en el sistema modular.</p>
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Correo:</span>
                        <span class="fw-semibold">{{ auth()->user()->email }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Estado 2FA:</span>
                        @if (auth()->user()->two_factor_secret)
                            <span class="badge neu-badge-accent px-2 py-1">Habilitado</span>
                        @else
                            <span class="badge bg-secondary px-2 py-1">Deshabilitado</span>
                        @endif
                    </div>
                </div>
            </div>
            

        </div>
    </div>
</div>
@endsection