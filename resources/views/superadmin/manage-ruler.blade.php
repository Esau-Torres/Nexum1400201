@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">

    <div class="mb-4">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-md-between gap-3 p-3 rounded-4 card">
            <div>
                <h3 class="fw-semibold mb-1">
                    REGLAS ACADEMICAS
                </h3>
                <p class="text-muted mb-0 small">
                    Configuración por carrera. Las reglas <strong>no se eliminan</strong>; solo se actualizan.
                </p>
            </div>

            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <button type="button"
                        class="btn bg-danger-subtle text-danger border border-danger px-3 py-2"
                        data-bs-toggle="modal"
                        data-bs-target="#modalConfig"
                        data-mode="create"
                        data-action="{{ route('superadmin.configuracion.store') }}"
                        data-carreras='@json($carreras->map(fn($c) => ["id" => $c->carrera_id, "nombre" => $c->nombre, "facultad" => $c->facultad?->nombre ?? "Sin facultad"]))'>
                    <i class="bi bi-plus-lg"></i> Nueva regla
                </button>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        

        <div>
            <table class="table table-hover align-middle mb-0" id="reglas-table">
                <thead class="table-light">
                    <tr>
                        <th>Carrera</th>
                        <th class="text-center">Nota mín.</th>
                        <th class="text-center">Máx. reprob.</th>
                        <th class="text-center">CUM mín.</th>
                        <th class="text-center">UV Horas Sociales</th>
                        <th class="text-center">UV Egreso</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($carreras as $carrera)
                        @php $config = $configs->get($carrera->carrera_id); @endphp
                        <tr>
                            <td>
                                <strong class="d-block">{{ $carrera->nombre }}</strong>
                                <small class="text-muted">
                                    <i class="bi bi-building me-1"></i>{{ $carrera->facultad?->nombre ?? 'Sin facultad' }}
                                </small>
                            </td>

                            @if($config)
                                <td class="text-center">{{ $config->nota_minima_aprobatoria }}</td>
                                <td class="text-center">{{ $config->max_reprobaciones_materia }}</td>
                                <td class="text-center">{{ $config->cum_minimo_materia_extra }}</td>
                                <td class="text-center">{{ $config->uv_requeridas_horas_sociales }}</td>
                                <td class="text-center">{{ $config->uv_totales_egreso }}</td>
                            @else
                                <td colspan="5" class="text-center">
                                    <span class="badge bg-warning text-dark">
                                        <i class="bi bi-exclamation-triangle me-1"></i>
                                        Sin configuración (usa valores por defecto)
                                    </span>
                                </td>
                            @endif

                            <td class="text-end">
                                <button type="button"
                                        class="btn btn-sm btn-outline-danger"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalConfig"
                                        data-mode="edit"
                                        data-action="{{ route('superadmin.configuracion.update', $carrera->carrera_id) }}"
                                        data-carrera-nombre="{{ $carrera->nombre }}"
                                        data-nota="{{ $config->nota_minima_aprobatoria ?? 6.00 }}"
                                        data-reprob="{{ $config->max_reprobaciones_materia ?? 3 }}"
                                        data-cum="{{ $config->cum_minimo_materia_extra ?? 8.50 }}"
                                        data-uv-hs="{{ $config->uv_requeridas_horas_sociales ?? 80 }}"
                                        data-uv-egreso="{{ $config->uv_totales_egreso ?? 160 }}">
                                    <i class="bi bi-pencil"></i> Editar
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                No hay carreras registradas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="mb-0 fw-bold text-uppercase small text-muted">
                <i class="bi bi-calendar-check me-1"></i> Cierre de Ciclo Lectivo
            </h6>
        </div>
        <div class="card-body">
            @if($cicloAbierto)
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h6 class="fw-bold mb-1">{{ $cicloAbierto->nombre_ciclo }}</h6>
                        <small class="text-muted d-block">
                            <i class="bi bi-calendar-range me-1"></i>
                            {{ $cicloAbierto->fecha_inicio->format('d/m/Y') }} —
                            {{ $cicloAbierto->fecha_fin->format('d/m/Y') }}
                        </small>
                        <span class="badge bg-success mt-2">
                            {{ $cicloAbierto->estado->label() }}
                        </span>
                    </div>

                    <div class="text-end">
                        <div class="alert alert-warning border-0 mb-2 py-2 px-3 small d-inline-block">
                            <i class="bi bi-info-circle me-1"></i>
                            Al cerrar el ciclo se calculará el estado final de cada materia inscrita
                            (aprobada/reprobada) según la configuración vigente.
                        </div>
                        <div>
                            <button type="button"
                                    class="btn btn-danger"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalCerrarCiclo">
                                <i class="bi bi-lock-fill me-1"></i> Cerrar ciclo
                            </button>
                        </div>
                    </div>
                </div>
            @else
                <div class="alert alert-info border-0 mb-0">
                    <i class="bi bi-info-circle-fill me-1"></i>
                    No hay ningún ciclo lectivo en estado <strong>Matrícula Abierta</strong> o <strong>Activo</strong>.
                    @if($cicloMasReciente)
                        Último ciclo: <strong>{{ $cicloMasReciente->nombre_ciclo }}</strong>
                        ({{ $cicloMasReciente->estado->label() }}).
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

