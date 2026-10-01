<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Acerca de - {{ config('app.name', 'Nexum') }}</title>

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
            <a href="{{ url('/') }}" class="text-decoration-none fw-bold text-dark" style="font-size: 1.1rem;">{{ config('app.name', 'NEXUM') }}</a>
            <a href="{{ url('/') }}" class="text-decoration-none text-muted small">Volver al inicio</a>
        </nav>
    </header>

    <!-- Contenido Principal -->
    <main class="container-lg flex-grow-1 d-flex flex-column align-items-center justify-content-center py-5">
        <div class="row justify-content-center w-100">
            <div class="col-12 col-lg-8 col-xl-7">
                <div class="text-center mb-5">
                    <h1 class="display-5 fw-bold mb-3">Acerca de NEXUM</h1>
                    <div class="mx-auto rounded" style="width: 40px; height: 2px; background-color: #dc3545;"></div>
                </div>

                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4 p-lg-5">
                        <p class="fs-5 mb-4">
                            NEXUM es una plataforma integral diseñada para la gestión académica y administrativa de instituciones educativas. Su propósito es centralizar y simplificar los procesos clave, ofreciendo una experiencia moderna, segura y accesible para toda la comunidad universitaria.
                        </p>

                        <h5 class="fw-semibold mb-3">¿Qué ofrece NEXUM?</h5>
                        <ul class="list-unstyled mb-4">
                            <li class="d-flex align-items-start mb-2">
                                <i class="fa-solid fa-check text-danger mt-1 me-2"></i>
                                <span>Gestión centralizada de información académica y administrativa.</span>
                            </li>
                            <li class="d-flex align-items-start mb-2">
                                <i class="fa-solid fa-check text-danger mt-1 me-2"></i>
                                <span>Acceso personalizado según el rol de cada usuario.</span>
                            </li>
                            <li class="d-flex align-items-start mb-2">
                                <i class="fa-solid fa-check text-danger mt-1 me-2"></i>
                                <span>Consultas en tiempo real de calificaciones, horarios y registros.</span>
                            </li>
                            <li class="d-flex align-items-start mb-2">
                                <i class="fa-solid fa-check text-danger mt-1 me-2"></i>
                                <span>Administración eficiente de procesos financieros y de matrícula.</span>
                            </li>
                            <li class="d-flex align-items-start">
                                <i class="fa-solid fa-check text-danger mt-1 me-2"></i>
                                <span>Interfaz intuitiva y adaptada a diferentes dispositivos.</span>
                            </li>
                        </ul>

                        <p class="text-muted small mb-0">
                            NEXUM está en constante evolución para satisfacer las necesidades cambiantes de la educación moderna, facilitando la comunicación y el trabajo colaborativo entre estudiantes, docentes y personal administrativo.
                        </p>
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