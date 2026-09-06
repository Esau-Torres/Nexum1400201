@extends('layouts.app')

@section('title', 'Mi Perfil - Nexum')

@section('content')
<div class="container-fluid p-4">

    <!-- Alertas de Estado -->
    @if (session('status') == 'profile-information-updated')
        <div class="alert alert-success border-0 shadow-sm rounded-4 p-3 mb-4">
            <div class="d-flex align-items-center">
                <i class="fa-solid fa-circle-check fs-4 me-3"></i>
                <div>
                    <strong>Información de perfil actualizada exitosamente.</strong>
                    <p class="mb-0 small">Tus datos personales han sido guardados correctamente.</p>
                </div>
            </div>
        </div>
    @elseif (session('status') == 'password-updated')
        <div class="alert alert-success border-0 shadow-sm rounded-4 p-3 mb-4">
            <div class="d-flex align-items-center">
                <i class="fa-solid fa-circle-check fs-4 me-3"></i>
                <div>
                    <strong>Contraseña actualizada con éxito.</strong>
                    <p class="mb-0 small">Tu nueva contraseña ya está activa.</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Encabezado de página -->
    <div class="mb-4">
        <h1 class="h3 fw-bold mb-1">Mi Perfil</h1>
        <p class="text-muted mb-0">Gestiona tu información personal y configuración de seguridad.</p>
    </div>

    <div class="row g-4">
        <!-- Tarjeta: Datos Personales -->
        <div class="col-12 col-xl-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle me-3" 
                             style="width: 40px; height: 40px; background-color: #fff5f5;">
                            <i class="fa-solid fa-user text-danger"></i>
                        </div>
                        <h5 class="fw-bold mb-0">Información Personal</h5>
                    </div>
                    
                    <p class="text-muted small mb-4">Actualiza los datos de tu cuenta y dirección de correo electrónico.</p>

                    <form method="POST" action="{{ route('user-profile-information.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label fw-medium small">Nombre Completo</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="fa-solid fa-user text-muted"></i>
                                </span>
                                <input type="text" name="name" class="form-control border-start-0" 
                                       value="{{ old('name', auth()->user()->name) }}" required>
                            </div>
                            @error('name', 'updateProfileInformation')
                                <span class="text-danger small mt-1 d-block">
                                    <i class="fa-solid fa-circle-exclamation me-1"></i>{{ $message }}
                                </span>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-medium small">Correo Electrónico</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="fa-solid fa-envelope text-muted"></i>
                                </span>
                                <input type="email" name="email" class="form-control border-start-0" 
                                       value="{{ old('email', auth()->user()->email) }}" required>
                            </div>
                            @error('email', 'updateProfileInformation')
                                <span class="text-danger small mt-1 d-block">
                                    <i class="fa-solid fa-circle-exclamation me-1"></i>{{ $message }}
                                </span>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-danger w-100 py-2">
                            <i class="fa-solid fa-floppy-disk me-2"></i>Guardar Cambios
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Tarjeta: Modificar Contraseña -->
        <div class="col-12 col-xl-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle me-3" 
                             style="width: 40px; height: 40px; background-color: #fff5f5;">
                            <i class="fa-solid fa-key text-danger"></i>
                        </div>
                        <h5 class="fw-bold mb-0">Actualizar Contraseña</h5>
                    </div>
                    
                    <p class="text-muted small mb-4">Asegúrate de utilizar una contraseña larga y segura para proteger tu cuenta.</p>

                    <form method="POST" action="{{ route('user-password.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label fw-medium small">Contraseña Actual</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="fa-solid fa-lock text-muted"></i>
                                </span>
                                <input type="password" name="current_password" class="form-control border-start-0" required>
                            </div>
                            @error('current_password', 'updatePassword')
                                <span class="text-danger small mt-1 d-block">
                                    <i class="fa-solid fa-circle-exclamation me-1"></i>{{ $message }}
                                </span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-medium small">Nueva Contraseña</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="fa-solid fa-lock text-muted"></i>
                                </span>
                                <input type="password" name="password" class="form-control border-start-0" required>
                            </div>
                            @error('password', 'updatePassword')
                                <span class="text-danger small mt-1 d-block">
                                    <i class="fa-solid fa-circle-exclamation me-1"></i>{{ $message }}
                                </span>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-medium small">Confirmar Nueva Contraseña</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="fa-solid fa-lock text-muted"></i>
                                </span>
                                <input type="password" name="password_confirmation" class="form-control border-start-0" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-danger w-100 py-2">
                            <i class="fa-solid fa-key me-2"></i>Actualizar Contraseña
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Tarjeta Especial: Resumen de Seguridad -->
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4" style="background-color: #fff9e1;">
                <div class="card-body p-4">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                        <div class="d-flex align-items-start">
                            <div class="d-inline-flex align-items-center justify-content-center rounded-circle me-3 flex-shrink-0" 
                                 style="width: 48px; height: 48px; background-color: rgba(220, 53, 69, 0.1);">
                                <i class="fa-solid fa-shield-halved fs-5 text-danger"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1 text-danger">Estado de la Autenticación de Dos Factores</h6>
                                <p class="small text-muted mb-0">
                                    @if (auth()->user()->two_factor_secret)
                                        <i class="fa-solid fa-circle-check text-success me-1"></i>
                                        Tu cuenta tiene la protección 2FA activada.
                                    @else
                                        <i class="fa-solid fa-triangle-exclamation text-warning me-1"></i>
                                        Activa la protección 2FA en el Dashboard para mayor seguridad.
                                    @endif
                                </p>
                            </div>
                        </div>
                        <a href="{{ route('home') }}" class="btn btn-danger btn-sm text-nowrap align-self-start align-self-md-center">
                            <i class="fa-solid fa-shield-halved me-2"></i>Ir al Gestor 2FA
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection