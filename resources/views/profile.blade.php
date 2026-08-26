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

        <!-- Tarjeta Especial: Resumen de Seguridad -->
        <div class="col-12">
            <div class="neu-special-card p-4">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div>
                        <h6 class="fw-bold mb-1" style="color: var(--color-accent);">Estado de la Autenticación de Dos Factores</h6>
                        <p class="small text-muted m-0">
                            {{ auth()->user()->two_factor_secret ? 'Tu cuenta tiene la protección 2FA activada.' : 'Activa la protección 2FA en el Dashboard para mayor seguridad.' }}
                        </p>
                    </div>
                    <a href="{{ route('home') }}" class="btn neu-btn btn-sm text-nowrap align-self-start align-self-md-center">
                        Ir al Gestor 2FA
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection