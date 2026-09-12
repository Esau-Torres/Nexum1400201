@extends('layouts.app')

@section('title', 'Mi Perfil - NEXUM')
@section('titulo_navbar', 'Gestión de Perfil')

@section('content')
<div class="container-fluid px-2 px-md-4 py-3">

    <!-- ==========================================
         SECCIÓN 1: DATOS Y CREDENCIALES
    =========================================== -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="d-flex align-items-center gap-2 border-bottom pb-2">
                <i class="bi bi-person-badge fs-4" style="color: var(--color-accent);"></i>
                <h4 class="fw-bold text-dark m-0">Actualización de Datos y Credenciales</h4>
            </div>
        </div>

        <!-- Tarjeta 1: Información Personal -->
        <div class="col-12 col-xl-6">
            <div class="card border-0 shadow-sm rounded-3 p-3 p-sm-4 h-100 bg-white">
                <div class="d-flex align-items-center mb-3">
                    <div class="p-2 rounded-2 me-3 flex-shrink-0" style="background-color: var(--color-hover-bg);">
                        <i class="bi bi-person-circle fs-4" style="color: var(--color-accent);"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">Información Personal</h5>
                        <p class="text-secondary small mb-0">Actualiza tus nombres y cuenta de correo institucional.</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('user-profile-information.update') }}" class="d-flex flex-column justify-content-between flex-grow-1">
                    @csrf
                    @method('PUT')

                    <div>
                        <div class="mb-3">
                            <label for="name" class="form-label small fw-semibold text-dark">Nombre Completo</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted">
                                    <i class="bi bi-person"></i>
                                </span>
                                <input type="text" 
                                       name="name" 
                                       id="name" 
                                       class="form-control border-start-0 ps-0 @error('name', 'updateProfileInformation') is-invalid @enderror" 
                                       value="{{ old('name', auth()->user()->name) }}" 
                                       required>
                            </div>
                            @error('name', 'updateProfileInformation')
                                <span class="text-danger small mt-1 d-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="email" class="form-label small fw-semibold text-dark">Correo Electrónico</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted">
                                    <i class="bi bi-envelope"></i>
                                </span>
                                <input type="email" 
                                       name="email" 
                                       id="email" 
                                       class="form-control border-start-0 ps-0 @error('email', 'updateProfileInformation') is-invalid @enderror" 
                                       value="{{ old('email', auth()->user()->email) }}" 
                                       required>
                            </div>
                            @error('email', 'updateProfileInformation')
                                <span class="text-danger small mt-1 d-block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <button type="submit" class="btn text-white w-100 py-2 fw-semibold shadow-sm mt-auto" style="background-color: var(--color-accent);">
                        <i class="bi bi-floppy me-1"></i> Guardar Cambios
                    </button>
                </form>
            </div>
        </div>

        <!-- Tarjeta 2: Actualizar Contraseña -->
        <div class="col-12 col-xl-6">
            <div class="card border-0 shadow-sm rounded-3 p-3 p-sm-4 h-100 bg-white">
                <div class="d-flex align-items-center mb-3">
                    <div class="p-2 rounded-2 me-3 flex-shrink-0" style="background-color: var(--color-hover-bg);">
                        <i class="bi bi-shield-lock fs-4" style="color: var(--color-accent);"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">Actualizar Contraseña</h5>
                        <p class="text-secondary small mb-0">Usa una frase de contraseña larga y de alta entropía.</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('user-password.update') }}" class="d-flex flex-column justify-content-between flex-grow-1">
                    @csrf
                    @method('PUT')

                    <div>
                        <div class="mb-3">
                            <label for="current_password" class="form-label small fw-semibold text-dark">Contraseña Actual</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted">
                                    <i class="bi bi-key"></i>
                                </span>
                                <input type="password" 
                                       name="current_password" 
                                       id="current_password" 
                                       class="form-control border-start-0 ps-0 @error('current_password', 'updatePassword') is-invalid @enderror" 
                                       required>
                            </div>
                            @error('current_password', 'updatePassword')
                                <span class="text-danger small mt-1 d-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="row g-2 mb-4">
                            <div class="col-12 col-md-6">
                                <label for="password" class="form-label small fw-semibold text-dark">Nueva Contraseña</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted">
                                        <i class="bi bi-lock"></i>
                                    </span>
                                    <input type="password" 
                                           name="password" 
                                           id="password" 
                                           class="form-control border-start-0 ps-0 @error('password', 'updatePassword') is-invalid @enderror" 
                                           required>
                                </div>
                                @error('password', 'updatePassword')
                                    <span class="text-danger small mt-1 d-block">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="password_confirmation" class="form-label small fw-semibold text-dark">Confirmar Contraseña</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted">
                                        <i class="bi bi-check2-circle"></i>
                                    </span>
                                    <input type="password" 
                                           name="password_confirmation" 
                                           id="password_confirmation" 
                                           class="form-control border-start-0 ps-0" 
                                           required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-outline-dark w-100 py-2 fw-semibold mt-auto">
                        <i class="bi bi-arrow-repeat me-1"></i> Actualizar Contraseña
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- ==========================================
         SECCIÓN 2: SEGURIDAD Y ESTADO
    =========================================== -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="d-flex align-items-center gap-2 border-bottom pb-2">
                <i class="bi bi-shield-check fs-4" style="color: var(--color-accent);"></i>
                <h4 class="fw-bold text-dark m-0">Seguridad de la Cuenta y Auditoría</h4>
            </div>
        </div>

        <!-- Tarjeta 3: Seguridad 2FA -->
        <div class="col-12 col-xl-6">
            <div class="card border-0 shadow-sm rounded-3 p-3 p-sm-4 h-100 bg-white">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <div class="d-flex align-items-center">
                        <div class="p-2 rounded-2 me-3 flex-shrink-0" style="background-color: var(--color-hover-bg);">
                            <i class="bi bi-shield-lock-fill fs-4" style="color: var(--color-accent);"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0 text-dark">Autenticación en Dos Pasos (2FA)</h5>
                            <p class="text-secondary small mb-0">Protección criptográfica mediante protocolo TOTP RFC 6238.</p>
                        </div>
                    </div>
                    <div>
                        @if (auth()->user()->two_factor_confirmed_at)
                            <span class="badge bg-success-subtle text-success border border-success px-3 py-2">
                                <i class="bi bi-patch-check-fill me-1"></i> Habilitado y Confirmado
                            </span>
                        @elseif (auth()->user()->two_factor_secret)
                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning px-3 py-2">
                                <i class="bi bi-hourglass-split me-1"></i> Pendiente de Verificación
                            </span>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary border border-secondary px-3 py-2">
                                <i class="bi bi-slash-circle me-1"></i> Deshabilitado
                            </span>
                        @endif
                    </div>
                </div>

                <div class="d-flex flex-column justify-content-between flex-grow-1">
                    {{-- ESTADO 1: No configurado --}}
                    @if (! auth()->user()->two_factor_secret)
                        <div class="p-3 rounded-3 mb-4 border-start border-3 border-danger" style="background-color: var(--color-hover-bg);">
                            <p class="text-dark small mb-0">
                                La autenticación en dos pasos agrega una capa indispensable de defensa. Al activarla, se requerirá un token numérico dinámico generado desde tu dispositivo móvil autorizado para acceder al sistema NEXUM.
                            </p>
                        </div>

                        <button type="button" class="btn text-white w-100 py-2 fw-semibold shadow-sm" style="background-color: var(--color-accent);" data-bs-toggle="modal" data-bs-target="#confirmEnable2faModal">
                            <i class="bi bi-qr-code-scan me-1"></i> Habilitar 2FA
                        </button>

                    {{-- ESTADO 2: Habilitado pero no confirmado (Muestra el QR en la vista) --}}
                    @elseif (! auth()->user()->two_factor_confirmed_at)
                        <div class="border rounded-3 p-3 p-md-4 mb-3 bg-light">
                            <h6 class="fw-bold text-dark mb-2">
                                <i class="bi bi-1-circle-fill text-primary me-1"></i> Escanea el Código QR con tu App Autenticadora
                            </h6>
                            <p class="text-secondary small mb-3">
                                Utiliza aplicaciones estándar como Google Authenticator, Microsoft Authenticator o 1Password para registrar tu llave.
                            </p>

                            <div class="text-center my-3">
                                <div class="d-inline-block bg-white p-3 rounded-3 shadow-sm border">
                                    {!! auth()->user()->twoFactorQrCodeSvg() !!}
                                </div>
                            </div>

                            <div class="mt-3 p-2 bg-white rounded border text-center">
                                <span class="text-muted small d-block mb-1">¿No puedes escanear el código? Usa la clave de configuración manual:</span>
                                <code class="fs-6 fw-bold text-dark font-monospace user-select-all">{{ decrypt(auth()->user()->two_factor_secret) }}</code>
                            </div>

                            <hr class="my-4">

                            <h6 class="fw-bold text-dark mb-2">
                                <i class="bi bi-2-circle-fill text-primary me-1"></i> Ingresa el Código de Verificación
                            </h6>
                            <p class="text-secondary small mb-3">Ingresa el código temporal de 6 dígitos que muestra tu app para confirmar el enlace.</p>

                            <form method="POST" action="{{ url('/user/confirmed-two-factor-authentication') }}">
                                @csrf
                                <div class="row g-2 justify-content-center">
                                    <div class="col-12 col-sm-6 col-md-5">
                                        <input type="text" 
                                               name="code" 
                                               id="code" 
                                               class="form-control text-center font-monospace fs-5 py-2 @error('code') is-invalid @enderror" 
                                               placeholder="000000" 
                                               inputmode="numeric" 
                                               pattern="[0-9]*" 
                                               maxlength="6" 
                                               autocomplete="one-time-code" 
                                               required 
                                               autofocus>
                                        @error('code')
                                            <span class="text-danger small mt-1 d-block text-center">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="col-12 col-sm-4 col-md-3">
                                        <button type="submit" class="btn text-white w-100 py-2 fw-semibold h-100" style="background-color: var(--color-accent);">
                                            Confirmar
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                    {{-- ESTADO 3: Completamente activo y verificado --}}
                    @else
                        <div class="mb-4">
                            <label class="form-label small fw-semibold text-secondary mb-1">Códigos de Recuperación de Emergencia:</label>
                            <p class="text-secondary small mb-2">
                                Guarda estos códigos en un lugar seguro y sin conexión. Cada uno puede emplearse una única vez para recuperar el acceso en caso de pérdida de tu dispositivo:
                            </p>
                            <div class="p-3 rounded-3 border bg-light font-monospace small" style="max-height: 125px; overflow-y: auto;">
                                <div class="row g-2">
                                    @foreach (json_decode(decrypt(auth()->user()->two_factor_recovery_codes), true) as $recoveryCode)
                                        <div class="col-12 col-sm-6 text-secondary py-1 border-bottom border-light">
                                            <i class="bi bi-key-fill text-muted me-1"></i> {{ $recoveryCode }}
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="d-flex flex-column flex-sm-row gap-2 mt-auto">
                            {{-- Regenerar códigos de respaldo --}}
                            <form method="POST" action="{{ url('/user/two-factor-recovery-codes') }}" class="flex-grow-1">
                                @csrf
                                <button type="submit" class="btn btn-outline-secondary w-100 py-2 fw-semibold">
                                    <i class="bi bi-arrow-clockwise me-1"></i> Regenerar Códigos
                                </button>
                            </form>

                            {{-- Botón modal para deshabilitar --}}
                            <button type="button" class="btn btn-outline-danger flex-grow-1 py-2 fw-semibold" data-bs-toggle="modal" data-bs-target="#confirmDisable2faModal">
                                <i class="bi bi-shield-x me-1"></i> Deshabilitar 2FA
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>

          <!-- Tarjeta 4: verificacion de correo electronico  -->
        <div class="col-12 col-xl-6">
            <div class="card border-0 shadow-sm rounded-3 p-3 p-sm-4 h-100 bg-white d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                        <div class="d-flex align-items-center">
                            <div class="p-2 rounded-2 me-3 flex-shrink-0" style="background-color: var(--color-hover-bg);">
                                <i class="bi bi-envelope-at fs-4" style="color: var(--color-accent);"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-0 text-dark">Verificación de Correo</h5>
                                <p class="text-secondary small mb-0">Estado de validación de tu identidad institucional.</p>
                            </div>
                        </div>

                        <div>
                            @if (auth()->user()->hasVerifiedEmail())
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2">
                                    <i class="bi bi-patch-check-fill me-1"></i> Verificado
                                </span>
                            @else
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-3 py-2">
                                    <i class="bi bi-hourglass-split me-1"></i> Pendiente
                                </span>
                            @endif
                        </div>
                    </div>

                    @if (! auth()->user()->hasVerifiedEmail())
                        <div class="p-3 rounded-3 mb-4 border-start border-3 border-danger" style="background-color: var(--color-hover-bg);">
                            <p class="text-dark small mb-0">
                                Tu correo institucional <strong>{{ auth()->user()->email }}</strong> aún no ha sido autenticado. Algunas funciones avanzadas del sistema NEXUM permanecerán restringidas hasta completar este paso.
                            </p>
                        </div>
                    @else
                        <div class="p-3 rounded-3 mb-4 border-start border-3 border-success bg-light">
                            <p class="text-dark small mb-0">
                                Tu dirección <strong>{{ auth()->user()->email }}</strong> se encuentra verificada y vinculada activamente a tu expediente universitario.
                            </p>
                        </div>
                    @endif
                </div>

                <!-- Acciones -->
                <div class="pt-3 border-top mt-auto">
                    @if (auth()->user()->hasVerifiedEmail())
                        <button type="button" class="btn btn-light text-muted w-100 py-2 fw-semibold border" disabled>
                            <i class="bi bi-check-all me-1 text-success"></i> Identidad Confirmada
                        </button>
                    @else
                        <button type="button" class="btn text-white w-100 py-2 fw-semibold shadow-sm" style="background-color: var(--color-accent);" data-bs-toggle="modal" data-bs-target="#confirmEmailVerificationModal">
                            <i class="bi bi-send-check me-1"></i> Solicitar Enlace de Verificación
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <!-- Tarjeta 5: Estado y Sesión -->
        <div class="col-12 col-xl-6">
            <div class="card border-0 shadow-sm rounded-3 p-3 p-sm-4 h-100 bg-white d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex align-items-center mb-3">
                        <div class="p-2 rounded-2 me-3 flex-shrink-0" style="background-color: var(--color-special);">
                            <i class="bi bi-info-circle fs-4 text-dark"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0 text-dark">Estado del Usuario</h5>
                            <p class="text-secondary small mb-0">Parámetros institucionales y metadatos de sesión.</p>
                        </div>
                    </div>

                    <ul class="list-group list-group-flush small mb-4">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-light">
                            <span class="text-secondary">Identificador Institucional:</span>
                            <span class="font-monospace fw-semibold text-dark">#{{ auth()->user()->id }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-light">
                            <span class="text-secondary">Fecha de Incorporación:</span>
                            <span class="fw-semibold text-dark">{{ auth()->user()->created_at->format('d/m/Y H:i') }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-light">
                            <span class="text-secondary">Verificación de Correo:</span>
                            @if (auth()->user()->email_verified_at)
                                <span class="badge bg-success-subtle text-success border border-success px-2 py-1">Verificado</span>
                            @else
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning px-2 py-1">Pendiente</span>
                            @endif
                        </li>
                    </ul>
                </div>

                <!-- Cierre de Sesión Seguro -->
                <div class="pt-3 border-top">
                    <button type="button" class="btn btn-danger w-100 py-2 fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#confirmLogoutModal">
                        <i class="bi bi-box-arrow-right me-1"></i> Cerrar Sesión del Sistema
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- ==========================================================================
     MODALES DE CONFIRMACIÓN (BOOTSTRAP 5)
========================================================================== -->

<!-- Modal 1: Habilitar 2FA -->
<div class="modal fade" id="confirmEnable2faModal" tabindex="-1" aria-labelledby="confirmEnable2faLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-3">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark" id="confirmEnable2faLabel">
                    <i class="bi bi-shield-lock-fill me-2" style="color: var(--color-accent);"></i>Activar Doble Factor (2FA)
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body py-3">
                <p class="text-secondary mb-3">
                    ¿Deseas iniciar el aprovisionamiento del segundo factor de autenticación para tu cuenta institucional?
                </p>
                <div class="p-3 bg-light rounded-3 text-secondary small border-start border-3" style="border-left-color: var(--color-accent) !important;">
                    <i class="bi bi-info-circle-fill me-1" style="color: var(--color-accent);"></i>
                    Al confirmar, se generará el código QR en pantalla y las llaves maestras de rescate que deberás registrar inmediatamente.
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancelar</button>
                <form method="POST" action="{{ url('/user/two-factor-authentication') }}" class="m-0">
                    @csrf
                    <button type="submit" class="btn text-white px-4 fw-semibold" style="background-color: var(--color-accent);">
                        Sí, continuar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal 2: Deshabilitar 2FA -->
<div class="modal fade" id="confirmDisable2faModal" tabindex="-1" aria-labelledby="confirmDisable2faLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-3">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-danger" id="confirmDisable2faLabel">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Desactivar Seguridad 2FA
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body py-3">
                <p class="text-secondary mb-3">
                    ¿Estás seguro de que deseas deshabilitar la autenticación de dos factores?
                </p>
                <div class="p-3 bg-light rounded-3 text-danger small border-start border-3 border-danger">
                    <i class="bi bi-exclamation-circle-fill me-1"></i>
                    Esta acción degradará el perfil de seguridad de tu cuenta, haciéndola vulnerable ante incidentes de credenciales comprometidas.
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancelar</button>
                <form method="POST" action="{{ url('/user/two-factor-authentication') }}" class="m-0">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger px-4 fw-semibold">
                        Sí, deshabilitar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal 3: Confirmación de Cierre de Sesión -->
<div class="modal fade" id="confirmLogoutModal" tabindex="-1" aria-labelledby="confirmLogoutLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-3">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark" id="confirmLogoutLabel">
                    <i class="bi bi-box-arrow-right me-2 text-danger"></i>Cerrar Sesión Activa
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body py-3">
                <p class="text-secondary mb-0">
                    ¿Estás seguro de que deseas salir de la plataforma NEXUM? Toda transacción o formulario no guardado se perderá al invalidar la sesión.
                </p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Permanecer</button>
                <form method="POST" action="{{ route('logout') }}" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-danger px-4 fw-semibold">
                        Cerrar Sesión
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal 4: Verificacion De correo electronico -->
<div class="modal fade" id="confirmEmailVerificationModal" tabindex="-1" aria-labelledby="confirmEmailVerificationLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-3">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark" id="confirmEmailVerificationLabel">
                    <i class="bi bi-shield-check me-2" style="color: var(--color-accent);"></i>Verificación Institucional
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            
            <div class="modal-body py-3">
                <p class="text-secondary mb-3">
                    Se enviará un enlace temporal de validación a tu bandeja de entrada: <strong class="text-dark">{{ auth()->user()->email }}</strong>.
                </p>
                <div class="p-3 bg-light rounded-3 text-secondary small border-start border-3" style="border-left-color: var(--color-accent) !important;">
                    <i class="bi bi-info-circle-fill me-1" style="color: var(--color-accent);"></i>
                    Al confirmar el enlace desde tu correo, el sistema desbloqueará las consultas académicas, actas y gestiones administrativas de forma inmediata.
                </div>
            </div>

            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                    Cancelar
                </button>
                <form action="{{ route('verification.send') }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="btn text-white px-4 fw-semibold shadow-sm" style="background-color: var(--color-accent);">
                        <i class="bi bi-envelope-paper me-1"></i> Enviar Enlace
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- al realizar un cambio de contraseña este se eliminara la sesion actual despues de 5 seg -->
@push('scripts')
@if (session('auto_logout'))
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            let secondsLeft = 5;
            const interval = setInterval(() => {
                secondsLeft--;
                if (secondsLeft <= 0) {
                    clearInterval(interval);
                    
                    // Disparar el logout automático
                    const logoutForm = document.createElement('form');
                    logoutForm.method = 'POST';
                    logoutForm.action = "{{ route('logout') }}";
                    
                    const csrfToken = document.createElement('input');
                    csrfToken.type = 'hidden';
                    csrfToken.name = '_token';
                    csrfToken.value = "{{ csrf_token() }}";
                    
                    logoutForm.appendChild(csrfToken);
                    document.body.appendChild(logoutForm);
                    logoutForm.submit();
                }
            }, 1000);
        });
    </script>
@endif
@endpush

@endsection