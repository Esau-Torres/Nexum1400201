@extends('layouts.app')

@section('title', 'Mi Perfil - NEXUM')
@section('titulo_navbar', 'Gestión de Perfil')
@push('styles')
    @vite('resources/css/profile.css')
@endpush

@section('content')
<div class="profile-wrapper">
    <div class="container-fluid px-2 px-md-4 py-3 mx-auto" style="max-width: 1100px;">

        @php
            $user   = auth()->user();
            $alumno = $user->alumno;
        @endphp

        {{-- ============================================================
            SECCIÓN 0: CARNET VIRTUAL (solo estudiantes)
            ============================================================ --}}
        @if($alumno)
            <div class="row g-4 mb-4">
                <div class="col-12">
                    <div class="d-flex align-items-center gap-2 border-bottom pb-2">
                        <i class="bi bi-credit-card-2-front fs-4" style="color: var(--color-accent);"></i>
                        <h4 class="fw-bold text-dark m-0">CARNET UNIVERSITARIO</h4>
                    </div>
                </div>

                <div class="col-12">
                    <div class="row g-4">

                        {{-- Carnet visual --}}
                    <div class="col-12 col-xl-7">
                        <div class="carnet-card">
                            <div class="carnet-header">
                                <div class="carnet-logo">
                                    <i class="bi bi-mortarboard-fill"></i>
                                    <span>UMA</span>
                                </div>
                                <div class="carnet-header-text">
                                    <strong>UNIVERSIDAD MODULAR ABIERTA</strong>
                                    <small>Carnet Universitario Oficial</small>
                                </div>
                            </div>

                            <div class="carnet-body">
                                {{-- Foto --}}
                                <div class="carnet-foto">
                                    @if($alumno->foto_carnet_mostrar)
                                        <img src="{{ $alumno->foto_carnet_mostrar }}"
                                            alt="Foto del carnet"
                                            class="carnet-foto-img"
                                            onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">

                                        {{-- Placeholder oculto que se muestra si la imagen falla --}}
                                        <div class="carnet-foto-placeholder" style="display:none;">
                                            <i class="bi bi-person-fill"></i>
                                        </div>

                                        @if($alumno->foto_carnet_es_subida)
                                            @if($alumno->foto_carnet_validada_at)
                                                <span class="carnet-foto-badge badge-validada">
                                                    <i class="bi bi-patch-check-fill"></i> Validada
                                                </span>
                                            @endif
                                        @else
                                            <span class="carnet-foto-badge badge-pendiente">
                                                <i class="bi bi-exclamation-triangle-fill"></i> Del expediente
                                            </span>
                                        @endif
                                    @else
                                        <div class="carnet-foto-placeholder">
                                            <i class="bi bi-person-fill"></i>
                                        </div>
                                    @endif
                                </div>

                                {{-- Datos --}}
                                <div class="carnet-datos">
                                    <div class="carnet-datos-nombre">
                                        {{ $user->name }}
                                    </div>
                                    <div class="carnet-datos-carrera">
                                        {{ $alumno->carrera->nombre ?? '—' }}
                                    </div>
                                    <div class="carnet-datos-facultad">
                                        <small>{{ $alumno->carrera->facultad->nombre ?? '' }}</small>
                                    </div>

                                    <div class="carnet-datos-grid">
                                        <div>
                                            <span class="carnet-label">Carnet</span>
                                            <span class="carnet-value font-monospace">{{ $alumno->codigo_estudiante }}</span>
                                        </div>
                                        <div>
                                            <span class="carnet-label">Correo institucional</span>
                                            <span class="carnet-value">{{ $alumno->correo_institucional }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="carnet-footer">
                                <div>
                                    <span class="carnet-label">Vigencia</span>
                                    <span class="carnet-value">{{ $alumno->anio_vigencia_carnet }}</span>
                                </div>
                                <div>
                                    <span class="carnet-label">Alumno: </span>
                                    <span class="carnet-value"> {{ $alumno->estado_carrera ?? 'ACTIVO' }} </span>
                                </div>
                                <div class="carnet-qr-placeholder">
                                    <i class="bi bi-qr-code"></i>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info small mt-3 mb-0">
                            <i class="bi bi-info-circle-fill me-1"></i>
                            El carnet virtual tiene vigencia anual. La foto debe ser un retrato reciente,
                            con fondo claro y rostro visible. El sistema valida la imagen automáticamente
                            antes de hacerla efectiva.
                        </div>
                    </div>

                        {{-- Subida de foto --}}
                        <div class="col-12 col-xl-5">
                            <div class="card border-0 shadow-sm rounded-3 p-4 h-100 bg-white">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="p-2 rounded-2 me-3" style="background-color: var(--color-hover-bg);">
                                        <i class="bi bi-camera-fill fs-4" style="color: var(--color-accent);"></i>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mb-0">Foto del carnet</h5>
                                        <p class="text-secondary small mb-0">Subí una foto reciente para tu carnet virtual.</p>
                                    </div>
                                </div>

                                @if(session('success'))
                                    <div class="alert alert-success small py-2 mb-3">
                                        <i class="bi bi-check-circle-fill me-1"></i> {{ session('success') }}
                                    </div>
                                @endif
                                @if(session('error'))
                                    <div class="alert alert-danger small py-2 mb-3">
                                        <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ session('error') }}
                                    </div>
                                @endif
                                @if($errors->any())
                                    <div class="alert alert-warning small py-2 mb-3">
                                        <ul class="mb-0 ps-3">
                                            @foreach($errors->all() as $e)
                                                <li>{{ $e }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                @if($alumno->foto_carnet_validada_at)
                                    <div class="alert alert-success small py-2 mb-3">
                                        <i class="bi bi-patch-check-fill me-1"></i>
                                        Foto validada automáticamente el
                                        {{ $alumno->foto_carnet_validada_at->format('d/m/Y H:i') }}.
                                    </div>
                                @elseif($alumno->foto_carnet)
                                    <div class="alert alert-warning small py-2 mb-3">
                                        <i class="bi bi-hourglass-split me-1"></i>
                                        Foto subida. Pendiente de validación automática.
                                    </div>
                                @else
                                    <div class="alert alert-info small py-2 mb-3">
                                        <i class="bi bi-info-circle-fill me-1"></i>
                                        Aún no subiste una foto para el carnet. Se usará la del expediente si existe.
                                    </div>
                                @endif

                                <form method="POST"
                                    action="{{ route('estudiante.carnet.store') }}"
                                    enctype="multipart/form-data"
                                    id="form-foto-carnet">
                                    @csrf

                                    <div class="mb-3">
                                        <label for="foto_carnet" class="form-label small fw-semibold">
                                            Nueva foto (JPG/PNG, máx. 2 MB)
                                        </label>
                                        <input type="file"
                                            name="foto_carnet"
                                            id="foto_carnet"
                                            class="form-control @error('foto_carnet') is-invalid @enderror"
                                            accept=".jpg,.jpeg,.png"
                                            required>
                                        @error('foto_carnet')
                                            <span class="text-danger small">{{ $message }}</span>
                                        @enderror
                                        <div class="form-text small">
                                            La foto debe mostrar tu rostro de frente, con fondo claro,
                                            sin lentes de sol ni gorra. El sistema validará la imagen automáticamente.
                                        </div>
                                    </div>

                                    {{-- Preview --}}
                                    <div id="preview-container" class="mb-3 d-none text-center">
                                        <img id="preview-img" src="" alt="Preview" class="img-fluid rounded border" style="max-height: 180px;">
                                        <div id="rostro-status" class="mt-2 small"></div>
                                    </div>

                                    {{-- Hidden que envía si se detectó rostro --}}
                                    <input type="hidden" name="rostro_detectado" id="rostro_detectado" value="0">

                                    <button type="submit"
                                            id="btn-subir-carnet"
                                            class="btn text-white w-100 py-2 fw-semibold"
                                            style="background-color: var(--color-accent);"
                                            disabled>
                                        <i class="bi bi-cloud-upload me-1"></i> Subir foto del carnet
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- ============================================================
            SECCIÓN 1: DATOS Y CREDENCIALES (global)
            ============================================================ --}}
            <div class="row g-4 mb-4 text-medium">
                <div class="col-12">
                    <div class="d-flex align-items-center gap-2 border-bottom pb-2">
                        <i class="bi bi-person-badge fs-4" style="color: var(--color-accent);"></i>
                        <h4 class="fw-bold text-dark m-0">ACTUALIZACION DE DATOS Y CREDENCIALES</h4>
                    </div>
                </div>

                <!-- Tarjeta 1: Información Personal -->
                <div class="col-12 col-xl-6">
                    <div class="card border-0 shadow-sm rounded-4 h-100 bg-white overflow-hidden">
                        <div class="card-header bg-white border-0 pt-4 px-4 pb-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                                    style="width: 48px; height: 48px; background-color: var(--color-hover-bg);">
                                    <i class="bi bi-person-circle fs-4" style="color: var(--color-accent);"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-0 text-dark">Información Personal</h5>
                                    <p class="text-secondary small mb-0">Actualizá tu nombre y correo institucional.</p>
                                </div>
                            </div>
                        </div>

                        <div class="card-body px-4 pb-4">
                            <form method="POST"
                                action="{{ route('user-profile-information.update') }}"
                                class="d-flex flex-column h-100">
                                @csrf
                                @method('PUT')

                                <div class="mb-3">
                                    <label for="name" class="form-label small fw-semibold text-dark">
                                        Nombre completo
                                    </label>
                                    <div class="input-group input-group-lg">
                                        <span class="input-group-text bg-light border-end-0 text-muted">
                                            <i class="bi bi-person"></i>
                                        </span>
                                        <input type="text"
                                            name="name"
                                            id="name"
                                            class="form-control border-start-0 fs-6 @error('name', 'updateProfileInformation') is-invalid @enderror"
                                            value="{{ old('name', auth()->user()->name) }}"
                                            placeholder="Tu nombre completo"
                                            required>
                                    </div>
                                    @error('name', 'updateProfileInformation')
                                        <span class="text-danger small mt-1 d-block">
                                            <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                                        </span>
                                    @enderror
                                </div>

                                <div class="mb-4">
                                    <label for="email" class="form-label small fw-semibold text-dark">
                                        Correo electrónico
                                    </label>
                                    <div class="input-group input-group-lg">
                                        <span class="input-group-text bg-light border-end-0 text-muted">
                                            <i class="bi bi-envelope"></i>
                                        </span>
                                        <input type="email"
                                            name="email"
                                            id="email"
                                            class="form-control border-start-0 fs-6 @error('email', 'updateProfileInformation') is-invalid @enderror"
                                            value="{{ old('email', auth()->user()->email) }}"
                                            placeholder="usuario@ejemplo.com"
                                            required>
                                    </div>
                                    @error('email', 'updateProfileInformation')
                                        <span class="text-danger small mt-1 d-block">
                                            <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                                        </span>
                                    @enderror
                                </div>

                                <button type="submit"
                                        class="btn text-white w-100 py-2 fw-semibold shadow-sm mt-auto"
                                        style="background-color: var(--color-accent);">
                                    <i class="bi bi-floppy me-1"></i> Guardar cambios
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Tarjeta 2: Actualizar Contraseña -->
                <div class="col-12 col-xl-6">
                    <div class="card border-0 shadow-sm rounded-4 h-100 bg-white overflow-hidden">
                        <div class="card-header bg-white border-0 pt-4 px-4 pb-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                                    style="width: 48px; height: 48px; background-color: var(--color-hover-bg);">
                                    <i class="bi bi-shield-lock fs-4" style="color: var(--color-accent);"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-0 text-dark">Actualizar Contraseña</h5>
                                    <p class="text-secondary small mb-0">Usá una contraseña larga y difícil de adivinar.</p>
                                </div>
                            </div>
                        </div>

                        <div class="card-body px-4 pb-4">
                            <form method="POST"
                                action="{{ route('user-password.update') }}"
                                class="d-flex flex-column h-100">
                                @csrf
                                @method('PUT')

                                <div class="mb-3">
                                    <label for="current_password" class="form-label small fw-semibold text-dark">
                                        Contraseña actual
                                    </label>
                                    <div class="input-group input-group-lg">
                                        <span class="input-group-text bg-light border-end-0 text-muted">
                                            <i class="bi bi-key"></i>
                                        </span>
                                        <input type="password"
                                            name="current_password"
                                            id="current_password"
                                            class="form-control border-start-0 fs-6 @error('current_password', 'updatePassword') is-invalid @enderror"
                                            placeholder="••••••••"
                                            required>
                                    </div>
                                    @error('current_password', 'updatePassword')
                                        <span class="text-danger small mt-1 d-block">
                                            <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                                        </span>
                                    @enderror
                                </div>

                                <div class="row g-3 mb-4">
                                    <div class="col-12 col-md-6">
                                        <label for="password" class="form-label small fw-semibold text-dark">
                                            Nueva contraseña
                                        </label>
                                        <div class="input-group input-group-lg">
                                            <span class="input-group-text bg-light border-end-0 text-muted">
                                                <i class="bi bi-lock"></i>
                                            </span>
                                            <input type="password"
                                                name="password"
                                                id="password"
                                                class="form-control border-start-0 fs-6 @error('password', 'updatePassword') is-invalid @enderror"
                                                placeholder="••••••••"
                                                required>
                                        </div>
                                        @error('password', 'updatePassword')
                                            <span class="text-danger small mt-1 d-block">
                                                <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                                            </span>
                                        @enderror
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label for="password_confirmation" class="form-label small fw-semibold text-dark">
                                            Confirmar contraseña
                                        </label>
                                        <div class="input-group input-group-lg">
                                            <span class="input-group-text bg-light border-end-0 text-muted">
                                                <i class="bi bi-check2-circle"></i>
                                            </span>
                                            <input type="password"
                                                name="password_confirmation"
                                                id="password_confirmation"
                                                class="form-control border-start-0 fs-6"
                                                placeholder="••••••••"
                                                required>
                                        </div>
                                    </div>
                                </div>

                                <button type="submit"
                                        class="btn btn-outline-danger w-100 py-2 fw-semibold mt-auto">
                                    <i class="bi bi-arrow-repeat me-1"></i> Actualizar contraseña
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        <!-- ==========================================
            SECCIÓN 2: SEGURIDAD Y ESTADO (global)
        =========================================== -->
            <div class="row g-4 mb-4 text-medium">
                <div class="col-12">
                    <div class="d-flex align-items-center gap-2 border-bottom pb-2">
                        <i class="bi bi-shield-check fs-4" style="color: var(--color-accent);"></i>
                        <h4 class="fw-bold text-dark m-0">SEGURIDAD DE CUENTA</h4>
                    </div>
                </div>

                <!-- Tarjeta 3: Seguridad 2FA -->
                <div class="col-12 col-xl-6">
                    <div class="card border-0 shadow-sm rounded-4 h-100 bg-white overflow-hidden">
                        <div class="card-header bg-white border-0 pt-4 px-4 pb-3">
                            <div class="d-flex justify-content-between align-items-start gap-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                                        style="width: 48px; height: 48px; background-color: var(--color-hover-bg);">
                                        <i class="bi bi-shield-lock-fill fs-4" style="color: var(--color-accent);"></i>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mb-0 text-dark">Autenticación en Dos Pasos</h5>
                                        <p class="text-secondary small mb-0">Protección mediante TOTP RFC 6238.</p>
                                    </div>
                                </div>
                                <div class="flex-shrink-0">
                                    @if (auth()->user()->two_factor_confirmed_at)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                            <i class="bi bi-patch-check-fill me-1"></i> Activo
                                        </span>
                                    @elseif (auth()->user()->two_factor_secret)
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1">
                                            <i class="bi bi-hourglass-split me-1"></i> Pendiente
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1">
                                            <i class="bi bi-slash-circle me-1"></i> Inactivo
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="card-body px-4 pb-4 d-flex flex-column">
                            {{-- ESTADO 1: No configurado --}}
                            @if (! auth()->user()->two_factor_secret)
                                <div class="p-3 rounded-3 mb-3 border-start border-3 border-danger"
                                    style="background-color: var(--color-hover-bg);">
                                    <p class="text-dark small mb-0">
                                        La autenticación en dos pasos agrega una capa indispensable de defensa.
                                        Al activarla, se requerirá un token dinámico generado desde tu dispositivo móvil.
                                    </p>
                                </div>

                                <button type="button"
                                        class="btn text-white w-100 py-2 fw-semibold shadow-sm mt-auto"
                                        style="background-color: var(--color-accent);"
                                        data-bs-toggle="modal"
                                        data-bs-target="#confirmEnable2faModal">
                                    <i class="bi bi-qr-code-scan me-1"></i> Habilitar 2FA
                                </button>

                            {{-- ESTADO 2: Habilitado pero no confirmado --}}
                            @elseif (! auth()->user()->two_factor_confirmed_at)
                                <div class="border rounded-3 p-3 mb-3 bg-light">
                                    <h6 class="fw-bold text-dark mb-2">
                                        <i class="bi bi-1-circle-fill text-primary me-1"></i>
                                        Escaneá el código QR con tu app autenticadora
                                    </h6>
                                    <p class="text-secondary small mb-3">
                                        Usá Google Authenticator, Microsoft Authenticator o 1Password.
                                    </p>

                                    <div class="text-center my-3">
                                        <div class="d-inline-block bg-white p-3 rounded-3 shadow-sm border">
                                            {!! auth()->user()->twoFactorQrCodeSvg() !!}
                                        </div>
                                    </div>

                                    <div class="mt-3 p-2 bg-white rounded border text-center">
                                        <span class="text-muted small d-block mb-1">
                                            Clave de configuración manual:
                                        </span>
                                        <code class="fs-6 fw-bold text-dark font-monospace user-select-all">
                                            {{ decrypt(auth()->user()->two_factor_secret) }}
                                        </code>
                                    </div>

                                    <hr class="my-3">

                                    <h6 class="fw-bold text-dark mb-2">
                                        <i class="bi bi-2-circle-fill text-primary me-1"></i>
                                        Ingresá el código de verificación
                                    </h6>
                                    <p class="text-secondary small mb-3">
                                        Código de 6 dígitos que muestra tu app.
                                    </p>

                                    <form method="POST" action="{{ url('/user/confirmed-two-factor-authentication') }}">
                                        @csrf
                                        <div class="row g-2 justify-content-center">
                                            <div class="col-12 col-sm-6">
                                                <input type="text"
                                                    name="code"
                                                    id="code"
                                                    class="form-control text-center font-monospace fs-5 py-2 @error('code') is-invalid @enderror"
                                                    placeholder="000000"
                                                    inputmode="numeric"
                                                    pattern="[0-9]*"
                                                    maxlength="6"
                                                    autocomplete="one-time-code"
                                                    required>
                                                @error('code')
                                                    <span class="text-danger small mt-1 d-block text-center">{{ $message }}</span>
                                                @enderror
                                            </div>
                                            <div class="col-12 col-sm-4">
                                                <button type="submit"
                                                        class="btn text-white w-100 py-2 fw-semibold h-100"
                                                        style="background-color: var(--color-accent);">
                                                    Confirmar
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                            {{-- ESTADO 3: Completamente activo --}}
                            @else
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-secondary mb-1">
                                        Códigos de recuperación
                                    </label>
                                    <p class="text-secondary small mb-2">
                                        Guardalos en un lugar seguro. Cada uno puede usarse una sola vez.
                                    </p>
                                    <div class="p-3 rounded-3 border bg-light font-monospace small"
                                        style="max-height: 140px; overflow-y: auto;">
                                        <div class="row g-1">
                                            @foreach (json_decode(decrypt(auth()->user()->two_factor_recovery_codes), true) as $recoveryCode)
                                                <div class="col-12 col-sm-6 text-secondary py-1">
                                                    <i class="bi bi-key-fill text-muted me-1"></i>
                                                    <span class="user-select-all">{{ $recoveryCode }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex flex-column flex-sm-row gap-2 mt-auto">
                                    <form method="POST"
                                        action="{{ url('/user/two-factor-recovery-codes') }}"
                                        class="flex-grow-1">
                                        @csrf
                                        <button type="submit"
                                                class="btn btn-outline-secondary w-100 py-2 fw-semibold">
                                            <i class="bi bi-arrow-clockwise me-1"></i> Regenerar códigos
                                        </button>
                                    </form>

                                    <button type="button"
                                            class="btn btn-outline-danger flex-grow-1 py-2 fw-semibold"
                                            data-bs-toggle="modal"
                                            data-bs-target="#confirmDisable2faModal">
                                        <i class="bi bi-shield-x me-1"></i> Deshabilitar
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Tarjeta 4: Verificación de correo -->
                <div class="col-12 col-xl-6">
                    <div class="card border-0 shadow-sm rounded-4 h-100 bg-white overflow-hidden d-flex flex-column">
                        <div class="card-header bg-white border-0 pt-4 px-4 pb-3">
                            <div class="d-flex justify-content-between align-items-start gap-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                                        style="width: 48px; height: 48px; background-color: var(--color-hover-bg);">
                                        <i class="bi bi-envelope-at fs-4" style="color: var(--color-accent);"></i>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mb-0 text-dark">Verificación de Correo</h5>
                                        <p class="text-secondary small mb-0">Estado de validación de tu identidad.</p>
                                    </div>
                                </div>
                                <div class="flex-shrink-0">
                                    @if (auth()->user()->hasVerifiedEmail())
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                            <i class="bi bi-patch-check-fill me-1"></i> Verificado
                                        </span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1">
                                            <i class="bi bi-hourglass-split me-1"></i> Pendiente
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="card-body px-4 pb-4 d-flex flex-column">
                            @if (! auth()->user()->hasVerifiedEmail())
                                <div class="p-3 rounded-3 mb-3 border-start border-3 border-danger"
                                    style="background-color: var(--color-hover-bg);">
                                    <p class="text-dark small mb-0">
                                        Tu correo <strong>{{ auth()->user()->email }}</strong> aún no ha sido autenticado.
                                        Algunas funciones avanzadas permanecerán restringidas hasta completar este paso.
                                    </p>
                                </div>
                            @else
                                <div class="p-3 rounded-3 mb-3 border-start border-3 border-success bg-light">
                                    <p class="text-dark small mb-0">
                                        Tu correo <strong>{{ auth()->user()->email }}</strong> se encuentra verificado
                                        y vinculado a tu expediente universitario.
                                    </p>
                                </div>
                            @endif

                            <div class="mt-auto pt-3">
                                @if (auth()->user()->hasVerifiedEmail())
                                    <button type="button"
                                            class="btn btn-light text-muted w-100 py-2 fw-semibold border"
                                            disabled>
                                        <i class="bi bi-check-all me-1 text-success"></i> Identidad confirmada
                                    </button>
                                @else
                                    <button type="button"
                                            class="btn btn-outline-danger w-100 py-2 fw-semibold mt-auto"
                                            data-bs-toggle="modal"
                                            data-bs-target="#confirmEmailVerificationModal">
                                        <i class="bi bi-send-check me-1"></i> Enviar enlace de verificación
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tarjeta 5: Cerrar Sesión (corregida) -->
                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
                        <div class="card-body p-4 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                                    style="width: 48px; height: 48px; background-color: var(--color-hover-bg);">
                                    <i class="bi bi-box-arrow-right fs-4 text-danger"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-0 text-dark">Cerrar Sesión</h5>
                                    <p class="text-secondary small mb-0">
                                        Finalizá tu sesión de forma segura en este dispositivo.
                                    </p>
                                </div>
                            </div>

                            <button type="button"
                                    class="btn btn-danger px-4 py-2 fw-semibold shadow-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#confirmLogoutModal">
                                <i class="bi bi-box-arrow-right me-1"></i> Cerrar sesión
                            </button>
                        </div>
                    </div>
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

<!-- Modal 4: Verificación de correo electrónico -->
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

@endsection


@push('scripts')
{{-- Script de validación de rostro (solo si es alumno) --}}
@if($alumno)
<script src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api/dist/face-api.js"></script>
<script>
(function() {
    const input      = document.getElementById('foto_carnet');
    const preview    = document.getElementById('preview-img');
    const container  = document.getElementById('preview-container');
    const status     = document.getElementById('rostro-status');
    const hidden     = document.getElementById('rostro_detectado');
    const btnSubmit  = document.getElementById('btn-subir-carnet');
    const form       = document.getElementById('form-foto-carnet');

    if (!input || !form) return;

    let modelosCargados = false;

    // Cargar modelos de detección de rostro (lazy: solo cuando el usuario interactúa)
    async function cargarModelos() {
        if (modelosCargados) return true;

        try {
            await faceapi.nets.tinyFaceDetector.loadFromUri(
                'https://cdn.jsdelivr.net/npm/@vladmandic/face-api@latest/model'
            );
            modelosCargados = true;
            return true;
        } catch (e) {
            console.error('No se pudieron cargar los modelos de face-api', e);
            return false;
        }
    }

    input.addEventListener('change', async () => {
        const file = input.files[0];

        if (!file) {
            container.classList.add('d-none');
            btnSubmit.disabled = true;
            hidden.value = '0';
            return;
        }

        // Mostrar preview
        const reader = new FileReader();
        reader.onload = async (e) => {
            preview.src = e.target.result;
            container.classList.remove('d-none');

            status.textContent = 'Cargando validador...';
            status.className = 'mt-2 small text-muted';
            btnSubmit.disabled = true;

            const ok = await cargarModelos();

            if (!ok) {
                // Fallback: no se pudo cargar el modelo
                status.innerHTML = '<i class="bi bi-exclamation-triangle-fill text-warning me-1"></i> No se pudo cargar el validador automático. Podés enviar la foto igual, será revisada.';
                status.className = 'mt-2 small text-warning';
                hidden.value = '1';
                btnSubmit.disabled = false;
                return;
            }

            status.textContent = 'Analizando imagen...';
            status.className = 'mt-2 small text-muted';

            try {
                const img = await faceapi.bufferToImage(file);
                const deteccion = await faceapi.detectSingleFace(
                    img,
                    new faceapi.TinyFaceDetectorOptions()
                );

                if (deteccion) {
                    status.innerHTML = '<i class="bi bi-check-circle-fill text-success me-1"></i> Rostro detectado. La foto es válida.';
                    status.className = 'mt-2 small text-success';
                    hidden.value = '1';
                    btnSubmit.disabled = false;
                } else {
                    status.innerHTML = '<i class="bi bi-x-circle-fill text-danger me-1"></i> No se detectó un rostro humano. Probá con otra foto más clara y de frente.';
                    status.className = 'mt-2 small text-danger';
                    hidden.value = '0';
                    btnSubmit.disabled = true;
                }
            } catch (err) {
                console.error(err);
                status.innerHTML = '<i class="bi bi-x-circle-fill text-danger me-1"></i> Error al analizar la imagen.';
                status.className = 'mt-2 small text-danger';
                hidden.value = '0';
                btnSubmit.disabled = true;
            }
        };
        reader.readAsDataURL(file);
    });
})();
</script>
@endif

{{-- Logout automático después de cambio de contraseña --}}
@if (session('auto_logout'))
<script>
    document.addEventListener('DOMContentLoaded', () => {
        let secondsLeft = 5;
        const interval = setInterval(() => {
            secondsLeft--;
            if (secondsLeft <= 0) {
                clearInterval(interval);

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
