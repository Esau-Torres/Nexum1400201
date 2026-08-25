<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificar Correo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-light d-flex align-items-center vh-100">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm border-0 text-center p-4">
                    <div class="card-body">
                        <h4 class="card-title mb-3">Verifica tu dirección de correo</h4>
                        <p class="text-muted">
                            Te enviamos un enlace de verificación. Por favor revisa tu bandeja de entrada antes de continuar.
                        </p>

                        @if (session('status') == 'verification-link-sent')
                            <div class="alert alert-success small my-3">
                                Se ha enviado un nuevo enlace de verificación a tu correo.
                            </div>
                        @endif

                        <div class="d-flex justify-content-center gap-3 mt-4">
                            <form method="POST" action="{{ route('verification.send') }}">
                                @csrf
                                <button type="submit" class="btn btn-primary">Reenviar Correo</button>
                            </form>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="btn btn-outline-secondary">Cerrar Sesión</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>