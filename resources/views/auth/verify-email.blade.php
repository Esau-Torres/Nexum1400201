<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Verificación de Correo</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    @vite(['resources/css/app.css','resources/css/toast.css' , 'resources/js/app.js'])
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100 p-3" style="background-color: var(--bg-main, #f0f2f5);">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-10 col-md-7 col-lg-5 col-xl-4">
                
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white text-center">
                    
                    <!-- Isotipo Institucional -->
                    <img src="{{ asset('images/uma_santa_ana.png') }}" alt="Logo UMA" class="img-fluid mx-auto mb-3" style="max-height: 52px;">
                    
                    <div class="d-inline-flex p-3 rounded-circle mx-auto mb-3" style="background-color: var(--color-hover-bg, #fff5f5);">
                        <i class="bi bi-envelope-check fs-2" style="color: var(--color-accent, #dc3545);"></i>
                    </div>

                    <h4 class="fw-bold text-dark mb-1">Verificación Requerida</h4>
                    <p class="text-secondary small mb-4">
                        Por motivos de seguridad institucional, debes validar tu dirección de correo electrónico institucional antes de acceder a la plataforma NEXUM.
                    </p>

                    <div class="p-3 rounded-3 mb-4 text-start border-start border-3" style="background-color: var(--color-special, #fff9e1); border-left-color: #d4a017 !important;">
                        <div class="d-flex align-items-start gap-2">
                            <i class="bi bi-info-circle-fill text-warning fs-5 flex-shrink-0"></i>
                            <span class="small text-dark">
                                Hemos enviado un enlace de confirmación a <strong>{{ auth()->user()->email }}</strong>. Si no lo has recibido, revisa tu carpeta de spam o solicita un nuevo envío.
                            </span>
                        </div>
                    </div>

                    <!-- Reenvío de Enlace de Verificación -->
                    <form method="POST" action="{{ route('verification.send') }}" class="mb-3">
                        @csrf
                        <button type="submit" class="btn text-white w-100 py-2 fw-semibold shadow-sm" style="background-color: var(--color-accent, #dc3545);">
                            <i class="bi bi-send me-1"></i> Reenviar Correo de Verificación
                        </button>
                    </form>

                    <!-- Cerrar Sesión -->
                    <form method="POST" action="{{ route('logout') }}" class="m-0">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary w-100 py-2 fw-semibold">
                            <i class="bi bi-box-arrow-right me-1"></i> Salir del Sistema
                        </button>
                    </form>

                </div>

                <p class="text-center text-secondary small mt-4 mb-0">
                    Plataforma Académico-Administrativa &copy; {{ date('Y') }} NEXUM
                </p>

            </div>
        </div>
    </div>

    <!-- Integración del contenedor global de Toasts -->
    <x-toast-container />

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>