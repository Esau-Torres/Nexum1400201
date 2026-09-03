@extends('layouts.app')

@section('title', 'Mi Perfil - Nexum')

@section('titulo_navbar', 'Gestión de perfil')

@section('content')
<div class="container-fluid px-0">

    <!-- Alertas de Estado -->
    @if (session('status') == 'profile-information-updated')
        <div class="alert neu-card border-0 p-3 mb-4 text-success fw-semibold">
            Información de perfil actualizada exitosamente.
        </div>
    @elseif (session('status') == 'password-updated')
        <div class="alert neu-card border-0 p-3 mb-4 text-success fw-semibold">
            Contraseña actualizada con éxito.
        </div>
    @endif

    <div class="row g-4">
        <!-- Tarjeta: Datos Personales -->
        <div class="col-12 col-xl-6">
            <div class="neu-card p-4 h-100">
                <h5 class="fw-bold mb-3" style="color: var(--color-accent);">Información Personal</h5>
                <p class="text-muted small">Actualiza los datos de tu cuenta y dirección de correo electrónico.</p>

                <form method="POST" action="{{ route('user-profile-information.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nombre Completo</label>
                        <input type="text" name="name" class="form-control neu-input p-2" value="{{ old('name', auth()->user()->name) }}" required>
                        @error('name', 'updateProfileInformation')
                            <span class="text-danger small mt-1 d-block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-semibold">Correo Electrónico</label>
                        <input type="email" name="email" class="form-control neu-input p-2" value="{{ old('email', auth()->user()->email) }}" required>
                        @error('email', 'updateProfileInformation')
                            <span class="text-danger small mt-1 d-block">{{ $message }}</span>
                        @enderror
                    </div>

                    <button type="submit" class="btn neu-btn-accent w-100 py-2">
                        Guardar Cambios
                    </button>
                </form>
            </div>
        </div>

        <!-- Tarjeta: Modificar Contraseña -->
        <div class="col-12 col-xl-6">
            <div class="neu-card p-4 h-100">
                <h5 class="fw-bold mb-3">Actualizar Contraseña</h5>
                <p class="text-muted small">Asegúrate de utilizar una contraseña larga y segura.</p>

                <form method="POST" action="{{ route('user-password.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Contraseña Actual</label>
                        <input type="password" name="current_password" class="form-control neu-input p-2" required>
                        @error('current_password', 'updatePassword')
                            <span class="text-danger small mt-1 d-block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nueva Contraseña</label>
                        <input type="password" name="password" class="form-control neu-input p-2" required>
                        @error('password', 'updatePassword')
                            <span class="text-danger small mt-1 d-block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-semibold">Confirmar Nueva Contraseña</label>
                        <input type="password" name="password_confirmation" class="form-control neu-input p-2" required>
                    </div>

                    <button type="submit" class="btn neu-btn w-100 py-2">
                        Actualizar Contraseña
                    </button>
                </form>
            </div>
        </div>

       
        <!-- Tarjeta de Gestión 2FA -->
        <div class="col-lg-6">
            <div class="neu-card p-4">
                <h5 class="fw-bold mb-3">Seguridad y 2FA</h5>
                <p class="text-muted small">Protege tu cuenta vinculando una app como Google Authenticator o Authy.</p>

                @if (! auth()->user()->two_factor_secret)
                    <!-- Botón disparador del modal de confirmación -->
                    <button type="button" class="btn neu-btn-accent w-100 py-2" data-bs-toggle="modal" data-bs-target="#confirmEnable2faModal">
                        Habilitar 2FA
                    </button>
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
    <!-- Modal Global de Confirmación 2FA (Bootstrap 5) -->
    <div class="modal fade" id="confirmEnable2faModal" tabindex="-1" aria-labelledby="confirmEnable2faLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body py-3">
                    <p class="mb-2 fw-bold text-dark text-center">
                        ¿Deseas iniciar la configuración de autenticación de dos factores en tu cuenta?
                    </p>
                    <div class="p-3 bg-light rounded-3 text-secondary small border-start border-3 border-info">
                        <i class="bi bi-info-circle-fill me-1 text-info"></i>
                        Al confirmar, se generará un código QR único y claves de recuperación que deberás registrar en tu aplicación de autenticación para completar el enlace.
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    
                    {{-- Formulario oficial procesado por Fortify --}}
                    <form method="POST" action="{{ url('/user/two-factor-authentication') }}" class="m-0">
                        @csrf
                        <button type="submit" class="btn px-4 fw-semibold" style="background-color: var(--color-accent); color: #fff;">
                            Sí, continuar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection