<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Nexum')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="d-flex flex-column flex-lg-row min-vh-100">
        
        <!-- Sidebar Responsivo (Offcanvas en móvil, fijo en desktop) -->
        <div class="offcanvas-lg offcanvas-start neu-sidebar p-3 d-flex flex-column flex-shrink-0" tabindex="-1" id="sidebarMenu" aria-labelledby="sidebarMenuLabel">
            <div class="d-flex align-items-center justify-content-between mb-4 px-2">
                <div class="d-flex align-items-center gap-2">
                    <span class="p-2 rounded-circle neu-btn-accent d-inline-block" style="width: 24px; height: 24px;"></span>
                    <h5 class="fw-bold m-0" style="color: var(--color-accent);" id="sidebarMenuLabel">UMA SANTA ANA</h5>
                </div>
                <!-- Botón cerrar solo visible en móvil -->
                <button type="button" class="btn-close d-lg-none" data-bs-dismiss="offcanvas" data-bs-target="#sidebarMenu" aria-label="Close"></button>
            </div>

            <nav class="nav flex-column mb-auto">
                <a href="{{ route('home') }}" class="neu-nav-link {{ request()->routeIs('home') ? 'active' : '' }}">
                    <span>Inicio</span>
                </a>
                <a href="#" class="neu-nav-link">
                    <span>Modulos</span>
                </a>
                <a href="#" class="neu-nav-link">
                    <span>modulo</span>
                </a>
                <a href="{{ route('profile') }}" class="neu-nav-link {{ request()->routeIs('profile') ? 'active' : '' }}">
                    <span>setting</span>
                </a>
            </nav>

            <hr class="text-secondary opacity-25">

            <div class="px-2">
                <span class="badge neu-badge-accent px-3 py-2 w-100 text-center">
                    Modo Seguro 2FA
                </span>
            </div>
        </div>

        <!-- Contenedor Principal -->
        <div class="flex-grow-1 d-flex flex-column" style="min-width: 0;">
            <!-- Top Navbar -->
            <header class="neu-navbar py-3 px-3 px-md-4 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <!-- Botón Hamburguesa para Móvil -->
                    <button class="btn neu-btn d-lg-none p-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-5a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-5a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5z"/>
                        </svg>
                    </button>
                    <h6 class="m-0 medium d-sm-block fw-bold m-0" style="color: var(--color-accent);">@yield('titulo_navbar', 'Panel Principal')</h6>
                </div>

                <div class="d-flex align-items-center gap-2 gap-md-3">
                    <span class="fw-semibold small px-2 px-md-3 py-2 neu-card text-truncate" style="max-width: 180px;">
                        Usuario: <strong style="color: var(--color-accent);">{{ auth()->user()->name }}</strong>
                    </span>

                    <a href="{{ route('profile') }}" class="btn neu-btn btn-sm px-2 px-md-3 py-2 d-none d-sm-inline-block">
                        Configuración
                    </a>

                    <form method="POST" action="{{ route('logout') }}" class="m-0">
                        @csrf
                        <button type="submit" class="btn neu-btn-accent btn-sm px-2 px-md-3 py-2">
                            Cerrar Sesión
                        </button>
                    </form>
                </div>
            </header>

            <!-- Renderizado de Vistas Parciales -->
            <main class="p-3 p-md-4 flex-grow-1">
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>