<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmar Contraseña</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-light d-flex align-items-center vh-100">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-warning text-dark text-center py-3">
                        <h5 class="mb-0">Área Segura</h5>
                    </div>
                    <div class="card-body p-4">
                        <p class="text-muted small">Por favor confirma tu contraseña actual para continuar con esta acción de seguridad.</p>

                        @if ($errors->any())
                            <div class="alert alert-danger small">{{ $errors->first() }}</div>
                        @endif

                        <form method="POST" action="{{ route('password.confirm') }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Contraseña</label>
                                <input type="password" name="password" class="form-control" required autofocus autocomplete="current-password">
                            </div>
                            <button type="submit" class="btn btn-dark w-100">Confirmar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>