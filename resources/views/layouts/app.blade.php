<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', config('app.name', 'NEXUM'))</title>

    <!-- Fuente Inter desde Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    <!-- Font Awesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    <!-- Styles / Scripts Vite -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>

<body class="bg-light">

    <!-- Botón Toggle (Solo Móvil) -->
    <button class="sidebar-toggle" id="sidebarToggle" aria-label="Abrir menú">
        <i class="fa-solid fa-bars"></i>
    </button>

    <!-- Backdrop (Solo Móvil) -->
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    <div class="d-flex">
        <!-- Sidebar -->
        <aside class="sidebar bg-white border-end d-flex flex-column" id="sidebar">
            <!-- Header del Sidebar con botón de cerrar (solo móvil) -->
            <div class="sidebar-header p-3 border-bottom d-flex align-items-center justify-content-between">
                <a href="{{ route('home') }}" class="d-inline-block text-decoration-none">
                    <img src="{{ asset('assets/images/logo-uma-santa-ana.png') }}"
                        alt="Logo Universidad UMA Santa Ana"
                        class="img-fluid"
                        style="max-height: 50px; cursor: pointer;">
                </a>
                <button class="sidebar-close d-lg-none" id="sidebarClose" aria-label="Cerrar menú">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <!-- Navegación Principal -->
            <nav class="flex-grow-1 p-3">
                <ul class="nav flex-column">
                    <li class="nav-item mb-1">
                        <a href="{{ route('home') }}" class="nav-item-custom d-flex align-items-center text-decoration-none text-dark {{ request()->routeIs('home') ? 'active' : '' }}">
                            <i class="fa-solid fa-home nav-icon fs-5 me-3 text-muted"></i>
                            <div>
                                <div class="fw-medium">Inicio</div>
                                <div class="nav-text-secondary">Panel de rutas</div>
                            </div>
                        </a>
                    </li>
                @if(auth()->user()->hasrole('ESTUDIANTE'))
                    <li class="nav-item mb-1">
                        <a href="#" class="nav-item-custom d-flex align-items-center text-decoration-none text-dark {{ request()->is('pensum*') ? 'active' : '' }}">
                            <i class="fa-solid fa-book-open nav-icon fs-5 me-3 text-muted"></i>
                            <div>
                                <div class="fw-medium">Pensum</div>
                                <div class="nav-text-secondary">Plan académico</div>
                            </div>
                        </a>
                    </li>
                    <li class="nav-item mb-1">
                        <a href="#" class="nav-item-custom d-flex align-items-center text-decoration-none text-dark @if(request()->is('enlaces')) active @endif">
                            <i class="fa-solid fa-link nav-icon fs-5 me-3 text-muted"></i>
                            <div>
                                <div class="fw-medium">Enlaces</div>
                                <div class="nav-text-secondary">Cursos virtuales</div>
                            </div>
                        </a>
                    </li>

                    <li class="nav-item mb-1">
                        <a href="#" class="nav-item-custom d-flex align-items-center text-decoration-none text-dark @if(request()->is('record-academico')) active @endif">
                            <i class="fa-solid fa-graduation-cap nav-icon fs-5 me-3 text-muted"></i>
                            <div>
                                <div class="fw-medium">Record Académico</div>
                                <div class="nav-text-secondary">Notas ciclo</div>
                            </div>
                        </a>
                    </li>

                    <li class="nav-item mb-1">
                        <a href="#" class="nav-item-custom d-flex align-items-center text-decoration-none text-dark @if(request()->is('inscripcion')) active @endif">
                            <i class="fa-solid fa-pen-to-square nav-icon fs-5 me-3 text-muted"></i>
                            <div>
                                <div class="fw-medium">Inscripción</div>
                                <div class="nav-text-secondary">En línea</div>
                            </div>
                        </a>
                    </li>

                    <li class="nav-item mb-1">
                        <a href="#" class="nav-item-custom d-flex align-items-center text-decoration-none text-dark @if(request()->is('evaluacion')) active @endif">
                            <i class="fa-solid fa-clipboard-check nav-icon fs-5 me-3 text-muted"></i>
                            <div>
                                <div class="fw-medium">Evaluación del Desempeño</div>
                                <div class="nav-text-secondary">Docente</div>
                            </div>
                        </a>
                    </li>

                    <li class="nav-item mb-1">
                        <a href="#" class="nav-item-custom d-flex align-items-center text-decoration-none text-dark @if(request()->is('record-financiero')) active @endif">
                            <i class="fa-solid fa-file-invoice-dollar nav-icon fs-5 me-3 text-muted"></i>
                            <div>
                                <div class="fw-medium">Record Financiero</div>
                                <div class="nav-text-secondary">Credenciales de pago</div>
                            </div>
                        </a>
                    </li>

                    <li class="nav-item mb-1">
                        <a href="#" class="nav-item-custom d-flex align-items-center text-decoration-none text-dark @if(request()->is('buzon')) active @endif">
                            <i class="fa-solid fa-envelope nav-icon fs-5 me-3 text-muted"></i>
                            <div>
                                <div class="fw-medium">Buzón</div>
                                <div class="nav-text-secondary">Observaciones, sugerencias y quejas</div>
                            </div>
                        </a>
                    </li>

                    <li class="nav-item mb-1">
                        <a href="#" class="nav-item-custom d-flex align-items-center text-decoration-none text-dark @if(request()->is('recursos')) active @endif">
                            <i class="fa-solid fa-folder-open nav-icon fs-5 me-3 text-muted"></i>
                            <div>
                                <div class="fw-medium">Recursos</div>
                                <div class="nav-text-secondary">Bibliográficos</div>
                            </div>
                        </a>
                    </li>
                    @endif
                    @if(auth()->user()->hasrole('SUPER_ADMIN'))
                        <li class="nav-item mb-1">
                            <a href="{{ route('superadmin.createuser') }}" class="nav-item-custom d-flex align-items-center text-decoration-none text-dark {{ request()->is('superadmin/createuser*') ? 'active' : '' }}">
                                <i class="fa-solid fa-user nav-icon fs-5 me-3 text-muted"></i>
                                <div>
                                    <div class="fw-medium">Crear</div>
                                    <div class="nav-text-secondary">Ingreso de usuarios</div>
                                </div>
                            </a>
                        </li>
                        <li class="nav-item mb-1">
                            <a href="{{ route('superadmin.panel-administrativo') }}" class="nav-item-custom d-flex align-items-center text-decoration-none text-dark {{ request()->is('superadmin/panel-administrativo*') ? 'active' : '' }}">
                                <i class="fa-solid fa-gauge nav-icon fs-5 me-3 text-muted"></i>
                                <div>
                                    <div class="fw-medium">Panel Administrativo</div>
                                    <div class="nav-text-secondary">Administrar usuarios</div>
                                </div>
                            </a>
                        </li>
                        <li class="nav-item mb-1">
                            <a href="{{ route('superadmin.create-rol') }}" class="nav-item-custom d-flex align-items-center text-decoration-none text-dark {{ request()->is('superadmin/create-rol*') ? 'active' : '' }}">
                                <i class="fa-solid fa-sliders nav-icon fs-5 me-3 text-muted"></i>
                                <div>
                                    <div class="fw-medium">Gestion</div>
                                    <div class="nav-text-secondary">Administar roles de usuario</div>
                                </div>
                            </a>
                        </li>
                        <li class="nav-item mb-1">
                            <a href="{{ route('superadmin.manage-ruler') }}" class="nav-item-custom d-flex align-items-center text-decoration-none text-dark {{ request()->is('superadmin/manage-ruler*') ? 'active' : '' }}">
                                <i class="fa-solid fa-gavel nav-icon fs-5 me-3 text-muted"></i>
                                <div>
                                    <div class="fw-medium">Gestionar reglas</div>
                                    <div class="nav-text-secondary">reglas de academicas</div>
                                </div>
                            </a>
                        </li>
                    @endif
                </ul>
            </nav>

            <!-- Sección Inferior -->
            <div class="p-3 border-top">
                <a href="#" class="nav-item-custom d-flex align-items-center text-decoration-none text-dark mb-1">
                    <i class="fa-solid fa-gear nav-icon fs-5 me-3 text-muted"></i>
                    <div class="fw-medium">Ajustes</div>
                </a>
                <a href="{{ route('profile') }}" class="nav-item-custom d-flex align-items-center text-decoration-none text-dark @if(request()->is('profile')) active @endif">
                    <i class="fa-solid fa-user-circle nav-icon fs-5 me-3 text-muted"></i>
                    <div class="fw-medium">Perfil</div>
                </a>
            </div>
        </aside>

        <!-- Contenido Principal -->
        <main class="main-content flex-grow-1">
            @yield('content')
        </main>
    </div>
    <x-toast-container />
    @stack('scripts')
</body>

</html>