
@extends('layouts.app')

@section('title', 'Reglas Académicas - NEXUM')

@push('styles')
    @vite('resources/css/reglas-academicas.css')
@endpush

@section('content')
<div class="ra-page container-fluid">

    {{-- ENCABEZADO --}}
    <section class="ra-header">
        <div class="ra-header-content">
            <div class="ra-header-icon">
                <i class="bi bi-sliders2"></i>
            </div>

            <div class="ra-header-text">
                <span class="ra-eyebrow">ADMINISTRACIÓN ACADÉMICA</span>
                <h1>REGLAS ACADÉMICAS</h1>
                <p>
                    Administra los parámetros académicos aplicables a cada carrera
                    de la institución.
                </p>
            </div>
        </div>

        <button type="button"
                class="ra-btn ra-btn-primary"
                data-bs-toggle="modal"
                data-bs-target="#modalConfig"
                data-mode="create"
                data-action="{{ route('superadmin.configuracion.store') }}"
                data-carreras='@json($carreras->map(fn($c) => ["id" => $c->carrera_id, "nombre" => $c->nombre, "facultad" => $c->facultad?->nombre ?? "Sin facultad"]))'>
            <i class="bi bi-plus-lg"></i>
            <span>Nueva regla</span>
        </button>
    </section>

    {{-- INDICADORES --}}
    <section class="ra-stats">
        <article class="ra-stat-card">
            <div class="ra-stat-icon ra-stat-icon-neutral">
                <i class="bi bi-mortarboard"></i>
            </div>
            <div class="ra-stat-info">
                <span class="ra-stat-label">Carreras registradas</span>
                <strong class="ra-stat-value">{{ $carreras->count() }}</strong>
                <span class="ra-stat-description">En el catálogo académico</span>
            </div>
        </article>

        <article class="ra-stat-card">
            <div class="ra-stat-icon ra-stat-icon-success">
                <i class="bi bi-check2-circle"></i>
            </div>
            <div class="ra-stat-info">
                <span class="ra-stat-label">Con configuración</span>
                <strong class="ra-stat-value">{{ $configs->count() }}</strong>
                <span class="ra-stat-description">Reglas personalizadas</span>
            </div>
        </article>

        <article class="ra-stat-card">
            <div class="ra-stat-icon ra-stat-icon-warning">
                <i class="bi bi-exclamation-circle"></i>
            </div>
            <div class="ra-stat-info">
                <span class="ra-stat-label">Sin configuración</span>
                <strong class="ra-stat-value">{{ $carreras->filter(fn($c) => !$configs->has($c->carrera_id))->count() }}</strong>
                <span class="ra-stat-description">Utilizan valores por defecto</span>
            </div>
        </article>
    </section>

    {{-- TABLA DE REGLAS --}}
    <section class="ra-panel">
        <div class="ra-panel-header">
            <div>
                <span class="ra-section-eyebrow">PARÁMETROS INSTITUCIONALES</span>
                <h2>Configuración por carrera</h2>
                <p>Consulta y actualiza los criterios académicos de cada programa.</p>
            </div>

            <div class="ra-panel-counter">
                <i class="bi bi-list-ul"></i>
                {{ $carreras->count() }} carreras
            </div>
        </div>

        <div class="ra-table-container">
            <table class="table ra-table align-middle mb-0" id="reglas-table">
                <thead>
                    <tr>
                        <th class="ra-col-career">Carrera</th>
                        <th class="text-center">Nota mín.</th>
                        <th class="text-center">Máx. reprob.</th>
                        <th class="text-center">CUM mín.</th>
                        <th class="text-center">UV horas sociales</th>
                        <th class="text-center">UV egreso</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($carreras as $carrera)
                        @php $config = $configs->get($carrera->carrera_id); @endphp

                        <tr>
                            <td class="ra-career-cell">
                                <div class="ra-career-name">
                                    <span class="ra-career-avatar">
                                        <i class="bi bi-mortarboard"></i>
                                    </span>

                                    <div>
                                        <strong>{{ $carrera->nombre }}</strong>
                                        <small>
                                            <i class="bi bi-building"></i>
                                            {{ $carrera->facultad?->nombre ?? 'Sin facultad' }}
                                        </small>
                                    </div>
                                </div>
                            </td>

                            @if($config)
                                <td class="text-center">
                                    <span class="ra-value">{{ $config->nota_minima_aprobatoria }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="ra-value">{{ $config->max_reprobaciones_materia }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="ra-value">{{ $config->cum_minimo_materia_extra }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="ra-value">{{ $config->uv_requeridas_horas_sociales }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="ra-value">{{ $config->uv_totales_egreso }}</span>
                                </td>
                            @else
                                <td colspan="5" class="text-center">
                                    <span class="ra-default-status">
                                        <i class="bi bi-info-circle"></i>
                                        Valores por defecto
                                    </span>
                                </td>
                            @endif

                            <td class="text-end">
                                <button type="button"
                                        class="ra-btn ra-btn-outline"
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
                                    <i class="bi bi-pencil-square"></i>
                                    <span>Editar</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="ra-empty-state">
                                    <div class="ra-empty-icon">
                                        <i class="bi bi-journal-x"></i>
                                    </div>
                                    <strong>No hay carreras registradas</strong>
                                    <p>No existen carreras disponibles para configurar.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="ra-table-footer">
            <i class="bi bi-shield-check"></i>
            Las reglas no se eliminan. Puedes actualizar su configuración cuando sea necesario.
        </div>
    </section>

    {{-- CIERRE DE CICLO --}}
    <section class="ra-cycle-panel">
        <div class="ra-cycle-header">
            <div class="ra-cycle-heading">
                <div class="ra-cycle-icon">
                    <i class="bi bi-calendar2-check"></i>
                </div>
                <div>
                    <span class="ra-section-eyebrow">GESTIÓN DEL PERÍODO ACADÉMICO</span>
                    <h2>Cierre de ciclo lectivo</h2>
                    <p>Consulta el estado del ciclo y gestiona su cierre.</p>
                </div>
            </div>

            @if($cicloAbierto)
                <span class="ra-cycle-status ra-cycle-status-active">
                    <span></span>
                    {{ $cicloAbierto->estado->label() }}
                </span>
            @else
                <span class="ra-cycle-status ra-cycle-status-inactive">
                    <span></span>
                    Sin ciclo abierto
                </span>
            @endif
        </div>

        <div class="ra-cycle-body">
            @if($cicloAbierto)
                <div class="ra-cycle-information">
                    <div class="ra-cycle-name">
                        <span>CICLO ACTUAL</span>
                        <h3>{{ $cicloAbierto->nombre_ciclo }}</h3>
                    </div>

                    <div class="ra-cycle-date">
                        <i class="bi bi-calendar-range"></i>
                        <div>
                            <span>Período académico</span>
                            <strong>
                                {{ $cicloAbierto->fecha_inicio->format('d/m/Y') }}
                                — 
                                {{ $cicloAbierto->fecha_fin->format('d/m/Y') }}
                            </strong>
                        </div>
                    </div>
                </div>

                <div class="ra-cycle-warning">
                    <i class="bi bi-info-circle-fill"></i>
                    <p>
                        Al cerrar el ciclo se calculará el estado final de cada materia inscrita
                        (aprobada/reprobada), de acuerdo con la configuración académica vigente.
                    </p>
                </div>

                <div class="ra-cycle-action">
                    <button type="button"
                            class="ra-btn ra-btn-danger"
                            data-bs-toggle="modal"
                            data-bs-target="#modalCerrarCiclo">
                        <i class="bi bi-lock-fill"></i>
                        Cerrar ciclo lectivo
                    </button>
                </div>
            @else
                <div class="ra-cycle-empty">
                    <div class="ra-cycle-empty-icon">
                        <i class="bi bi-calendar-x"></i>
                    </div>
                    <div>
                        <strong>No hay un ciclo lectivo disponible para cierre</strong>
                        <p>
                            No existe ningún ciclo en estado
                            <strong>Matrícula Abierta</strong> o <strong>Activo</strong>.
                        </p>

                        @if($cicloMasReciente)
                            <span class="ra-last-cycle">
                                Último ciclo:
                                <strong>{{ $cicloMasReciente->nombre_ciclo }}</strong>
                                ({{ $cicloMasReciente->estado->label() }}).
                            </span>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </section>
</div>

{{-- MODAL DE CONFIGURACIÓN --}}
<div class="modal fade ra-modal" id="modalConfig" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST"
              id="formConfig"
              class="modal-content ra-modal-content"
              novalidate>
            @csrf
            <div id="methodField"></div>

            <div class="modal-header ra-modal-header">
                <div class="ra-modal-title-icon">
                    <i class="bi bi-sliders2"></i>
                </div>
                <div>
                    <span>PARÁMETROS ACADÉMICOS</span>
                    <h5 class="modal-title" id="modalConfigTitle">Configuración académica</h5>
                </div>
                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Cerrar"></button>
            </div>

            <div class="modal-body ra-modal-body">
                <div class="ra-modal-notice">
                    <i class="bi bi-info-circle-fill"></i>
                    <p>
                        Los cambios aplican a las materias
                        <strong>que se inscriban a partir de ahora</strong>.
                    </p>
                </div>

                <div class="mb-3" id="carreraSelectWrapper">
                    <label for="inpCarrera" class="ra-form-label">Carrera</label>
                    <select name="id_carrera" id="inpCarrera" class="form-select ra-form-control" required>
                        <option value="">Selecciona una carrera</option>
                    </select>
                </div>

                <div class="ra-form-section-title">
                    <i class="bi bi-clipboard-data"></i>
                    Criterios de evaluación
                </div>

                <div class="row g-3">
                    <div class="col-12 col-sm-6">
                        <label for="inpNota" class="ra-form-label">Nota mínima aprobatoria</label>
                        <div class="ra-input-with-icon">
                            <i class="bi bi-award"></i>
                            <input type="text"
                                   inputmode="decimal"
                                   pattern="^(10(\.0{1,2})?|[0-9](\.[0-9]{1,2})?)$"
                                   maxlength="5"
                                   placeholder="Ej. 8.5"
                                   name="nota_minima_aprobatoria"
                                   id="inpNota"
                                   class="form-control ra-form-control @error('nota_minima_aprobatoria') is-invalid @enderror"
                                   value="{{ old('nota_minima_aprobatoria') }}"
                                   required>
                        </div>
                        @error('nota_minima_aprobatoria')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 col-sm-6">
                        <label for="inpReprob" class="ra-form-label">Máx. reprobaciones</label>
                        <div class="ra-input-with-icon">
                            <i class="bi bi-arrow-repeat"></i>
                            <input type="text"
                                   inputmode="decimal"
                                   pattern="[0-9]{0,3}"
                                   maxlength="1"
                                   placeholder="Máx. 3"
                                   name="max_reprobaciones_materia"
                                   id="inpReprob"
                                   class="form-control ra-form-control @error('max_reprobaciones_materia') is-invalid @enderror"
                                   value="{{ old('max_reprobaciones_materia') }}"
                                   required>
                        </div>
                        @error('max_reprobaciones_materia')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 col-sm-6">
                        <label for="inpCum" class="ra-form-label">CUM mín. (materia extra)</label>
                        <div class="ra-input-with-icon">
                            <i class="bi bi-graph-up"></i>
                            <input type="text"
                                   inputmode="decimal"
                                   pattern="^(10(\.0{1,2})?|[0-9](\.[0-9]{1,2})?)$"
                                   maxlength="5"
                                   placeholder="Ej. 8.5"
                                   name="cum_minimo_materia_extra"
                                   id="inpCum"
                                   class="form-control ra-form-control @error('cum_minimo_materia_extra') is-invalid @enderror"
                                   value="{{ old('cum_minimo_materia_extra') }}"
                                   required>
                        </div>
                        @error('cum_minimo_materia_extra')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="ra-form-section-title ra-form-section-spaced">
                    <i class="bi bi-mortarboard"></i>
                    Requisitos de graduación
                </div>

                <div class="row g-3">
                    <div class="col-12 col-sm-6">
                        <label for="inpUvHs" class="ra-form-label">UV horas sociales</label>
                        <div class="ra-input-with-icon">
                            <i class="bi bi-people"></i>
                            <input type="number"
                                   min="0"
                                   max="1000"
                                   name="uv_requeridas_horas_sociales"
                                   id="inpUvHs"
                                   class="form-control ra-form-control @error('uv_requeridas_horas_sociales') is-invalid @enderror"
                                   value="{{ old('uv_requeridas_horas_sociales') }}"
                                   required>
                        </div>
                        @error('uv_requeridas_horas_sociales')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 col-sm-6">
                        <label for="inpUvEgreso" class="ra-form-label">UV totales de egreso</label>
                        <div class="ra-input-with-icon">
                            <i class="bi bi-book"></i>
                            <input type="number"
                                   min="100"
                                   name="uv_totales_egreso"
                                   id="inpUvEgreso"
                                   class="form-control ra-form-control @error('uv_totales_egreso') is-invalid @enderror"
                                   value="{{ old('uv_totales_egreso') }}"
                                   required>
                        </div>
                        @error('uv_totales_egreso')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="modal-footer ra-modal-footer">
                <button type="button" class="ra-btn ra-btn-light" data-bs-dismiss="modal">
                    Cancelar
                </button>
                <button type="submit" class="ra-btn ra-btn-primary" id="btnGuardarConfig">
                    <i class="bi bi-check2"></i>
                    Guardar
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL DE CIERRE DE CICLO --}}
@if($cicloAbierto)
<div class="modal fade ra-modal" id="modalCerrarCiclo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <form method="POST"
              action="{{ route('superadmin.academico.ciclo.cerrar', $cicloAbierto->ciclo_lectivo_id) }}"
              class="modal-content ra-modal-content"
              id="formCerrarCiclo">
            @csrf

            <div class="modal-header ra-modal-header ra-modal-header-danger">
                <div class="ra-modal-title-icon">
                    <i class="bi bi-lock-fill"></i>
                </div>
                <div>
                    <span>GESTIÓN ACADÉMICA</span>
                    <h5 class="modal-title">Confirmar cierre de ciclo</h5>
                </div>
                <button type="button"
                        class="btn-close btn-close-white"
                        data-bs-dismiss="modal"
                        aria-label="Cerrar"></button>
            </div>

            <div class="modal-body ra-modal-body">
                <div class="ra-close-warning">
                    <div class="ra-close-warning-title">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <span>Acción irreversible</span>
                    </div>

                    <h6>Estás a punto de cerrar el ciclo:</h6>
                    <strong class="ra-close-cycle-name">{{ $cicloAbierto->nombre_ciclo }}</strong>

                    <p>Al confirmar, se ejecutarán los siguientes procesos:</p>

                    <ul>
                        <li>Calculará el promedio final de cada materia inscrita.</li>
                        <li>Determinará si cada materia es <strong>APROBADA</strong> o <strong>REPROBADA</strong>.</li>
                        <li>Actualizará el récord académico de cada alumno.</li>
                        <li>Marcará las inscripciones como <strong>COMPLETADA</strong>.</li>
                        <li>Pondrá el ciclo en estado <strong>CERRADO</strong>.</li>
                    </ul>
                </div>

                <div class="ra-close-note">
                    <i class="bi bi-info-circle-fill"></i>
                    <p>
                        Los alumnos que hayan reprobado una materia 3 veces requerirán
                        <strong>cambio de carrera</strong> gestionado por Administración Académica.
                    </p>
                </div>

                <label class="ra-confirm-check" for="confirmCierre">
                    <input class="form-check-input" type="checkbox" id="confirmCierre">
                    <span>
                        Confirmo que he revisado la información y entiendo que esta acción
                        <strong>no se puede deshacer</strong>.
                    </span>
                </label>
            </div>

            <div class="modal-footer ra-modal-footer">
                <button type="button" class="ra-btn ra-btn-light" data-bs-dismiss="modal">
                    Cancelar
                </button>
                <button type="submit" class="ra-btn ra-btn-danger" id="btnCerrarCiclo" disabled>
                    <i class="bi bi-lock-fill"></i>
                    Sí, cerrar ciclo
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
