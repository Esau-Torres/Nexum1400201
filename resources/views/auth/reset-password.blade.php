<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Contraseña</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-light d-flex align-items-center vh-100">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white text-center py-3">
                        <h5 class="mb-0">Restablecer Contraseña</h5>
                    </div>
                    <div class="card-body p-4">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0 small">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('password.update') }}">
                            @csrf
                            <input type="hidden" name="token" value="{{ $request->route('token') }}">

                            <div class="mb-3">
                                <label class="form-label">Correo Electrónico</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email', $request->email) }}" required autofocus readonly>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Nueva Contraseña</label>
                                <input type="password" name="password" class="form-control" required autocomplete="new-password">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Confirmar Nueva Contraseña</label>
                                <input type="password" name="password_confirmation" class="form-control" required autocomplete="new-password">
                            </div>

                            <button type="submit" class="btn btn-primary w-100">Guardar Contraseña</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>