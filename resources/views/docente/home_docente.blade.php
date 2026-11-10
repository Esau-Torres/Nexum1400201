@extends('layouts.app')

@section('title', 'Inicio - Docente | NEXUM')

@section('content')
<style>
    /* Refuerzo local del dashboard: garantiza colores planos y tipografía grande
       aun si el navegador conserva en caché una versión anterior de app.css. */
    .dashboard-wrapper .stat-mini-card {
        min-height: 180px;
        background-image: none !important;
    }

    .dashboard-wrapper .stat-mini-card.card-materias {
        background-color: #ffffff !important;
        background-image: none !important;
    }

    .dashboard-wrapper .stat-mini-card.card-estudiantes {
        background: #dc3545 !important;
        background-image: none !important;
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12) !important;
    }

    .dashboard-wrapper .stat-mini-card.card-estudiantes:hover {
        background: #e35d6a !important;
        background-image: none !important;
        box-shadow: 0 14px 28px rgba(0, 0, 0, 0.16) !important;
    }

    .dashboard-wrapper .stat-mini-card.card-clases {
        background: #0d9488 !important;
        background-image: none !important;
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12) !important;
    }

    .dashboard-wrapper .stat-mini-card.card-clases:hover {
        background: #14b8a6 !important;
        background-image: none !important;
        box-shadow: 0 14px 28px rgba(0, 0, 0, 0.16) !important;
    }

    .dashboard-wrapper .stat-mini-card.card-evaluaciones {
        background: #d97706 !important;
        background-image: none !important;
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12) !important;
    }

    .dashboard-wrapper .stat-mini-card.card-evaluaciones:hover {
        background: #f59e0b !important;
        background-image: none !important;
        box-shadow: 0 14px 28px rgba(0, 0, 0, 0.16) !important;
    }

    .dashboard-wrapper .stat-mini-number {
        font-size: 3.8rem !important;
        font-weight: 800;
        line-height: 0.9;
        letter-spacing: -2px;
    }

    .dashboard-wrapper .stat-mini-label {
        font-size: 1.15rem !important;
        line-height: 1.25;
    }

    .dashboard-wrapper .stat-mini-sub {
        font-size: 0.95rem !important;
        line-height: 1.2;
    }

    .dashboard-wrapper .icon-square-mini {
        backdrop-filter: none !important;
    }

    @media (max-width: 767.98px) {
        .dashboard-wrapper .stat-mini-card {
            min-height: 150px;
        }

        .dashboard-wrapper .stat-mini-number {
            font-size: 2.6rem !important;
        }

        .dashboard-wrapper .stat-mini-label {
            font-size: 0.98rem !important;
        }

        .dashboard-wrapper .stat-mini-sub {
            font-size: 0.82rem !important;
        }
    }
