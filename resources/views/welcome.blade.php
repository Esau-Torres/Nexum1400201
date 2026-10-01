<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Nexum') }}</title>

    <!-- Fuente Inter desde Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    <!-- Styles / Scripts Vite -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>

<body class="min-vh-100 d-flex flex-column p-3 p-lg-4">

    <!-- Encabezado -->
    <header class="container-lg">
        <nav class="d-flex align-items-center justify-content-between py-2">
            <span class="fw-bold text-dark" style="font-size: 1.1rem;">{{ config('app.name', 'NEXUM') }}</span>
            <a href="{{ route('about') }}" class="text-decoration-none text-muted small">Acerca de</a>
        </nav>
    </header>

    <!-- Contenido Principal -->
    <main class="container-lg flex-grow-1 d-flex flex-column align-items-center justify-content-center py-5">
        <div class="row g-4 g-lg-5 w-100 justify-content-center align-items-center">
            <!-- Lado Izquierdo: Logo, Bienvenida y Descripción -->
            <div class="col-12 col-lg-5 text-center text-lg-start">
                <img src="{{ asset('assets/images/logo-uma-santa-ana.png') }}" alt="Logo Universidad UMA Santa Ana" class="mb-4" style="height: 80px; width: auto;">
                <h2 class="h3 fw-bold mb-3">Bienvenido</h2>
                <p class="fs-5 mb-3 text-dark">Estamos aquí para acompañarte en tu camino académico. Inicia sesión para acceder a todas las herramientas que necesitas.</p>
                <p class="text-muted small mb-4">Plataforma integral de gestión académica y administrativa</p>
                <div class="d-none d-lg-block rounded" style="width: 40px; height: 2px; background-color: #dc3545;"></div>
            </div>

            <!-- Lado Derecho: Formulario de Login -->
            <div class="col-12 col-lg-5">
                <div class="card border-0 shadow rounded-4">
                    <div class="card-body p-4 p-lg-5">
                        <h3 class="fw-bold mb-2">Iniciar Sesión</h3>
                        <p class="text-muted small mb-4">Acceso al Sistema</p>

                        @if ($errors->any())
                        <div class="alert alert-danger small">
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif

                        <form method="POST" action="{{ route('login') }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-medium small">Correo Electrónico</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="tu@email.com" required autofocus>
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-medium small">Contraseña</label>
                                <div class="input-group">
                                    <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required>
                                    <button class="btn btn-outline-secondary btn-sm" type="button" id="togglePassword">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-danger flex-fill">Entrar</button>
                            </div>
                        </form>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('password.request') }}" class="text-decoration-none small text-danger">¿Olvidaste tu contraseña?</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="container-lg text-center py-3 mt-4">
        <small class="text-muted">&copy; {{ date('Y') }} {{ config('app.name', 'NEXUM') }}. Todos los derechos reservados.</small>
    </footer>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Script para toggle de contraseña -->
    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const input = document.getElementById('password');
            const icon = this.querySelector('i');

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    </script>
</body>

</html>