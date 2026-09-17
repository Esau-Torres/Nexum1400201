<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Confirmación de Seguridad - NEXUM</title>

    <!-- Fuente Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS & Iconos -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    @vite(['resources/css/app.css', 'resources/css/toast.css', 'resources/js/app.js'])
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100 p-3" style="background-color: var(--bg-main, #f0f2f5);">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-10 col-md-7 col-lg-5 col-xl-4">
                
                <!-- Tarjeta Principal de Confirmación -->
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                    
                    <!-- Cabecera Institucional -->
                    <div class="text-center mb-4">
                        <img src="{{ asset('images/uma_santa_ana.png') }}" alt="Logo UMA" class="img-fluid mb-3" style="max-height: 52px;">
                        
                        <h4 class="fw-bold text-dark mb-1">Área Restringida</h4>
                        <p class="text-secondary small mb-0">Confirma tu identidad para autorizar esta operación sensible.</p>
                    </div>

                    <!-- Aviso Informativo -->
                    <div class="p-3 rounded-3 mb-4 border-start border-3" style="background-color: var(--color-special, #fff9e1); border-left-color: #d4a017 !important;">
                        <div class="d-flex align-items-start gap-2">
                            <i class="bi bi-exclamation-triangle-fill fs-5" style="color: #b78103;"></i>
                            <span class="small text-dark">
                                Estás ingresando a un módulo de configuración crítica. La sesión de seguridad caducará tras un período de inactividad.
                            </span>
                        </div>
                    </div>

                    <!-- Formulario de Validación Fortify -->
                    <form method="POST" action="{{ route('password.confirm') }}">
                        @csrf

                        <div class="mb-4">
                            <label for="password" class="form-label small fw-semibold text-dark">Contraseña Actual</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted">
                                    <i class="bi bi-key"></i>
                                </span>
                                <input type="password" 
                                       name="password" 
                                       id="password" 
                                       class="form-control border-start-0 ps-0 @error('password') is-invalid @enderror" 
                                       placeholder="••••••••" 
                                       required 
                                       autofocus 
                                       autocomplete="current-password">
                            </div>
                            @error('password')
                                <span class="text-danger small mt-1 d-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn text-white py-2 fw-semibold shadow-sm" style="background-color: var(--color-accent, #dc3545);">
                                <i class="bi bi-shield-check me-1"></i> Confirmar y Continuar
                            </button>

                            <a href="{{ url()->previous() }}" class="btn btn-outline-secondary py-2 fw-semibold">
                                Cancelar Operación
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Pie de Página -->
                <p class="text-center text-secondary small mt-4 mb-0">
                    Plataforma Académico-Administrativa &copy; {{ date('Y') }} NEXUM
                </p>

            </div>
        </div>
    </div>

    <!-- Inicialización del Contenedor de Toasts Globales -->
    <x-toast-container />

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>