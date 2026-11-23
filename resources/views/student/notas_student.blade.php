@extends('layouts.app')

@section('title', 'Mis Notas - NEXUM')

@push('styles')
    @vite('resources/css/notas-estudiante.css')
@endpush

@section('content')
<div class="container-fluid p-4 p-lg-5 notas-page">
    <div class="mx-auto notas-container">

        {{-- ENCABEZADO --}}
        <div class="text-center notas-heading">
            <h1 class="display-5 fw-bold mb-3">NOTAS ACADÉMICAS</h1>
            <div class="notas-heading-line"></div>

            <div class="card border shadow-sm rounded-4 mx-auto notas-cycle-card">
                <div class="card-body p-4">
                    <p class="text-dark mb-2 fw-medium">
                        <i class="fa-solid fa-calendar-check text-danger me-2"></i>
                        @if ($ciclo)
                            Ciclo <strong>{{ $ciclo->nombre_ciclo }}</strong>
                            <span class="badge bg-light text-dark border ms-1">
                                {{ $ciclo->estado }}
                            </span>
                        @else
                            Sin ciclo asignado
                        @endif
                    </p>

                    <p class="text-muted small mb-0 notas-cycle-description">
                        <i class="fa-solid fa-circle-info me-1"></i>
                        Las calificaciones son registradas por tus docentes.
                    </p>
                </div>
            </div>
        </div>

        {{-- MENSAJE --}}
        @if ($mensaje)
            <div class="alert alert-info border-0 shadow-sm rounded-4 mb-4">
                <i class="bi bi-info-circle-fill me-1"></i>
                {{ $mensaje }}
            </div>
        @endif

        {{-- MATERIAS --}}
        @if ($secciones->isNotEmpty())

            <div class="accordion accordion-materias" id="accordionMaterias">

                @foreach ($secciones as $i => $sec)

                    @php
                        $materia = $sec['materia'] ?? null;
                        $notaFinal = $sec['nota_final_redondeada'] ?? 0.0;

                        [$badgeClass, $badgeLabel] = $sec['estado_badge']
                            ?? ['badge-sin-calificar', 'Sin calificar'];

                        $porcentajeCubierto = $sec['porcentaje_cubierto'] ?? 0.0;

                        $collapseId = 'collapseMateria-' . $sec['detalle_inscripcion_id'];
                        $abiertoPorDefecto = $i === 0;
                    @endphp

                    <div class="accordion-item card border-0 shadow-sm rounded-4 overflow-hidden">

                        {{-- ENCABEZADO DE MATERIA --}}
                        <h2 class="accordion-header"
                            id="heading-{{ $sec['detalle_inscripcion_id'] }}">

                            <button
                                class="accordion-button {{ $abiertoPorDefecto ? '' : 'collapsed' }} materia-header"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#{{ $collapseId }}"
                                aria-expanded="{{ $abiertoPorDefecto ? 'true' : 'false' }}"
                                aria-controls="{{ $collapseId }}">

                                <div class="materia-header-content w-100 pe-2">

                                    <div class="materia-info">

                                        <div class="materia-title">
                                            <code>
                                                {{ $materia?->codigo_materia ?? 'S/C' }}
                                            </code>

                                            <span>
                                                {{ $materia?->nombre ?? 'Sin nombre' }}
                                            </span>
                                        </div>

                                        <div class="materia-details">

                                            <span>
                                                <i class="fa-solid fa-user-tie"></i>
                                                {{ $sec['docente'] }}
                                            </span>

                                            @if ($sec['seccion']?->nombre_grupo)
                                                <span>
                                                    <i class="fa-solid fa-users"></i>
                                                    Grupo {{ $sec['seccion']->nombre_grupo }}
                                                </span>
                                            @endif

                                            @if ($sec['horarios']->isNotEmpty())
                                                <span class="materia-horarios">
                                                    <i class="fa-solid fa-clock"></i>

                                                    {{ $sec['horarios']->map(function ($h) {
                                                        $dia = $h->horario?->dia;

                                                        $diaStr = $dia instanceof \App\Enums\DiaSemana
                                                            ? $dia->short()
                                                            : (string) $dia;

                                                        $ini = substr($h->horario?->hora_inicio ?? '', 0, 5);
                                                        $fin = substr($h->horario?->hora_fin ?? '', 0, 5);

                                                        return trim("{$diaStr} {$ini}-{$fin}");
                                                    })->filter()->implode(' | ') }}
                                                </span>
                                            @endif

                                        </div>
                                    </div>

                                    <div class="materia-result">

                                        <div class="materia-grade">
                                            <span>Nota final</span>

                                            <strong class="{{ $notaFinal >= 6
                                                ? 'nota-aprobada'
                                                : ($porcentajeCubierto > 0 ? 'nota-reprobada' : 'text-muted') }}">
                                                {{ $porcentajeCubierto > 0
                                                    ? number_format($notaFinal, 1)
                                                    : '—' }}
                                            </strong>
                                        </div>

                                        <span class="estado-badge badge {{ $badgeClass }}">
                                            {{ $badgeLabel }}
                                        </span>

                                    </div>
                                </div>
                            </button>
                        </h2>

                        {{-- CUERPO DEL ACORDEÓN --}}
                        <div
                            id="{{ $collapseId }}"
                            class="accordion-collapse collapse {{ $abiertoPorDefecto ? 'show' : '' }}"
                            aria-labelledby="heading-{{ $sec['detalle_inscripcion_id'] }}"
                            data-bs-parent="#accordionMaterias">

                            <div class="accordion-body p-0">

                                {{-- TABLA PARA ESCRITORIO --}}
                                <div class="d-none d-md-block">
                                    <div class="table-responsive px-3 px-lg-4 pt-3 pb-2">

                                        <table class="table align-middle mb-0 tabla-notas">

                                            <thead>
                                                <tr>

                                                    @foreach ($sec['evaluaciones'] as $ev)
                                                        <th class="text-center fw-semibold text-dark"
                                                            style="min-width: 110px;">

                                                            <div>{{ $ev['nombre'] }}</div>

                                                            <small class="text-muted fw-normal th-sub">
                                                                {{ $ev['categoria'] }}
                                                                · P{{ $ev['periodo_numero'] }}
                                                                · {{ number_format($ev['porcentaje'], 0) }}%
                                                            </small>
                                                        </th>
                                                    @endforeach

                                                    <th class="text-center fw-semibold text-dark"
                                                        style="min-width: 100px;">
                                                        <div>Promedio</div>
                                                        <small class="text-muted fw-normal th-sub">
                                                            3 decimales
                                                        </small>
                                                    </th>

                                                    <th class="text-center fw-semibold text-dark"
                                                        style="min-width: 100px;">
                                                        <div>Nota Final</div>
                                                        <small class="text-muted fw-normal th-sub">
                                                            1 decimal
                                                        </small>
                                                    </th>

                                                    <th class="text-center fw-semibold text-dark pe-3"
                                                        style="min-width: 110px;">
                                                        Estado
                                                    </th>

                                                </tr>
                                            </thead>

                                            <tbody>
                                                <tr class="fila-materia">

                                                    @foreach ($sec['evaluaciones'] as $ev)
                                                        @php
                                                            $n = $sec['notas']->get($ev['evaluacion_id']);
                                                        @endphp

                                                        <td class="text-center">

                                                            <input
                                                                type="number"
                                                                class="form-control form-control-sm text-center nota-input"
                                                                value="{{ $n ? number_format($n['nota'], 1) : '' }}"
                                                                placeholder="—"
                                                                disabled>

                                                            @if ($n && $n['es_diferida'])
                                                                <small class="badge text-bg-info mt-1 d-inline-block">
                                                                    Diferida
                                                                </small>
                                                            @endif

                                                        </td>
                                                    @endforeach

                                                    <td class="text-center">
                                                        <span class="promedio-raw-display">
                                                            {{ $porcentajeCubierto > 0
                                                                ? number_format($sec['nota_final_calculada'] ?? 0.0, 3)
                                                                : '—' }}
                                                        </span>
                                                    </td>

                                                    <td class="text-center">
                                                        <span class="fw-bold fs-5 promedio-display {{ $notaFinal >= 6
                                                            ? 'nota-aprobada'
                                                            : ($porcentajeCubierto > 0 ? 'nota-reprobada' : '') }}">
                                                            {{ $porcentajeCubierto > 0
                                                                ? number_format($notaFinal, 1)
                                                                : '—' }}
                                                        </span>
                                                    </td>

                                                    <td class="text-center pe-3">
                                                        <span class="estado-badge badge {{ $badgeClass }}">
                                                            {{ $badgeLabel }}
                                                        </span>
                                                    </td>

                                                </tr>
                                            </tbody>

                                        </table>
                                    </div>
                                </div>

                                {{-- TARJETAS PARA CELULAR --}}
                                <div class="materia-evaluaciones-mobile d-md-none">

                                    @foreach ($sec['evaluaciones'] as $ev)

                                        @php
                                            $n = $sec['notas']->get($ev['evaluacion_id']);
                                        @endphp

                                        <div class="evaluacion-card">

                                            <div class="evaluacion-card-info">
                                                <strong>{{ $ev['nombre'] }}</strong>

                                                <span>
                                                    {{ $ev['categoria'] }}
                                                    · Periodo {{ $ev['periodo_numero'] }}
                                                </span>

                                                @if ($n && $n['es_diferida'])
                                                    <span class="badge text-bg-info evaluacion-diferida">
                                                        Evaluación diferida
                                                    </span>
                                                @endif
                                            </div>

                                            <div class="evaluacion-card-result">
                                                <strong>
                                                    {{ $n ? number_format($n['nota'], 1) : '—' }}
                                                </strong>

                                                <span>
                                                    {{ number_format($ev['porcentaje'], 0) }}%
                                                </span>
                                            </div>

                                        </div>

                                    @endforeach

                                    {{-- RESUMEN DE CALIFICACIONES --}}
                                    <div class="evaluacion-resumen-mobile">

                                        <div>
                                            <span>Promedio calculado</span>

                                            <strong>
                                                {{ $porcentajeCubierto > 0
                                                    ? number_format($sec['nota_final_calculada'] ?? 0.0, 3)
                                                    : '—' }}
                                            </strong>
                                        </div>

                                        <div>
                                            <span>Nota final</span>

                                            <strong class="{{ $notaFinal >= 6
                                                ? 'nota-aprobada'
                                                : ($porcentajeCubierto > 0 ? 'nota-reprobada' : 'text-muted') }}">
                                                {{ $porcentajeCubierto > 0
                                                    ? number_format($notaFinal, 1)
                                                    : '—' }}
                                            </strong>
                                        </div>

                                    </div>
                                </div>

                                {{-- PORCENTAJE CALIFICADO --}}
                                <div class="notas-progress-footer border-top">

                                    <i class="fa-solid fa-chart-simple me-1"></i>

                                    Porcentaje calificado:
                                    <strong>
                                        {{ number_format($porcentajeCubierto, 0) }}%
                                    </strong>

                                    @if ($porcentajeCubierto < 100)
                                        <span class="text-warning">
                                            · Aún hay evaluaciones pendientes de calificar.
                                        </span>
                                    @endif

                                </div>

                            </div>
                        </div>
                    </div>

                @endforeach

            </div>

        @else

            @if (!$mensaje)
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body text-center py-5 text-muted">
                        <i class="fa-solid fa-inbox fs-1 d-block mb-3"></i>
                        No tienes materias inscritas en el ciclo actual.
                    </div>
                </div>
            @endif

        @endif

        {{-- PROMEDIO GENERAL --}}
        @if ($secciones->isNotEmpty())

            @php
                $materiasCalificadas = $secciones->filter(
                    fn ($s) => ($s['porcentaje_cubierto'] ?? 0) > 0
                );

                $aprobadas = $materiasCalificadas->filter(
                    fn ($s) => ($s['nota_final_redondeada'] ?? 0) >= 6
                )->count();

                $reprobadas = $materiasCalificadas->filter(
                    fn ($s) => ($s['nota_final_redondeada'] ?? 0) < 6
                )->count();

                $promedioGeneral = $materiasCalificadas->isNotEmpty()
                    ? round($materiasCalificadas->avg('nota_final_redondeada'), 1)
                    : null;
            @endphp

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mt-4 notas-general-card">

                <div class="notas-general-body">

                    <div class="promedio-general-contenido">

                        <div class="promedio-general-heading">
                            <div class="promedio-general-icon">
                                <i class="fa-solid fa-chart-line"></i>
                            </div>

                            <div>
                                <h5 class="fw-bold mb-1">
                                    Promedio General de Materias
                                </h5>

                                <p class="text-muted mb-0">
                                    Calculado en base al promedio redondeado de cada materia registrada.
                                </p>
                            </div>
                        </div>

                        <div class="promedio-general-indicadores">

                            <div class="general-indicador">
                                <span>Aprobadas</span>
                                <strong class="nota-aprobada">{{ $aprobadas }}</strong>
                            </div>

                            <div class="general-indicador">
                                <span>Reprobadas</span>
                                <strong class="nota-reprobada">{{ $reprobadas }}</strong>
                            </div>

                            <div class="general-indicador promedio-destacado">
                                <span>Promedio General</span>

                                <strong class="{{ $promedioGeneral !== null && $promedioGeneral >= 6
                                    ? 'nota-aprobada'
                                    : 'text-danger' }}">
                                    {{ $promedioGeneral !== null
                                        ? number_format($promedioGeneral, 1)
                                        : '—' }}
                                </strong>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

        @endif

    </div>
</div>
@endsection