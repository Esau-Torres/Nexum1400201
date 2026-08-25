<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistema Modular</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-light">

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('home') }}">Nexum Core</a>
            <div class="d-flex align-items-center gap-3">
                <span class="text-white-50 small">{{ auth()->user()->email }}</span>
                <form method="POST" action="{{ route('logout') }}" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger btn-sm">Cerrar Sesión</button>
                </form>
            </div>
        </div>
    </nav>

    <!-- Contenido Principal -->
    <main class="container my-4">

        <!-- Mensajes de estado -->
        @if (session('status') == 'two-factor-authentication-enabled')
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Autenticación en dos pasos habilitada. Escanea el código QR a continuación para configurarla en tu aplicación.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @elseif (session('status') == 'two-factor-authentication-disabled')
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                Autenticación en dos pasos deshabilitada.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @elseif (session('status') == 'two-factor-authentication-confirmed')
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Código 2FA confirmado correctamente. La cuenta ahora está protegida.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row g-4">
            <!-- Columna Izquierda: Información de Usuario y Panel Tiempo Real -->
            <div class="col-lg-6">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 text-primary">Bienvenido, {{ auth()->user()->name }}</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Has iniciado sesión correctamente en el sistema modular.</p>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span class="fw-semibold">ID de Usuario:</span>
                                <span class="font-monospace">{{ auth()->user()->id }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span class="fw-semibold">Estado 2FA:</span>
                                @if (auth()->user()->two_factor_secret)
                                    <span class="badge bg-success">Activo</span>
                                @else
                                    <span class="badge bg-secondary">Inactivo</span>
                                @endif
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Tarjeta Reactiva con Alpine.js -->
                <div class="card shadow-sm border-0" x-data="{ online: true, contador: 0 }">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Canal de Monitoreo Local</h6>
                        <span class="badge" :class="online ? 'bg-success' : 'bg-danger'" x-text="online ? 'Conectado' : 'Desconectado'"></span>
                    </div>
                    <div class="card-body text-center py-4">
                        <p class="text-muted mb-2">Eventos procesados en memoria:</p>
                        <h2 class="display-6 fw-bold text-dark" x-text="contador"></h2>
                        <button class="btn btn-outline-primary btn-sm mt-2" @click="contador++">Simular Evento</button>
                    </div>
                </div>
            </div>

            <!-- Columna Derecha: Configuración de Seguridad 2FA -->
            <div class="col-lg-6">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 text-dark">Seguridad de la Cuenta (2FA)</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small">
                            Añade una capa adicional de seguridad a tu cuenta utilizando una aplicación autenticadora (Google Authenticator, Authy).
                        </p>

                        @if (! auth()->user()->two_factor_secret)
                            {{-- Formulario para activar 2FA --}}
                            <form method="POST" action="{{ url('/user/two-factor-authentication') }}">
                                @csrf
                                <button type="submit" class="btn btn-primary w-100">
                                    Habilitar 2FA
                                </button>
                            </form>
                        @else
                            {{-- Si el usuario ya lo activó pero necesita confirmarlo --}}
                            @if (! auth()->user()->two_factor_confirmed_at)
                                <div class="text-center my-3 p-3 bg-light rounded border">
                                    <p class="fw-semibold small mb-2">1. Escanea el código QR con tu app:</p>
                                    <div class="d-inline-block bg-white p-2 border rounded">
                                        {!! auth()->user()->twoFactorQrCodeSvg() !!}
                                    </div>

                                    <form method="POST" action="{{ url('/user/confirmed-two-factor-authentication') }}" class="mt-3">
                                        @csrf
                                        <p class="fw-semibold small mb-2">2. Ingresa el código de 6 dígitos:</p>
                                        <div class="input-group mb-2 mx-auto" style="max-width: 250px;">
                                            <input type="text" name="code" class="form-control text-center font-monospace" placeholder="123456" required autofocus>
                                            <button type="submit" class="btn btn-success">Confirmar</button>
                                        </div>
                                    </form>
                                </div>
                            @endif

                            {{-- Códigos de recuperación --}}
                            <div class="my-3">
                                <h6 class="fw-semibold small text-uppercase text-muted">Códigos de Recuperación</h6>
                                <p class="small text-muted mb-2">Guarda estos códigos en un lugar seguro por si pierdes acceso a tu autenticador.</p>
                                <div class="p-3 bg-light rounded border font-monospace small" style="max-height: 120px; overflow-y: auto;">
                                    @foreach (json_decode(decrypt(auth()->user()->two_factor_recovery_codes), true) as $code)
                                        <div>{{ $code }}</div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Deshabilitar 2FA --}}
                            <form method="POST" action="{{ url('/user/two-factor-authentication') }}" class="mt-4">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger w-100">
                                    Deshabilitar 2FA
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </main>

</body>
</html>