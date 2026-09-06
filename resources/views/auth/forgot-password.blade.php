<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Recuperar Contraseña - {{ config('app.name', 'NEXUM') }}</title>

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
            <a href="{{ route('welcome') }}" class="text-decoration-none fw-bold text-dark" style="font-size: 1.1rem;">{{ config('app.name', 'NEXUM') }}</a>
            <a href="{{ route('welcome') }}" class="text-decoration-none text-muted small">Volver al inicio</a>
        </nav>
    </header>

    <!-- Contenido Principal -->
    <main class="container-lg flex-grow-1 d-flex flex-column align-items-center justify-content-center py-5">
        <div class="row justify-content-center w-100">
            <div class="col-12 col-md-6 col-lg-5 col-xl-4">

                <div class="text-center mb-4">
                    <h1 class="h3 fw-bold mb-3">Recuperar Contraseña</h1>
                    <div class="mx-auto rounded" style="width: 40px; height: 2px; background-color: #dc3545;"></div>
                </div>

                <div class="card border-0 shadow rounded-4">
                    <div class="card-body p-4 p-lg-5">
                        <p class="text-muted small mb-4">Indica tu correo electrónico y te enviaremos un enlace para restablecer tu contraseña.</p>

                        @if (session('status'))
                        <div class="alert alert-success small">
                            {{ session('status') }}
                        </div>
                        @endif

                        @if ($errors->any())
                        <div class="alert alert-danger small">
                            {{ $errors->first() }}
                        </div>
                        @endif

                        <form method="POST" action="{{ route('password.email') }}">
                            @csrf
                            <div class="mb-4">
                                <label class="form-label fw-medium small">Correo Electrónico</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="tu@email.com" required autofocus>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-danger flex-fill">Enviar Enlace</button>
                            </div>
                        </form>

                        <div class="text-center mt-4">
                            <a href="{{ route('welcome') }}" class="text-decoration-none small text-danger">
                                <i class="fa-solid fa-arrow-left me-1"></i>Volver al inicio
                            </a>
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
</body>

</html>