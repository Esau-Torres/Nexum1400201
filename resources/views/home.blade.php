@extends('layouts.app')

@section('title', 'Dashboard - Home')

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

    <div class="row g-4">
        <!-- Tarjeta de Bienvenida y Datos -->
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

            <!-- Tarjeta Especial con color #fff9e1 -->
            <div class="neu-special-card p-4" x-data="{ count: 0 }">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold m-0" style="color: var(--color-accent);">Módulo de Monitoreo</h6>
                    <span class="badge bg-dark">Especial</span>
                </div>
                <p class="small text-muted mb-2">Canal reactivo de pruebas en memoria:</p>
                <h3 class="fw-bold" x-text="count"></h3>
                <button class="btn neu-btn btn-sm mt-2" @click="count++">Incrementar Evento</button>
            </div>
        </div>

        <!-- Tarjeta de Gestión 2FA -->
        <div class="col-lg-6">
            <div class="neu-card p-4">
                <h5 class="fw-bold mb-3">Seguridad y 2FA</h5>
                <p class="text-muted small">Protege tu cuenta vinculando una app como Google Authenticator o Authy.</p>

                @if (! auth()->user()->two_factor_secret)
                    <form method="POST" action="{{ url('/user/two-factor-authentication') }}">
                        @csrf
                        <button type="submit" class="btn neu-btn-accent w-100 py-2">
                            Habilitar 2FA
                        </button>
                    </form>
                @else
                    @if (! auth()->user()->two_factor_confirmed_at)
                        <div class="text-center my-3 p-3 neu-card">
                            <p class="fw-semibold small mb-2">1. Escanea el código QR:</p>
                            <div class="d-inline-block bg-white p-2 rounded shadow-sm">
                                {!! auth()->user()->twoFactorQrCodeSvg() !!}
                            </div>

                            <form method="POST" action="{{ url('/user/confirmed-two-factor-authentication') }}" class="mt-3">
                                @csrf
                                <div class="input-group mb-2 mx-auto" style="max-width: 260px;">
                                    <input type="text" name="code" class="form-control text-center font-monospace" placeholder="123456" required autofocus>
                                    <button type="submit" class="btn neu-btn-accent">Confirmar</button>
                                </div>
                            </form>
                        </div>
                    @endif

                    <div class="my-3">
                        <h6 class="fw-semibold small text-muted">Códigos de Recuperación:</h6>
                        <div class="p-3 neu-card font-monospace small" style="max-height: 110px; overflow-y: auto;">
                            @foreach (json_decode(decrypt(auth()->user()->two_factor_recovery_codes), true) as $code)
                                <div>{{ $code }}</div>
                            @endforeach
                        </div>
                    </div>

                    <form method="POST" action="{{ url('/user/two-factor-authentication') }}" class="mt-3">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn neu-btn w-100 py-2 text-danger">
                            Deshabilitar 2FA
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection