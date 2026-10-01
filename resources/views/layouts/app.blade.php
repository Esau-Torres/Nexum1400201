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

    <!-- Font Awesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    <!-- Styles / Scripts Vite -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>

<body class="bg-light">

    <!-- Barra de cabecera fija solo en móvil/tablet para contener el botón sin alterar el flujo -->
    <div class="d-lg-none bg-white border-bottom p-2 px-3 d-flex align-items-center justify-content-between sticky-top shadow-sm" style="z-index: 1010;">
        <button class="btn btn-outline-secondary border-0 p-2"
                type="button"
                data-bs-toggle="offcanvas"
                data-bs-target="#sidebarMenu"
                aria-controls="sidebarMenu"
                aria-label="Abrir Menú">
            <i class="fa-solid fa-bars fs-5 text-dark"></i>
        </button>
        <span class="fw-bold small text-dark">@yield('titulo_navbar', 'NEXUM')</span>
        <img src="{{ asset('images/uma_santa_ana.png') }}" alt="UMA" style="height: 32px;">
    </div>

    <div class="layout-wrapper">
        <!-- Sidebar Responsivo -->
        <aside class="sidebar offcanvas-lg offcanvas-start bg-white border-end d-flex flex-column"
               tabindex="-1"
               id="sidebarMenu"
               aria-labelledby="sidebarMenuLabel"
               data-bs-scroll="true">

            <!-- Logo -->
            <div class="p-3 border-bottom d-flex align-items-center justify-content-between">
                <a href="{{ route('home') }}">
                    <img src="{{ asset('images/uma_santa_ana.png') }}" alt="Logo UMA" class="img-fluid" style="max-height: 48px;">
                </a>
                <button class="sidebar-close d-lg-none" id="sidebarClose" aria-label="Cerrar menú">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <!-- Navegación Principal -->
            <nav class="flex-grow-1 p-3">
                <ul class="nav flex-column">
                    <!-- Inicio (común para todos los roles) -->
                    <li class="nav-item mb-1">
                        <a href="{{ route('home') }}" class="nav-item-custom d-flex align-items-center text-decoration-none text-dark {{ request()->routeIs('home') ? 'active' : '' }}">
                            <i class="fa-solid fa-home nav-icon fs-5 me-3 text-muted"></i>
                            <div>
                                <div class="fw-medium">Inicio</div>
                                <div class="nav-text-secondary">Panel principal</div>
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
                            <a href="#" class="nav-item-custom d-flex align-items-center text-decoration-none text-dark {{ request()->is('enlaces*') ? 'active' : '' }}">
                                <i class="fa-solid fa-link nav-icon fs-5 me-3 text-muted"></i>
                                <div>
                                    <div class="fw-medium">Enlaces</div>
                                    <div class="nav-text-secondary">Cursos virtuales</div>
                                </div>
                            </a>
                        </li>
                        <li class="nav-item mb-1">
                            <a href="#" class="nav-item-custom d-flex align-items-center text-decoration-none text-dark {{ request()->is('record-academico*') ? 'active' : '' }}">
                                <i class="fa-solid fa-graduation-cap nav-icon fs-5 me-3 text-muted"></i>
                                <div>
                                    <div class="fw-medium">Record Académico</div>
                                    <div class="nav-text-secondary">Notas ciclo</div>
                                </div>
                            </a>
                        </li>

                        <li class="nav-item mb-1">
                            <a href="#" class="nav-item-custom d-flex align-items-center text-decoration-none text-dark {{ request()->is('inscripcion*') ? 'active' : '' }}">
                                <i class="fa-solid fa-pen-to-square nav-icon fs-5 me-3 text-muted"></i>
                                <div>
                                    <div class="fw-medium">Inscripción</div>
                                    <div class="nav-text-secondary">En línea</div>
                                </div>
                            </a>
                        </li>

                        <li class="nav-item mb-1">
                            <a href="#" class="nav-item-custom d-flex align-items-center text-decoration-none text-dark {{ request()->is('evaluacion*') ? 'active' : '' }}">
                                <i class="fa-solid fa-clipboard-check nav-icon fs-5 me-3 text-muted"></i>
                                <div>
                                    <div class="fw-medium">Evaluación del Desempeño</div>
                                    <div class="nav-text-secondary">Docente</div>
                                </div>
                            </a>
                        </li>
                        <li class="nav-item mb-1">
                            <a href="#" class="nav-item-custom d-flex align-items-center text-decoration-none text-dark {{ request()->is('record-financiero*') ? 'active' : '' }}">
                                <i class="fa-solid fa-file-invoice-dollar nav-icon fs-5 me-3 text-muted"></i>
                                <div>
                                    <div class="fw-medium">Record Financiero</div>
                                    <div class="nav-text-secondary">Credenciales de pago</div>
                                </div>
                            </a>
                        </li>
                        <li class="nav-item mb-1">
                            <a href="#" class="nav-item-custom d-flex align-items-center text-decoration-none text-dark {{ request()->is('buzon*') ? 'active' : '' }}">
                                <i class="fa-solid fa-envelope nav-icon fs-5 me-3 text-muted"></i>
                                <div>
                                    <div class="fw-medium">Buzón</div>
                                    <div class="nav-text-secondary">Observaciones, sugerencias y quejas</div>
                                </div>
                            </a>
                        </li>
                        <li class="nav-item mb-1">
                            <a href="#" class="nav-item-custom d-flex align-items-center text-decoration-none text-dark {{ request()->is('recursos*') ? 'active' : '' }}">
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
                            <a href="{{ route('superadmin.roles.manageroles') }}" class="nav-item-custom d-flex align-items-center text-decoration-none text-dark {{ request()->is('superadmin/roles.manageroles*') ? 'active' : '' }}">
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

                    @if(auth()->user()->hasrole('ADMIN_FINANCIERO'))
                        <li class="nav-item mb-1">
                            <a href="{{ route('adfinanciero.conceptospagos.index') }}" class="nav-item-custom d-flex align-items-center text-decoration-none text-dark {{ request()->routeIs('adfinanciero.conceptospagos.*') ? 'active' : '' }}">
                                <i class="fa-solid fa-tags nav-icon fs-5 me-3 text-muted"></i>
                                <div>
                                    <div class="fw-medium">Aranceles</div>
                                    <div class="nav-text-secondary">Gestión de aranceles</div>
                                </div>
                            </a>
                        </li>

                        <li class="nav-item mb-1">
                            <a href="{{ route('reglas.index') }}" class="nav-item-custom d-flex align-items-center text-decoration-none text-dark {{ request()->routeIs('reglas.index') ? 'active' : '' }}">
                                <i class="fa-solid fa-scale-balanced nav-icon fs-5 me-3 text-muted"></i>
                                <div>
                                    <div class="fw-medium">Reglas de Cobro</div>
                                    <div class="nav-text-secondary">Políticas institucionales</div>
                                </div>
                            </a>
                        </li>

                        <li class="nav-item mb-1">
                            <!-- Usamos adfinanciero.promociones.* para unificar el método -->
                            <a href="{{ route('adfinanciero.promociones.index') }}" class="nav-item-custom d-flex align-items-center text-decoration-none text-dark {{ request()->routeIs('adfinanciero.promociones.*') ? 'active' : '' }}">
                                <i class="fa-solid fa-percent nav-icon fs-5 me-3 text-muted"></i>
                                <div>
                                    <div class="fw-medium">Promociones</div>
                                    <div class="nav-text-secondary">Descuentos especiales</div>
                                </div>
                            </a>
                        </li>
                    @endif

                    @if(auth()->user()->hasrole('DOCENTE'))
                        <li class="nav-item mb-1">
                            <a href="#" class="nav-item-custom d-flex align-items-center text-decoration-none text-dark @if(request()->is('materias*')) active @endif">
                                <i class="fa-solid fa-chalkboard-user nav-icon fs-5 me-3 text-muted"></i>
                                <div>
                                    <div class="fw-medium">Materias</div>
                                    <div class="nav-text-secondary">Cursos asignados</div>
                                </div>
                            </a>
                        </li>

                        <li class="nav-item mb-1">
                            <a href="#" class="nav-item-custom d-flex align-items-center text-decoration-none text-dark @if(request()->is('asistencia*')) active @endif">
                                <i class="fa-solid fa-calendar-check nav-icon fs-5 me-3 text-muted"></i>
                                <div>
                                    <div class="fw-medium">Asistencia</div>
                                    <div class="nav-text-secondary">Control de clases</div>
                                </div>
                            </a>
                        </li>

                        <li class="nav-item mb-1">
                            <a href="{{ route('prueba.notas') }}" class="nav-item-custom d-flex align-items-center text-decoration-none text-dark {{ request()->routeIs('prueba.notas') ? 'active' : '' }}">
                                <i class="fa-solid fa-file-pen nav-icon fs-5 me-3 text-muted"></i>
                                <div>
                                    <div class="fw-medium">Prueba de Notas</div>
                                    <div class="nav-text-secondary">Evaluaciones y calificaciones</div>
                                </div>
                            </a>
                        </li>
                    @endif

                    <!-- ============================================== -->
                    <!-- MENÚ PARA CAJERO  aqui va la autenticacion del usuario de CAJERO-->
                    <!-- ============================================== -->
                    @if(auth()->user()->hasrole('CAJERO'))
                        <li class="nav-item mb-1">
                            <a href="{{ route('cajero.deuda.index') }}" class="nav-item-custom d-flex align-items-center text-decoration-none text-dark {{ request()->is('cajero/deuda*') ? 'active' : '' }}">
                                <i class="fa-solid fa-file-invoice-dollar nav-icon fs-5 me-3 text-muted"></i>
                                <div>
                                    <div class="fw-medium">Deuda Pendiente</div>
                                    <div class="nav-text-secondary">Liquidar cargos del alumno</div>
                                </div>
                            </a>
                        </li>

                        <!-- 2. Módulo: concepto_pago -->
                        <li class="nav-item mb-1">
                            <!-- Aquí usamos request()->is('cajero/ventanilla*') que lee la URL real -->
                            <a href="{{ route('cajero.ventanilla.index') }}" class="nav-item-custom d-flex align-items-center text-decoration-none text-dark {{ request()->is('*ventanilla*') ? 'active' : '' }}">
                                <i class="fa-solid fa-cash-register nav-icon fs-5 me-3 text-muted"></i>
                                <div>
                                    <div class="fw-medium">Consultas en ventanilla</div>
                                    <div class="nav-text-secondary">Consulta de aranceles directos</div>
                                </div>
                            </a>
                        </li>

                        <!-- 3. Módulo: promociones -->
                        <li class="nav-item mb-1">
                            <a href="{{ route('cajero.promociones.index') }}" class="nav-item-custom d-flex align-items-center text-decoration-none text-dark {{ request()->is('*promociones*') ? 'active' : '' }}">
                                <i class="fa-solid fa-tags nav-icon fs-5 me-3 text-muted"></i>
                                <div>
                                    <div class="fw-medium">Promociones</div>
                                    <div class="nav-text-secondary">Consulta de beneficios activos</div>
                                </div>
                            </a>
                        </li>

                        <!-- 4. Módulo: pagos -->
                        <li class="nav-item mb-1">
                            <a href="{{ route('cajero.pagos.create') }}" class="nav-item-custom d-flex align-items-center text-decoration-none text-dark {{ request()->is('*pagos*') ? 'active' : '' }}">
                                <i class="fa-solid fa-file-invoice-dollar nav-icon fs-5 me-3 text-muted"></i>
                                <div>
                                    <div class="fw-medium">Cobrar</div>
                                    <div class="nav-text-secondary">Registro de pagos y transacciones</div>
                                </div>
                            </a>
                        </li>
                    @endif
                    <!-- aqui va el end cuandos termine todo -->
                </ul>
            </nav>

            <!-- Sección Inferior -->
            <div class="p-3 border-top">
                <a href="{{ route('profile') }}" class="nav-item-custom d-flex align-items-center text-decoration-none text-dark mb-1 {{ request()->routeIs('profile') ? 'active' : '' }}">
                    <i class="fa-solid fa-gear nav-icon fs-5 me-3 text-muted"></i>
                    <div class="fw-medium">Ajustes</div>
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

    <!-- Script del Sidebar Mobile -->
    <script>
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebarClose = document.getElementById('sidebarClose');
        const sidebarBackdrop = document.getElementById('sidebarBackdrop');

        function openSidebar() {
            sidebar.classList.add('sidebar-open');
            sidebarBackdrop.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeSidebar() {
            sidebar.classList.remove('sidebar-open');
            sidebarBackdrop.classList.remove('active');
            document.body.style.overflow = '';
        }

        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', openSidebar);
        }
        if (sidebarClose) {
            sidebarClose.addEventListener('click', closeSidebar);
        }
        if (sidebarBackdrop) {
            sidebarBackdrop.addEventListener('click', closeSidebar);
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && sidebar.classList.contains('sidebar-open')) {
                closeSidebar();
            }
        });
    </script>
</body>

</html>