<div class="modal fade" id="modalConfig" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST"
              id="formConfig"
              class="modal-content border-0 shadow"
              novalidate>
            @csrf
            <div id="methodField"></div>

            <div class="modal-header bg-danger text-white border-0">
                <h5 class="modal-title fw-bold" id="modalConfigTitle">Configuración académica</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body p-4">
                <div class="alert alert-light border small mb-3">
                    <i class="bi bi-info-circle me-1"></i>
                    Los cambios aplican a las materias <strong>que se inscriban a partir de ahora</strong>.
                </div>

                <div class="mb-3" id="carreraSelectWrapper">
                    <label for="inpCarrera" class="form-label small fw-semibold">Carrera</label>
                    <select name="id_carrera" id="inpCarrera" class="form-select" required>
                        <option value="">Selecciona una carrera</option>
                    </select>
                </div>

                <div class="row g-3">
                    <div class="col-6">
                        <label for="inpNota" class="form-label small fw-semibold">Nota mínima aprobatoria</label>
                        <input type="number" step="0.01" min="0" max="10"
                               name="nota_minima_aprobatoria" id="inpNota"
                               class="form-control @error('nota_minima_aprobatoria') is-invalid @enderror"
                               value="{{ old('nota_minima_aprobatoria') }}"
                               required>
                        @error('nota_minima_aprobatoria')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-6">
                        <label for="inpReprob" class="form-label small fw-semibold">Máx. reprobaciones</label>
                        <input type="number" min="1" max="10"
                               name="max_reprobaciones_materia" id="inpReprob"
                               class="form-control @error('max_reprobaciones_materia') is-invalid @enderror"
                               value="{{ old('max_reprobaciones_materia') }}"
                               required>
                        @error('max_reprobaciones_materia')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-6">
                        <label for="inpCum" class="form-label small fw-semibold">CUM mín. (materia extra)</label>
                        <input type="number" step="0.01" min="0" max="10"
                               name="cum_minimo_materia_extra" id="inpCum"
                               class="form-control @error('cum_minimo_materia_extra') is-invalid @enderror"
                               value="{{ old('cum_minimo_materia_extra') }}"
                               required>
                        @error('cum_minimo_materia_extra')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-6">
                        <label for="inpUvHs" class="form-label small fw-semibold">UV Horas Sociales</label>
                        <input type="number" min="0" max="1000"
                               name="uv_requeridas_horas_sociales" id="inpUvHs"
                               class="form-control @error('uv_requeridas_horas_sociales') is-invalid @enderror"
                               value="{{ old('uv_requeridas_horas_sociales') }}"
                               required>
                        @error('uv_requeridas_horas_sociales')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label for="inpUvEgreso" class="form-label small fw-semibold">UV Totales de Egreso</label>
                        <input type="number" min="100"
                               name="uv_totales_egreso" id="inpUvEgreso"
                               class="form-control @error('uv_totales_egreso') is-invalid @enderror"
                               value="{{ old('uv_totales_egreso') }}"
                               required>
                        @error('uv_totales_egreso')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-danger" id="btnGuardarConfig">
                    <i class="bi bi-check2 me-1"></i> Guardar
                </button>
            </div>
        </form>
    </div>
</div>

