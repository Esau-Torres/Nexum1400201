<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Bootstrap 5 CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

        <!-- Styles / Scripts Vite -->
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="bg-light text-dark min-vh-100 d-flex flex-column justify-content-between p-3 p-lg-4">

        <!-- Encabezado de Navegación -->
        <header class="container-lg">
            @if (Route::has('login'))
                <nav class="d-flex align-items-center justify-content-end gap-2 py-2">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="btn btn-outline-secondary btn-sm px-3">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-link btn-sm text-decoration-none text-dark px-3">
                            Log in
                        </a>

                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="btn btn-outline-secondary btn-sm px-3">
                                Register
                            </a>
                        @endif
                    @endauth
                </nav>
            @endif
        </header>

        <!-- Contenedor Principal Central -->
        <main class="container-lg d-flex flex-column align-items-center justify-content-center text-center my-auto">
            <h1 class="fw-bold text-dark">{{ config('app.name', 'NEXUM') }}</h1>
            <p class="text-secondary small">Sistema Integral de Gestión Académico-Administrativa</p>
        </main>

        <!-- Espaciador / Pie inferior -->
        <footer class="container-lg text-center py-2">
            <small class="text-muted">&copy; {{ date('Y') }} {{ config('app.name', 'NEXUM') }}. Todos los derechos reservados.</small>
        </footer>

        <!-- Bootstrap 5 JS Bundle -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>