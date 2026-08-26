<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesion</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="d-flex align-items-center justify-content-center vh-100">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="neu-card p-4">
                    <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRo9ixWEiUoSwggBueXWyS8CzRCQTZ8NkFPSdxbcfJjlg&s" alt="logo-uma" class="img-fluid d-flex mx-auto mb-4" style="max-width: 100px; height: 100px;">
                    <h5 class="text-center mb-5 fw-bold">Acceso al Sistema</h5>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-medium">Correo Electrónico</label>
                            <input type="email" name="email" class="form-control neu-input" value="{{ old('email') }}" placeholder="tu@email.com" required autofocus>
                        </div>
                        <div class="mb-3 mt-3">
                            <label class="form-label fw-medium">Contraseña</label>
                            <div class="input-group">
                                <input type="password" name="password" id="password" class="form-control neu-input" placeholder="••••••••" required>
                                <button class="btn neu-btn" type="button" id="togglePassword">
                                    <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                                        <path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0"/>
                                        <path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8m8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2 gap-md-3 mt-5">
                            <button onclick="window.history.back()" class="btn neu-btn w-50">Volver</button>
                            <button type="submit" class="btn neu-btn-accent w-50">Entrar</button>
                        </div>
                    </form>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ route('password.request') }}" class="text-decoration-none small" style="color: var(--color-accent);">¿Olvidaste tu contraseña?</a>
                        <a href="{{ route('register') }}" class="text-decoration-none small" style="color: var(--color-accent);">Registrarse</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.getElementById('togglePassword').addEventListener('click', function () {
            const input = document.getElementById('password');
            const icon = document.getElementById('eyeIcon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.innerHTML = '<path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M8 5.5a2.5 2.5 0 1 1 0 5 2.5 2.5 0 0 1 0-5M9 8a1 1 0 1 1-2 0 1 1 0 0 1 2 0"/>';
            } else {
                input.type = 'password';
                icon.innerHTML = '<path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0"/><path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8m8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7"/>';
            }
        });
    </script>
</body>
</html>