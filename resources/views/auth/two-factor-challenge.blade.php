<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Verificación 2FA</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-light d-flex align-items-center vh-100">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card shadow-sm">
                    <div class="card-header bg-dark text-white text-center">
                        <h5 class="mb-0">Autenticación en Dos Pasos</h5>
                    </div>
                    <div class="card-body p-4">
                        <p class="text-muted small">Ingresa el código de 6 dígitos generado por tu aplicación autenticadora.</p>
                        @if ($errors->any())
                            <div class="alert alert-danger">{{ $errors->first() }}</div>
                        @endif
                        <form method="POST" action="{{ url('/two-factor-challenge') }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Código de Autenticación</label>
                                <input type="text" name="code" class="form-control text-center fs-4" autofocus autocomplete="one-time-code">
                            </div>
                            <button type="submit" class="btn btn-dark w-100">Verificar Código</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>