@if($cicloAbierto)
<div class="modal fade" id="modalCerrarCiclo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <form method="POST"
              action="{{ route('superadmin.academico.ciclo.cerrar', $cicloAbierto->ciclo_lectivo_id) }}"
              class="modal-content border-0 shadow"
              id="formCerrarCiclo">
            @csrf

            <div class="modal-header bg-danger text-white border-0">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                    Confirmar cierre de ciclo
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body p-4">
                <div class="alert alert-danger border-0 mb-3">
                    <h6 class="fw-bold mb-2">
                        Estás a punto de cerrar el ciclo <br>
                        <span class="text-danger">{{ $cicloAbierto->nombre_ciclo }}</span>
                    </h6>
                    <p class="mb-2 small">
                        Esta acción ejecutará el siguiente proceso <strong>irreversible</strong>:
                    </p>
                    <ul class="mb-0 ps-3 small">
                        <li>Calculará el promedio final de cada materia inscrita.</li>
                        <li>Determinará si cada materia es <strong>APROBADA</strong> o <strong>REPROBADA</strong>.</li>
                        <li>Actualizará el récord académico de cada alumno.</li>
                        <li>Marcará las inscripciones como <strong>COMPLETADA</strong>.</li>
                        <li>Pondrá el ciclo en estado <strong>CERRADO</strong>.</li>
                    </ul>
                </div>

                <div class="alert alert-warning border-0 mb-3 small">
                    <i class="bi bi-info-circle me-1"></i>
                    Los alumnos que hayan reprobado una materia 3 veces requerirán
                    <strong>cambio de carrera</strong> gestionado por Administración Académica.
                </div>

                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="confirmCierre">
                    <label class="form-check-label small" for="confirmCierre">
                        Confirmo que he revisado la información y entiendo que esta acción
                        <strong>no se puede deshacer</strong>.
                    </label>
                </div>
            </div>

            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-danger" id="btnCerrarCiclo" disabled>
                    <i class="bi bi-lock-fill me-1"></i> Sí, cerrar ciclo
                </button>
            </div>
        </form>
    </div>
</div>
@endif

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {

        const modalConfig   = document.getElementById('modalConfig');
        const formConfig    = document.getElementById('formConfig');
        const titleConfig   = document.getElementById('modalConfigTitle');
        const btnGuardar    = document.getElementById('btnGuardarConfig');
        const methodField   = document.getElementById('methodField');
        const carreraSelect = document.getElementById('inpCarrera');
        const carrerasData  = @json($carreras->map(fn($c) => ["id" => $c->carrera_id, "nombre" => $c->nombre, "facultad" => $c->facultad?->nombre ?? "Sin facultad"]));

        function resetForm() {
            formConfig.reset();
            methodField.innerHTML = '';
            document.querySelectorAll('#formConfig .is-invalid').forEach(el => el.classList.remove('is-invalid'));
            document.querySelectorAll('#formConfig .invalid-feedback').forEach(el => el.remove());
        }

        function renderCarreraOptions() {
            carreraSelect.innerHTML = '<option value="">Selecciona una carrera</option>';
            carrerasData.forEach(c => {
                const opt = document.createElement('option');
                opt.value = c.id;
                opt.textContent = `${c.nombre} — ${c.facultad}`;
                carreraSelect.appendChild(opt);
            });
        }

        modalConfig.addEventListener('show.bs.modal', function (event) {
            const btn = event.relatedTarget;
            if (!btn) return;

            resetForm();
            const mode = btn.getAttribute('data-mode') || 'edit';
            const action = btn.getAttribute('data-action');

            if (mode === 'create') {
                titleConfig.textContent = 'Nueva configuración académica';
                formConfig.action = action;
                renderCarreraOptions();
                document.getElementById('carreraSelectWrapper').classList.remove('d-none');
                btnGuardar.innerHTML = '<i class="bi bi-check2 me-1"></i> Crear';
            } else {
                titleConfig.textContent = `Editar configuración: ${btn.getAttribute('data-carrera-nombre')}`;
                formConfig.action = action;
                methodField.innerHTML = '<input type="hidden" name="_method" value="PUT">';
                document.getElementById('carreraSelectWrapper').classList.add('d-none');

                document.getElementById('inpNota').value     = btn.getAttribute('data-nota');
                document.getElementById('inpReprob').value   = btn.getAttribute('data-reprob');
                document.getElementById('inpCum').value      = btn.getAttribute('data-cum');
                document.getElementById('inpUvHs').value     = btn.getAttribute('data-uv-hs');
                document.getElementById('inpUvEgreso').value = btn.getAttribute('data-uv-egreso');
                btnGuardar.innerHTML = '<i class="bi bi-check2 me-1"></i> Guardar cambios';
            }

            btnGuardar.disabled = false;
        });

        formConfig.addEventListener('submit', function () {
            btnGuardar.disabled = true;
            btnGuardar.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Procesando...';
        });

        const confirmCheck = document.getElementById('confirmCierre');
        const btnCerrar    = document.getElementById('btnCerrarCiclo');
        const formCerrar   = document.getElementById('formCerrarCiclo');

        if (confirmCheck && btnCerrar) {
            confirmCheck.addEventListener('change', function () {
                btnCerrar.disabled = !this.checked;
            });
        }

        if (formCerrar) {
            formCerrar.addEventListener('submit', function () {
                btnCerrar.disabled = true;
                btnCerrar.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Procesando...';
            });
        }
    });

</script>
@endpush
@endsection