@extends('layouts.app')
@section('title', 'Modificar Beneficios Estudiantiles')

@section('content')
<div class="container-fluid py-4">

    {{-- ============================================================
         ENCABEZADO
         ============================================================ --}}
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-md-between gap-3 p-3 mb-4 rounded-4 card">
        <div class="d-flex align-items-center">
            <div class="px-3 py-2 rounded-3 bg-danger bg-opacity-10" style="width: 48px; height: 48px;">
                <i class="bi bi-cash-coin fs-4 text-danger"></i>
            </div>
            <div class="px-3">
                <h1 class="h4 fw-bold mb-0">Modificar Beneficios</h1>
                <p class="text-muted mb-0 small">Gestión de becas, franjas y cuotas especiales activas</p>
            </div>
        </div>
        <div>
            <a href="{{ route('home') }}" class="btn bg-danger-subtle text-danger border border-danger px-3 py-2">
                <i class="fa-solid fa-home me-1"></i> Inicio
            </a>
        </div>
    </div>

    {{-- ============================================================
         ALERTAS
         ============================================================ --}}
    @if(session('success'))
        <div class="alert alert-success d-flex align-items-center gap-2 border-0 shadow-sm mb-4">
            <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger d-flex align-items-center gap-2 border-0 shadow-sm mb-4">
            <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}
        </div>
    @endif

    {{-- ============================================================
         FILTROS
         ============================================================ --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-center">
                <div class="col-md-6">
                    <label class="form-label small fw-medium mb-1">Buscar estudiante</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text"
                            id="buscador-beneficios"
                            class="form-control border-start-0 ps-0"
                            placeholder="Nombre, carnet o convenio..."
                            autocomplete="off">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-medium mb-1">Tipo de beneficio</label>
                    <select id="filtro-tipo" class="form-select">
                        <option value="">Todos</option>
                        @foreach(\App\Enums\TipoBeneficio::cases() as $t)
                            <option value="{{ $t->value }}">{{ $t->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <div class="text-muted small w-100">
                        <i class="bi bi-info-circle me-1"></i>
                        <span id="contador-resultados">{{ $beneficios->total() }}</span> resultado(s)
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================
         GRID DE CARDS
         ============================================================ --}}
    <div class="row g-4">
        @forelse($beneficios as $beneficio)
            @php
                $eval   = $evaluaciones[$beneficio->beneficio_id];
                $alumno = $beneficio->alumno;
            @endphp

            <div class="col-md-6 col-xl-4 card-beneficio" 
                data-nombre="{{ strtolower($alumno->usuario->name ?? '') }}"
                data-carnet="{{ strtolower($alumno->codigo_estudiante ?? '') }}"
                data-convenio="{{ strtolower($beneficio->nombre_convenio ?? '') }}"
                data-tipo="{{ $beneficio->tipo_beneficio->value }}">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">

                        {{-- Alumno --}}
                        <div class="d-flex align-items-start gap-3 mb-3">
                            <div class="rounded-circle bg-danger bg-opacity-10 d-flex align-items-center justify-content-center fw-bold text-danger"
                                 style="width: 48px; height: 48px;">
                                {{ strtoupper(substr($alumno->usuario->name ?? 'XX', 0, 2)) }}
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="fw-bold mb-0">{{ $alumno->usuario->name }}</h6>
                                <small class="text-muted d-block font-monospace">{{ $alumno->codigo_estudiante }}</small>
                                <small class="text-muted">{{ $alumno->carrera->nombre ?? '—' }}</small>
                            </div>
                        </div>

                        {{-- Beneficio actual --}}
                        <div class="border rounded-3 p-3 mb-3 bg-light">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <small class="text-muted">Beneficio activo</small>
                                <span class="badge bg-danger-subtle text-danger border border-danger">
                                    {{ $beneficio->tipo_beneficio->label() }}
                                </span>
                            </div>
                            <div class="small">
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Convenio:</span>
                                    <span class="fw-medium text-truncate ms-2" style="max-width: 60%;">
                                        {{ $beneficio->nombre_convenio }}
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Ciclo:</span>
                                    <span class="fw-medium">{{ $beneficio->cicloLectivo->nombre_ciclo ?? '—' }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- Evaluación --}}
                        @if($eval['cumple'])
                            <div class="alert alert-success py-2 px-3 small mb-3">
                                <i class="bi bi-check-circle-fill me-1"></i>
                                Cumple reglas administrativas
                            </div>
                        @else
                            <div class="alert alert-warning py-2 px-3 small mb-3">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                <strong>Revisión:</strong>
                                <ul class="mb-0 ps-3 mt-1">
                                    @foreach($eval['motivos'] as $m)
                                        <li>{{ $m }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        {{-- Botón abrir modal --}}
                        <div class="d-grid">
                            <button type="button"
                                    class="btn btn-outline-danger btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalBeneficio{{ $beneficio->beneficio_id }}">
                                <i class="bi bi-pencil-square me-1"></i> Gestionar beneficio
                            </button>
                        </div>

                    </div>
                </div>
            </div>

            {{-- ============================================================
                 MODAL POR CADA BENEFICIO
                 ============================================================ --}}
            <div class="modal fade" id="modalBeneficio{{ $beneficio->beneficio_id }}" tabindex="-1">
                <div class="modal-dialog modal-xl modal-dialog-scrollable">
                    <div class="modal-content border-0 shadow-lg">
                        <div class="modal-header bg-white border-bottom">
                            <h5 class="modal-title fw-bold">Gestión del beneficio</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                       <div class="modal-body p-4">

                            {{-- =========================================================
                                1. DATOS DEL ESTUDIANTE
                                ========================================================= --}}
                            <section class="mb-4">
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <span class="badge bg-danger rounded-circle"
                                        style="width: 24px; height: 24px; line-height: 16px;">1</span>
                                    <h6 class="text-danger fw-bold mb-0">
                                        <i class="bi bi-person-vcard me-1"></i> Datos del Estudiante
                                    </h6>
                                </div>

                                <div class="border rounded-3 p-3">
                                    <div class="d-flex align-items-center gap-3 mb-3 pb-3 border-bottom">
                                        <div class="rounded-circle bg-danger bg-opacity-10 d-flex align-items-center justify-content-center fw-bold text-danger"
                                            style="width: 56px; height: 56px; font-size: 1.2rem;">
                                            {{ strtoupper(substr($alumno->usuario->name ?? 'XX', 0, 2)) }}
                                        </div>
                                        <div>
                                            <h6 class="fw-bold mb-0">{{ $alumno->usuario->name }}</h6>
                                            <small class="text-muted d-block font-monospace">{{ $alumno->codigo_estudiante }}</small>
                                            <small class="text-muted">{{ $alumno->carrera->nombre ?? '—' }}</small>
                                        </div>
                                    </div>

                                    <div class="row g-3 small">
                                        <div class="col-md-6">
                                            <span class="text-muted d-block mb-1">Email</span>
                                            <span class="fw-medium">{{ $alumno->usuario->email }}</span>
                                        </div>
                                        <div class="col-md-6">
                                            <span class="text-muted d-block mb-1">Facultad</span>
                                            <span class="fw-medium">{{ $alumno->carrera->facultad->nombre ?? '—' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </section>

                            {{-- =========================================================
                                2. EVALUACIÓN DE REGLAS
                                ========================================================= --}}
                            <section class="mb-4">
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <span class="badge bg-danger rounded-circle"
                                        style="width: 24px; height: 24px; line-height: 16px;">2</span>
                                    <h6 class="text-danger fw-bold mb-0">
                                        <i class="bi bi-shield-check me-1"></i> Evaluación de Reglas
                                    </h6>
                                </div>

                                {{-- Parámetros de la regla --}}
                                @if($eval['regla'])
                                    <div class="border rounded-3 p-3 mb-3">
                                        <div class="row g-3 small">
                                            <div class="col-6 col-md-3">
                                                <span class="text-muted d-block mb-1">CUM mínimo</span>
                                                <span class="fw-semibold fs-6">{{ $eval['regla']->cum_minimo }}</span>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <span class="text-muted d-block mb-1">Nota mín. materia</span>
                                                <span class="fw-semibold fs-6">{{ $eval['regla']->nota_minima_materia }}</span>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <span class="text-muted d-block mb-1">Reprob. máx. ciclo</span>
                                                <span class="fw-semibold fs-6">{{ $eval['regla']->max_materias_reprobadas_ciclo }}</span>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <span class="text-muted d-block mb-1">Solvencia requerida</span>
                                                <span class="fw-semibold fs-6">{{ $eval['regla']->requiere_solvencia_financiera ? 'Sí' : 'No' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                {{-- Resultado --}}
                                @if($eval['cumple'])
                                    <div class="alert alert-success border-0 d-flex align-items-start gap-2 mb-0">
                                        <i class="bi bi-check-circle-fill fs-5"></i>
                                        <div>
                                            <strong>Cumple las reglas administrativas evaluadas.</strong>
                                            <div class="small text-muted mt-1">
                                                El estudiante puede mantener el beneficio en el ciclo actual.
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="alert alert-danger border-0 d-flex align-items-start gap-2 mb-0">
                                        <i class="bi bi-x-circle-fill fs-5"></i>
                                        <div>
                                            <strong>Incumple las siguientes reglas:</strong>
                                            <ul class="mb-0 mt-2 ps-3">
                                                @foreach($eval['motivos'] as $m)
                                                    <li>{{ $m }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                @endif
                            </section>

                            {{-- =========================================================
                                3. BENEFICIO ACTUAL
                                ========================================================= --}}
                            <section class="mb-4">
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <span class="badge bg-danger rounded-circle"
                                        style="width: 24px; height: 24px; line-height: 16px;">3</span>
                                    <h6 class="text-danger fw-bold mb-0">
                                        <i class="bi bi-cash-coin me-1"></i> Beneficio Actual
                                    </h6>
                                </div>

                                <div class="border rounded-3 p-3">
                                    <div class="row g-3 small">
                                        <div class="col-md-6">
                                            <span class="text-muted d-block mb-1">Tipo de beneficio</span>
                                            <span class="badge bg-danger-subtle text-danger border border-danger">
                                                {{ $beneficio->tipo_beneficio->label() }}
                                            </span>
                                        </div>
                                        <div class="col-md-6">
                                            <span class="text-muted d-block mb-1">Ciclo lectivo</span>
                                            <span class="fw-medium">{{ $beneficio->cicloLectivo->nombre_ciclo ?? '—' }}</span>
                                        </div>
                                        <div class="col-md-6">
                                            <span class="text-muted d-block mb-1">Convenio / Beca</span>
                                            <span class="fw-medium">{{ $beneficio->nombre_convenio }}</span>
                                        </div>
                                        <div class="col-md-6">
                                            <span class="text-muted d-block mb-1">Resolución académica</span>
                                            <span class="fw-medium">{{ $beneficio->resolucion_academica ?? '—' }}</span>
                                        </div>

                                        {{-- Cobertura --}}
                                        <div class="col-12">
                                            <hr class="my-2">
                                        </div>

                                        <div class="col-md-4">
                                            <span class="text-muted d-block mb-1">% Estudiante</span>
                                            <span class="fw-semibold fs-5 text-danger">{{ $beneficio->porcentaje_estudiante }}%</span>
                                        </div>
                                        <div class="col-md-4">
                                            <span class="text-muted d-block mb-1">% Universidad</span>
                                            <span class="fw-semibold fs-5 text-success">{{ $beneficio->porcentaje_universidad }}%</span>
                                        </div>
                                        <div class="col-md-4">
                                            <span class="text-muted d-block mb-1">Monto fijo cuota</span>
                                            <span class="fw-semibold fs-5">
                                                @if($beneficio->monto_fijo_cuota)
                                                    ${{ number_format($beneficio->monto_fijo_cuota, 2) }}
                                                @else
                                                    <span class="text-muted fs-6">N/A</span>
                                                @endif
                                            </span>
                                        </div>

                                        {{-- Cobertura adicional --}}
                                        <div class="col-12">
                                            <span class="text-muted d-block mb-2 mt-2">Cobertura adicional</span>
                                            <div class="d-flex gap-2 flex-wrap">
                                                <span class="badge {{ $beneficio->incluye_matricula ? 'bg-success-subtle text-success border border-success' : 'bg-secondary-subtle text-secondary border' }}">
                                                    <i class="bi {{ $beneficio->incluye_matricula ? 'bi-check-circle-fill' : 'bi-x-circle-fill' }} me-1"></i>
                                                    Matrícula
                                                </span>
                                                <span class="badge {{ $beneficio->incluye_laboratorio ? 'bg-success-subtle text-success border border-success' : 'bg-secondary-subtle text-secondary border' }}">
                                                    <i class="bi {{ $beneficio->incluye_laboratorio ? 'bi-check-circle-fill' : 'bi-x-circle-fill' }} me-1"></i>
                                                    Laboratorio
                                                </span>
                                                <span class="badge {{ $beneficio->incluye_derechos_grado ? 'bg-success-subtle text-success border border-success' : 'bg-secondary-subtle text-secondary border' }}">
                                                    <i class="bi {{ $beneficio->incluye_derechos_grado ? 'bi-check-circle-fill' : 'bi-x-circle-fill' }} me-1"></i>
                                                    Derechos de grado
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>

                            {{-- =========================================================
                                4. ACCIONES
                                ========================================================= --}}
                            <section>
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <span class="badge bg-danger rounded-circle"
                                        style="width: 24px; height: 24px; line-height: 16px;">4</span>
                                    <h6 class="text-danger fw-bold mb-0">
                                        <i class="bi bi-gear me-1"></i> Acciones
                                    </h6>
                                </div>

                                <div class="d-grid gap-2 d-md-flex">
                                    <button type="button"
                                            class="btn btn-outline-danger flex-fill"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#panel-revocar-{{ $beneficio->beneficio_id }}">
                                        <i class="bi bi-x-circle me-1"></i> Revocar beneficio
                                    </button>
                                    <button type="button"
                                            class="btn btn-outline-primary flex-fill"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#panel-reasignar-{{ $beneficio->beneficio_id }}">
                                        <i class="bi bi-arrow-repeat me-1"></i> Reasignar beneficio
                                    </button>
                                </div>

                                {{-- Panel revocar --}}
                                <div class="collapse mt-3" id="panel-revocar-{{ $beneficio->beneficio_id }}">
                                    <div class="border border-danger rounded-3 p-3 bg-danger bg-opacity-10">
                                        <form action="{{ route('admin-academico.admin-academico.beneficios.revoke', $beneficio) }}"
                                            method="POST"
                                            onsubmit="return confirm('¿Confirmás revocar el beneficio? Se notificará al estudiante.');">
                                            @csrf
                                            <label class="form-label fw-medium small text-danger">
                                                Motivo de la revocación <span class="text-danger">*</span>
                                            </label>
                                            <textarea name="motivo_revocacion" rows="3"
                                                    class="form-control mb-3"
                                                    placeholder="Ej: Incumplimiento del CUM mínimo requerido..."
                                                    required minlength="10" maxlength="500"></textarea>
                                            <button type="submit" class="btn btn-danger">
                                                <i class="bi bi-trash me-1"></i> Confirmar revocación
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                {{-- Panel reasignar --}}
                                <div class="collapse mt-3" id="panel-reasignar-{{ $beneficio->beneficio_id }}">
                                    <div class="border border-primary rounded-3 p-3 bg-primary bg-opacity-10">
                                        <form action="{{ route('admin-academico.admin-academico.beneficios.reassign', $beneficio) }}"
                                            method="POST"
                                            onsubmit="return confirm('¿Confirmás reasignar el beneficio? Se revocará el actual y se notificará.');">
                                            @csrf
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label fw-medium small">Nuevo tipo de beneficio *</label>
                                                    <select name="tipo_beneficio"
                                                            class="form-select tipo-beneficio-select"
                                                            data-modal="reasignar-{{ $beneficio->beneficio_id }}"
                                                            required>
                                                        <option value="">Seleccione...</option>
                                                        @foreach(\App\Enums\TipoBeneficio::cases() as $t)
                                                            <option value="{{ $t->value }}">{{ $t->label() }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-medium small">Nombre del convenio *</label>
                                                    <input type="text" name="nombre_convenio" class="form-control"
                                                        required placeholder="Ej: Convenio UMA-FUSAL">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-medium small">Resolución académica</label>
                                                    <input type="text" name="resolucion_academica" class="form-control"
                                                        placeholder="Ej: RA-2026-020">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-medium small">Motivo del cambio</label>
                                                    <input type="text" name="motivo_cambio" class="form-control"
                                                        placeholder="Ej: Mejora de rendimiento académico">
                                                </div>

                                                {{-- Porcentajes (solo BECA_PARCIAL y CUOTA_ESPECIAL) --}}
                                                <div class="col-md-4 campo-pct-reasignar" style="display:none;">
                                                    <label class="form-label fw-medium small">% Estudiante</label>
                                                    <input type="number"
                                                        name="porcentaje_estudiante"
                                                        step="0.01" min="0" max="100"
                                                        class="form-control input-pct-est"
                                                        placeholder="Ej: 50.00">
                                                </div>
                                                <div class="col-md-4 campo-pct-reasignar" style="display:none;">
                                                    <label class="form-label fw-medium small">% Universidad</label>
                                                    <input type="number"
                                                        name="porcentaje_universidad"
                                                        step="0.01" min="0" max="100"
                                                        class="form-control input-pct-univ"
                                                        placeholder="Ej: 50.00">
                                                </div>

                                                {{-- Monto fijo (solo FRANJA_BECARIA) --}}
                                                <div class="col-md-4 campo-monto-reasignar" style="display:none;">
                                                    <label class="form-label fw-medium small">Monto fijo ($)</label>
                                                    <input type="number"
                                                        name="monto_fijo_cuota"
                                                        step="0.01" min="0"
                                                        class="form-control input-monto-fijo"
                                                        placeholder="Ej: 53.00">
                                                </div>

                                                {{-- Matrícula (solo BECA_PARCIAL y CUOTA_ESPECIAL) --}}
                                                <div class="col-12 campo-matricula-reasignar" style="display:none;">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input"
                                                            type="checkbox"
                                                            name="incluye_matricula"
                                                            value="1"
                                                            id="chk-matricula-{{ $beneficio->beneficio_id }}">
                                                        <label class="form-check-label fw-medium small"
                                                            for="chk-matricula-{{ $beneficio->beneficio_id }}">
                                                            Incluir <strong>matrícula del ciclo</strong> en la cobertura
                                                        </label>
                                                    </div>
                                                </div>

                                                {{-- Laboratorio (solo BECA_COMPLETA) --}}
                                                <div class="col-12 campo-laboratorio-reasignar" style="display:none;">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input"
                                                            type="checkbox"
                                                            name="incluye_laboratorio"
                                                            value="1"
                                                            id="chk-laboratorio-{{ $beneficio->beneficio_id }}">
                                                        <label class="form-check-label fw-medium small"
                                                            for="chk-laboratorio-{{ $beneficio->beneficio_id }}">
                                                            Incluir <strong>laboratorio de informática</strong> en la cobertura
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mt-3">
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="bi bi-arrow-repeat me-1"></i> Confirmar reasignación
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </section>

                        </div>

                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-inbox fs-1 text-muted d-block mb-3"></i>
                        <p class="text-muted mb-0">No hay beneficios activos registrados.</p>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Paginación --}}
    <div class="mt-4 d-flex justify-content-end">
        {{ $beneficios->links() }}
    </div>

</div>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    /* ============================================================
       1. BUSCADOR EN TIEMPO REAL
       ============================================================ */
    (function initBuscador() {
        const input    = document.getElementById('buscador-beneficios');
        const select   = document.getElementById('filtro-tipo');
        const contador = document.getElementById('contador-resultados');
        const cards    = document.querySelectorAll('.card-beneficio');

        if (!input && !select) return;

        function filtrar() {
            const q    = (input?.value || '').trim().toLowerCase();
            const tipo = select?.value || '';

            let visibles = 0;

            cards.forEach(card => {
                const nombre   = card.dataset.nombre   || '';
                const carnet   = card.dataset.carnet   || '';
                const convenio = card.dataset.convenio || '';
                const tipoCard = card.dataset.tipo     || '';

                const coincideTexto =
                    q === '' ||
                    nombre.includes(q) ||
                    carnet.includes(q) ||
                    convenio.includes(q);

                const coincideTipo = tipo === '' || tipoCard === tipo;

                const visible = coincideTexto && coincideTipo;
                card.style.display = visible ? '' : 'none';
                if (visible) visibles++;
            });

            if (contador) contador.textContent = visibles;
        }

        let timeout;
        input?.addEventListener('input', () => {
            clearTimeout(timeout);
            timeout = setTimeout(filtrar, 150);
        });
        select?.addEventListener('change', filtrar);

        filtrar();
    })();

    /* ============================================================
       2. FORMULARIOS DE REASIGNACIÓN (uno por modal)
       ============================================================ */
    document.querySelectorAll('.tipo-beneficio-select').forEach(select => {

        const modalId = select.dataset.modal; // ej: "reasignar-12"
        const form    = select.closest('form');
        if (!form) return;

        // Elementos del form
        const pctFields        = form.querySelectorAll('.campo-pct-reasignar');
        const montoField       = form.querySelector('.campo-monto-reasignar');
        const wrapMatricula    = form.querySelector('.campo-matricula-reasignar');
        const wrapLaboratorio  = form.querySelector('.campo-laboratorio-reasignar');

        const pctEstInput      = form.querySelector('.input-pct-est');
        const pctUnivInput     = form.querySelector('.input-pct-univ');
        const montoInput       = form.querySelector('.input-monto-fijo');

        const chkMatricula     = form.querySelector('input[name="incluye_matricula"]');
        const chkLaboratorio   = form.querySelector('input[name="incluye_laboratorio"]');

        function actualizar() {
            const tipo = select.value;

            const esParcial  = tipo === 'BECA_PARCIAL' || tipo === 'CUOTA_ESPECIAL';
            const esFranja   = tipo === 'FRANJA_BECARIA';
            const esCompleta = tipo === 'BECA_COMPLETA';

            // Mostrar / ocultar bloques
            pctFields.forEach(el => el.style.display = esParcial ? '' : 'none');
            if (montoField)     montoField.style.display     = esFranja ? '' : 'none';
            if (wrapMatricula)  wrapMatricula.style.display  = esParcial ? '' : 'none';
            if (wrapLaboratorio) wrapLaboratorio.style.display = esCompleta ? '' : 'none';

            // Valores sugeridos
            if (tipo === 'BECA_COMPLETA') {
                if (pctEstInput)  pctEstInput.value  = 0;
                if (pctUnivInput) pctUnivInput.value = 100;
            } else if (tipo === 'CUOTA_ESPECIAL') {
                if (pctEstInput && !pctEstInput.value)   pctEstInput.value  = 88.33;
                if (pctUnivInput && !pctUnivInput.value) pctUnivInput.value = 11.67;
            } else if (tipo === 'BECA_PARCIAL') {
                if (pctEstInput && !pctEstInput.value)   pctEstInput.value  = 50.00;
                if (pctUnivInput && !pctUnivInput.value) pctUnivInput.value = 50.00;
            } else if (esFranja) {
                if (montoInput && !montoInput.value) montoInput.value = 53.00;
            }

            // Limpiar campos al ocultarlos (evitar valores residuales)
            if (!esParcial) {
                if (chkMatricula) chkMatricula.checked = false;
                if (pctEstInput)  pctEstInput.value  = '';
                if (pctUnivInput) pctUnivInput.value = '';
            }
            if (!esCompleta && chkLaboratorio) {
                chkLaboratorio.checked = false;
            }
            if (!esFranja && montoInput) {
                montoInput.value = '';
            }
        }

        // Sincronizar % Estudiante → % Universidad
        pctEstInput?.addEventListener('input', () => {
            if (pctUnivInput && pctEstInput.value !== '') {
                const est = parseFloat(pctEstInput.value || 0);
                pctUnivInput.value = (100 - est).toFixed(2);
            }
        });

        select.addEventListener('change', actualizar);

        // Inicializar al cargar (por si el navegador restaura valores)
        actualizar();
    });
});
</script>
@endpush
@endsection