</style>
<div class="dashboard-wrapper">
    <div class="container-fluid p-3 p-lg-4">

        <!-- ENCABEZADO -->
        <div class="mx-auto w-100 mb-4" style="max-width: 1200px;">
            <p class="text-muted small text-uppercase fw-semibold mb-1" style="letter-spacing: 1px;">Panel del Docente</p>
            <div class="d-flex flex-wrap justify-content-between align-items-end gap-2">
                <h1 class="h2 fw-bold mb-2" style="letter-spacing: -0.5px;">
                    Hola, {{ auth()->user()->name }}
                    <i class="fa-solid fa-hand-sparkles text-danger ms-2"></i>
                </h1>
                <div class="d-flex flex-wrap gap-2 mb-2">
                    <span class="badge rounded-pill px-3 py-2 bg-danger bg-opacity-10 text-danger fw-medium">
                        <i class="fa-solid fa-graduation-cap me-1"></i>{{ $cicloActual }}
                    </span>
                    <span class="badge rounded-pill px-3 py-2 bg-white text-muted fw-medium border">
                        <i class="fa-solid fa-calendar me-1"></i>{{ now()->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY') }}
                    </span>
                </div>
            </div>
            @if(session('success'))
            <div class="alert alert-success border-0 shadow-sm rounded-4 mt-3">
                <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
            </div>
            @endif
        </div>

        <!-- ==========================================
             FILA 1: 4 TARJETAS COMPACTAS EN UNA FILA
        =========================================== -->
        <div class="mx-auto w-100 mb-5" style="max-width: 1200px;">
            <div class="row g-3">

                <!-- Card 1: Materias (Blanca - CLICKEABLE) -->
                <div class="col-6 col-md-3">
                    <a href="{{ route('docente.materias') }}" class="text-decoration-none d-block h-100">
                        <div class="card border-0 shadow-sm rounded-4 h-100 stat-mini-card card-materias">
                            <div class="card-body d-flex justify-content-between align-items-center p-3">
                                <div class="d-flex flex-column">
                                    <div class="icon-square-mini bg-danger-soft text-danger mb-2">
                                        <i class="fa-solid fa-chalkboard-user"></i>
                                    </div>
                                    <div>
                                        <div class="stat-mini-label text-dark fw-semibold">Materias</div>
                                        <div class="stat-mini-sub text-muted">este ciclo</div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="stat-mini-number text-dark">{{ $totalMaterias }}</div>
                                    <div class="arrow-icon text-danger">
                                        <i class="fa-solid fa-arrow-right"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Card 2: Estudiantes (Roja) -->
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow rounded-4 h-100 stat-mini-card card-estudiantes">
                        <div class="card-body d-flex justify-content-between align-items-center p-3">
                            <div class="d-flex flex-column">
                                <div class="icon-square-mini icon-glass-white mb-2">
                                    <i class="fa-solid fa-users"></i>
                                </div>
                                <div>
                                    <div class="stat-mini-label text-white fw-semibold">Estudiantes</div>
                                    <div class="stat-mini-sub text-white-50">inscritos</div>
                                </div>
                            </div>
                            <div class="stat-mini-number text-white">{{ $totalEstudiantes }}</div>
                        </div>
                    </div>
                </div>

                <!-- Card 3: Clases (Teal elegante) -->
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow rounded-4 h-100 stat-mini-card card-clases">
                        <div class="card-body d-flex justify-content-between align-items-center p-3">
                            <div class="d-flex flex-column">
                                <div class="icon-square-mini icon-glass-white mb-2">
                                    <i class="fa-solid fa-calendar-week"></i>
                                </div>
                                <div>
                                    <div class="stat-mini-label text-white fw-semibold">Clases / semana</div>
                                    <div class="stat-mini-sub text-white-50">sesiones</div>
                                </div>
                            </div>
                            <div class="stat-mini-number text-white">{{ $clasesSemana }}</div>
                        </div>
                    </div>
                </div>

                <!-- Card 4: Evaluaciones (Amber cálido) -->
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm rounded-4 h-100 stat-mini-card card-evaluaciones">
                        <div class="card-body d-flex justify-content-between align-items-center p-3">
                            <div class="d-flex flex-column">
                                <div class="icon-square-mini icon-glass-dark mb-2">
                                    <i class="fa-solid fa-clipboard-check"></i>
                                </div>
                                <div>
                                    <div class="stat-mini-label text-dark fw-semibold">Evaluaciones</div>
                                    <div class="stat-mini-sub text-dark opacity-75">pendientes</div>
                                </div>
                            </div>
                            <div class="stat-mini-number text-dark">{{ $evaluacionesPendientes }}</div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- ==========================================
             FILA 2: CALENDARIO + PIZARRÓN
        =========================================== -->
        <div class="mx-auto w-100" style="max-width: 1200px;">
            <div class="row g-3">

                <!-- Calendario -->
                <div class="col-12 col-lg-7">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body p-3 p-md-4">

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="fw-bold text-dark mb-0">
                                    <i class="fa-solid fa-calendar-days me-2" style="color: var(--color-accent);"></i>Calendario
                                </h5>
                                <div class="d-flex align-items-center gap-2">
                                    <button type="button" class="btn btn-sm btn-light border rounded-3 px-2 py-1" id="calPrev" aria-label="Mes anterior">
                                        <i class="fa-solid fa-chevron-left small"></i>
                                    </button>
                                    <span class="fw-semibold small text-dark text-capitalize" id="calTitle">—</span>
                                    <button type="button" class="btn btn-sm btn-light border rounded-3 px-2 py-1" id="calNext" aria-label="Mes siguiente">
                                        <i class="fa-solid fa-chevron-right small"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="cal-grid" id="calGrid">
                                <div class="cal-dow">Lun</div>
                                <div class="cal-dow">Mar</div>
                                <div class="cal-dow">Mié</div>
                                <div class="cal-dow">Jue</div>
                                <div class="cal-dow">Vie</div>
                                <div class="cal-dow">Sáb</div>
                                <div class="cal-dow">Dom</div>
                                <!-- Días generados por JS -->
                            </div>

                            <div class="d-flex flex-wrap align-items-center gap-3 mt-3 pt-2 border-top">
                                <span class="cal-legend">
                                    <span class="cal-dot"></span> Día festivo
                                </span>
                                <span class="cal-legend">
                                    <span class="cal-today-dot"></span> Hoy
                                </span>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Pizarrón / Buzón de notificaciones -->
                <div class="col-12 col-lg-5">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body p-3 p-md-4 d-flex flex-column">

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="fw-bold text-dark mb-0">
                                    <i class="fa-solid fa-thumbtack me-2" style="color: var(--color-accent);"></i>Pizarrón de Notificaciones
                                </h5>
                                <span class="badge bg-light text-muted border px-2 py-1 small">0</span>
                            </div>

                            <!-- Contenido vacío -->
                            <div class="notices-empty flex-grow-1 d-flex flex-column align-items-center justify-content-center text-center py-4">
                                <div class="notices-empty-icon mb-3">
                                    <i class="fa-regular fa-clipboard"></i>
                                </div>
                                <h6 class="fw-semibold text-dark mb-1">Sin notificaciones por ahora</h6>
                                <p class="text-muted small mb-0" style="max-width: 260px;">
                                    Aquí aparecerán los avisos importantes, recordatorios y comunicados institucionales.
                                </p>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {

        // =========================================================
        // CALENDARIO
        // =========================================================
        const calendarEl = document.getElementById('calGrid');
        const titleEl = document.getElementById('calTitle');
        const prevBtn = document.getElementById('calPrev');
        const nextBtn = document.getElementById('calNext');

        if (!calendarEl || !titleEl) return;

        const MONTHS_ES = [
            'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
            'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
        ];

        // Días festivos fijos (formato: 'MM-DD'). Puedes agregar o editar aquí.
        // Para festivos variables (Semana Santa, etc.) habría que ampliar la lógica.
        const HOLIDAYS = {
            '01-01': 'Año Nuevo',
            '05-01': 'Día del Trabajo',
            '05-10': 'Día de la Madre',
            '06-17': 'Día del Padre',
            '08-06': 'Día del Divino Salvador del Mundo',
            '09-15': 'Día de la Independencia',
            '11-02': 'Día de los Difuntos',
            '11-05': 'Primer Grito de Independencia',
            '12-24': 'Nochebuena',
            '12-25': 'Navidad',
            '12-31': 'Fin de Año'
        };

        const today = new Date();
        let viewYear = today.getFullYear();
        let viewMonth = today.getMonth(); // 0-11

        function pad2(n) {
            return String(n).padStart(2, '0');
        }

        function renderCalendar(year, month) {
            // Limpiar días previos (mantener cabeceras de día de la semana)
            calendarEl.querySelectorAll('.cal-day').forEach(el => el.remove());

            titleEl.textContent = `${MONTHS_ES[month]} ${year}`;

            // Primer día del mes (0 = Domingo, 1 = Lunes, ... 6 = Sábado)
            const firstDay = new Date(year, month, 1).getDay();
            // Convertir a lunes como primer día: (0 domingo) => 6, resto => day-1
            const startOffset = (firstDay === 0) ? 6 : firstDay - 1;

            const daysInMonth = new Date(year, month + 1, 0).getDate();

            // Celdas vacías antes del primer día
            for (let i = 0; i < startOffset; i++) {
                const empty = document.createElement('div');
                empty.className = 'cal-day cal-day-empty';
                calendarEl.appendChild(empty);
            }

            // Días del mes
            for (let d = 1; d <= daysInMonth; d++) {
                const cell = document.createElement('div');
                cell.className = 'cal-day';

                const isToday =
                    d === today.getDate() &&
                    month === today.getMonth() &&
                    year === today.getFullYear();

                if (isToday) cell.classList.add('cal-day-today');

                const num = document.createElement('span');
                num.className = 'cal-day-num';
                num.textContent = d;
                cell.appendChild(num);

                const key = `${pad2(month + 1)}-${pad2(d)}`;
                if (HOLIDAYS[key]) {
                    cell.classList.add('cal-day-holiday');

                    const dot = document.createElement('span');
                    dot.className = 'cal-dot';
                    cell.appendChild(dot);

                    // Tooltip (burbuja) al hacer hover
                    const tooltip = document.createElement('span');
                    tooltip.className = 'cal-tooltip';
                    tooltip.textContent = HOLIDAYS[key];
                    cell.appendChild(tooltip);
                }

                calendarEl.appendChild(cell);
            }

            // Celdas vacías al final para completar la última semana
            const totalCells = startOffset + daysInMonth;
            const remainder = totalCells % 7;
            if (remainder !== 0) {
                for (let i = 0; i < (7 - remainder); i++) {
                    const empty = document.createElement('div');
                    empty.className = 'cal-day cal-day-empty';
                    calendarEl.appendChild(empty);
                }
            }
        }

        prevBtn && prevBtn.addEventListener('click', () => {
            viewMonth--;
            if (viewMonth < 0) {
                viewMonth = 11;
                viewYear--;
            }
            renderCalendar(viewYear, viewMonth);
        });

        nextBtn && nextBtn.addEventListener('click', () => {
            viewMonth++;
            if (viewMonth > 11) {
                viewMonth = 0;
                viewYear++;
            }
            renderCalendar(viewYear, viewMonth);
        });

        renderCalendar(viewYear, viewMonth);
    });
</script>
@endpush