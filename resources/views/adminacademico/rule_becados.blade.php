@extends('layouts.app')

@section('title', 'Reglas de Beneficio')

@section('content')
<div class="container-fluid p-4">

    {{-- Header --}}
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-md-between gap-3 p-3 mb-4 rounded-4 card">
        <div class="d-flex align-items-center">
            <div class="px-3 py-2 rounded-3 bg-danger bg-opacity-10" style="width: 48px; height: 48px;">
                <i class="bi bi-sliders2 fs-4 text-danger"></i>
            </div>
            <div class="px-3">
                <h1 class="h4 fw-bold mb-0">GESTION DE REGLAS DE BENEFICIO ESTUDIANTIL</h1>
                <p class="text-muted mb-0 small">
                    Parámetros académicos y de solvencia por tipo de beca
                </p>
            </div>
        </div>
        <a href="{{ route('home') }}" class="btn bg-danger-subtle text-danger border border-danger px-3 py-2">
            <i class="fa-solid fa-home me-1"></i> Inicio
        </a>
    </div>

    {{-- Nota informativa --}}
    <div class="alert alert-info small border-0 mb-4">
        <i class="bi bi-info-circle-fill me-1"></i>
        Las reglas son <strong>configuraciones institucionales</strong>. Solo podés <strong>editarlas</strong>;
        no se pueden crear ni eliminar desde este módulo.
    </div>

    {{-- Listado de reglas --}}
    @forelse($reglas as $regla)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-3 d-flex align-items-center justify-content-center"
                             style="width: 42px; height: 42px; background-color: var(--color-hover-bg, #f8f9fa);">
                            <i class="bi bi-award-fill text-danger fs-5"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0">
                                {{ $regla->tipo_beneficio->label() }}
                            </h5>
                            <small class="text-muted">
                                Vigente desde {{ $regla->vigente_desde?->format('d/m/Y') ?? '—' }}
                            </small>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        @if($regla->activo)
                            <span class="badge bg-success-subtle text-success border border-success-subtle">
                                <i class="bi bi-check-circle-fill me-1"></i> Activa
                            </span>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                <i class="bi bi-pause-circle-fill me-1"></i> Inactiva
                            </span>
                        @endif
                        <button class="btn btn-sm btn-outline-primary"
                                data-bs-toggle="modal"
                                data-bs-target="#modalEditarRegla{{ $regla->regla_beneficio_id }}">
                            <i class="bi bi-pencil me-1"></i> Editar
                        </button>
                    </div>
                </div>
            </div>

            <div class="card-body p-4">
                <div class="row g-3">

                    <div class="col-6 col-md-3">
                        <div class="border rounded-3 p-3 bg-light h-100">
                            <small class="text-muted d-block mb-1">CUM mínimo</small>
                            <strong class="fs-5">{{ number_format((float) $regla->cum_minimo, 2) }}</strong>
                        </div>
                    </div>

                    <div class="col-6 col-md-3">
                        <div class="border rounded-3 p-3 bg-light h-100">
                            <small class="text-muted d-block mb-1">Nota mínima por materia</small>
                            <strong class="fs-5">{{ number_format((float) $regla->nota_minima_materia, 2) }}</strong>
                        </div>
                    </div>

                    <div class="col-6 col-md-3">
                        <div class="border rounded-3 p-3 bg-light h-100">
                            <small class="text-muted d-block mb-1">Máx. reprobaciones</small>
                            <strong class="fs-5">{{ $regla->max_reprobaciones_permitidas }}</strong>
                        </div>
                    </div>

                    <div class="col-6 col-md-3">
                        <div class="border rounded-3 p-3 bg-light h-100">
                            <small class="text-muted d-block mb-1">Máx. materias reprobadas/ciclo</small>
                            <strong class="fs-5">{{ $regla->max_materias_reprobadas_ciclo }}</strong>
                        </div>
                    </div>

                    <div class="col-6 col-md-3">
                        <div class="border rounded-3 p-3 bg-light h-100">
                            <small class="text-muted d-block mb-1">UV mínimas cursadas</small>
                            <strong class="fs-5">{{ $regla->uv_minimas_cursadas }}</strong>
                        </div>
                    </div>

                    <div class="col-6 col-md-3">
                        <div class="border rounded-3 p-3 bg-light h-100">
                            <small class="text-muted d-block mb-1">Solvencia financiera</small>
                            @if($regla->requiere_solvencia_financiera)
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">
                                    <i class="bi bi-check-circle-fill me-1"></i> Requerida
                                </span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                    <i class="bi bi-x-circle me-1"></i> No requerida
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="col-6 col-md-3">
                        <div class="border rounded-3 p-3 bg-light h-100">
                            <small class="text-muted d-block mb-1">Actividades extracurriculares</small>
                            @if($regla->requiere_actividades)
                                <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle">
                                    <i class="bi bi-check-circle-fill me-1"></i> Requeridas
                                </span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                    <i class="bi bi-x-circle me-1"></i> No requeridas
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="border rounded-3 p-3 bg-light">
                            <small class="text-muted d-block mb-1">Observaciones</small>
                            <span class="small">{{ $regla->observaciones ?? '—' }}</span>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- MODAL: Editar regla --}}
        <div class="modal fade" id="modalEditarRegla{{ $regla->regla_beneficio_id }}" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-white border-bottom">
                        <h5 class="modal-title fw-bold">
                            Editar regla: {{ $regla->tipo_beneficio->label() }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="{{ route('admin-academico.reglas-beneficio.update', $regla) }}" method="POST">
                        @csrf @method('PUT')
                        <div class="modal-body p-4">

                            <div class="alert alert-light border small mb-4">
                                <i class="bi bi-info-circle me-1"></i>
                                Modificá solo los parámetros que correspondan. El tipo de beneficio
                                y la fecha de vigencia no se pueden cambiar.
                            </div>

                            <div class="row g-3">

                                {{-- Tipo (solo lectura) --}}
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold text-muted">
                                        Tipo de beneficio
                                    </label>

                                    {{-- Hidden que envía el valor real --}}
                                    <input type="hidden" name="tipo_beneficio" value="{{ $regla->tipo_beneficio->value }}">

                                    {{-- Visual de solo lectura --}}
                                    <input type="text"
                                        class="form-control bg-light"
                                        value="{{ $regla->tipo_beneficio->label() }}"
                                        readonly>
                                </div>

                                {{-- Vigencia desde (solo lectura) --}}
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold text-muted">
                                        Vigente desde
                                    </label>
                                    <input type="text"
                                           class="form-control bg-light"
                                           value="{{ $regla->vigente_desde?->format('d/m/Y') ?? '—' }}"
                                           readonly>
                                </div>

                                {{-- CUM mínimo --}}
                                <div class="col-md-6">
                                    <label for="cum-{{ $regla->regla_beneficio_id }}" class="form-label small fw-semibold">
                                        CUM mínimo <span class="text-danger">*</span>
                                    </label>
                                    <input type="number"
                                           step="0.01" min="0" max="10"
                                           name="cum_minimo"
                                           id="cum-{{ $regla->regla_beneficio_id }}"
                                           class="form-control"
                                           value="{{ old('cum_minimo', $regla->cum_minimo) }}"
                                           required>
                                </div>

                                {{-- Nota mínima por materia --}}
                                <div class="col-md-6">
                                    <label for="nota-{{ $regla->regla_beneficio_id }}" class="form-label small fw-semibold">
                                        Nota mínima por materia <span class="text-danger">*</span>
                                    </label>
                                    <input type="number"
                                           step="0.01" min="0" max="10"
                                           name="nota_minima_materia"
                                           id="nota-{{ $regla->regla_beneficio_id }}"
                                           class="form-control"
                                           value="{{ old('nota_minima_materia', $regla->nota_minima_materia) }}"
                                           required>
                                </div>

                                {{-- Máx. reprobaciones --}}
                                <div class="col-md-6">
                                    <label for="rep-{{ $regla->regla_beneficio_id }}" class="form-label small fw-semibold">
                                        Máximo de reprobaciones permitidas <span class="text-danger">*</span>
                                    </label>
                                    <input type="number"
                                           min="0" max="20"
                                           name="max_reprobaciones_permitidas"
                                           id="rep-{{ $regla->regla_beneficio_id }}"
                                           class="form-control"
                                           value="{{ old('max_reprobaciones_permitidas', $regla->max_reprobaciones_permitidas) }}"
                                           required>
                                </div>

                                {{-- Máx. materias reprobadas por ciclo --}}
                                <div class="col-md-6">
                                    <label for="matrep-{{ $regla->regla_beneficio_id }}" class="form-label small fw-semibold">
                                        Máximo de materias reprobadas por ciclo <span class="text-danger">*</span>
                                    </label>
                                    <input type="number"
                                           min="0" max="20"
                                           name="max_materias_reprobadas_ciclo"
                                           id="matrep-{{ $regla->regla_beneficio_id }}"
                                           class="form-control"
                                           value="{{ old('max_materias_reprobadas_ciclo', $regla->max_materias_reprobadas_ciclo) }}"
                                           required>
                                </div>

                                {{-- UV mínimas cursadas --}}
                                <div class="col-md-6">
                                    <label for="uv-{{ $regla->regla_beneficio_id }}" class="form-label small fw-semibold">
                                        UV mínimas cursadas <span class="text-danger">*</span>
                                    </label>
                                    <input type="number"
                                           min="0" max="500"
                                           name="uv_minimas_cursadas"
                                           id="uv-{{ $regla->regla_beneficio_id }}"
                                           class="form-control"
                                           value="{{ old('uv_minimas_cursadas', $regla->uv_minimas_cursadas) }}"
                                           required>
                                </div>

                                {{-- Activo --}}
                                <div class="col-md-6 d-flex align-items-end">
                                    <div class="form-check form-switch">
                                        <input type="hidden" name="activo" value="0">
                                        <input class="form-check-input"
                                               type="checkbox"
                                               name="activo"
                                               value="1"
                                               id="activo-{{ $regla->regla_beneficio_id }}"
                                               {{ $regla->activo ? 'checked' : '' }}>
                                        <label class="form-check-label small fw-semibold"
                                               for="activo-{{ $regla->regla_beneficio_id }}">
                                            Regla activa
                                        </label>
                                    </div>
                                </div>

                                {{-- Solvencia financiera --}}
                                <div class="col-md-6">
                                    <div class="form-check form-switch mt-4">
                                        <input type="hidden" name="requiere_solvencia_financiera" value="0">
                                        <input class="form-check-input"
                                               type="checkbox"
                                               name="requiere_solvencia_financiera"
                                               value="1"
                                               id="solv-{{ $regla->regla_beneficio_id }}"
                                               {{ $regla->requiere_solvencia_financiera ? 'checked' : '' }}>
                                        <label class="form-check-label small fw-semibold"
                                               for="solv-{{ $regla->regla_beneficio_id }}">
                                            Requiere solvencia financiera
                                        </label>
                                    </div>
                                </div>

                                {{-- Actividades extracurriculares --}}
                                <div class="col-md-6">
                                    <div class="form-check form-switch mt-4">
                                        <input type="hidden" name="requiere_actividades" value="0">
                                        <input class="form-check-input"
                                               type="checkbox"
                                               name="requiere_actividades"
                                               value="1"
                                               id="act-{{ $regla->regla_beneficio_id }}"
                                               {{ $regla->requiere_actividades ? 'checked' : '' }}>
                                        <label class="form-check-label small fw-semibold"
                                               for="act-{{ $regla->regla_beneficio_id }}">
                                            Requiere actividades extracurriculares
                                        </label>
                                    </div>
                                </div>

                                {{-- Observaciones --}}
                                <div class="col-12">
                                    <label for="obs-{{ $regla->regla_beneficio_id }}" class="form-label small fw-semibold">
                                        Observaciones
                                    </label>
                                    <textarea name="observaciones"
                                              id="obs-{{ $regla->regla_beneficio_id }}"
                                              rows="3"
                                              class="form-control"
                                              maxlength="1000"
                                              placeholder="Notas internas sobre esta regla...">{{ old('observaciones', $regla->observaciones) }}</textarea>
                                </div>

                                {{-- vigente_desde oculto (necesario para validar el unique) --}}
                                <input type="hidden" name="vigente_desde" value="{{ $regla->vigente_desde?->format('Y-m-d') }}">

                            </div>
                        </div>
                        <div class="modal-footer bg-light border-top-0">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                Cancelar
                            </button>
                            <button type="submit" class="btn btn-danger">
                                <i class="bi bi-check-circle me-1"></i> Guardar cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="bi bi-sliders fs-1 text-muted d-block mb-3"></i>
                <p class="text-muted mb-0">
                    No hay reglas de beneficio configuradas en el sistema.
                </p>
                <small class="text-muted d-block mt-2">
                    Contactá al administrador para que las configure.
                </small>
            </div>
        </div>
    @endforelse

</div>
@endsection