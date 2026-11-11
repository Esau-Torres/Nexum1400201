@extends('layouts.app')

@section('content')
<div class="container-fluid p-4 p-lg-5">

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- HEADER                                                       --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-md-between gap-3 p-3 mb-4 rounded-4 card">
        <div class="d-flex align-items-center">
            <div class="px-3 py-2 rounded-3 bg-danger bg-opacity-10" style="width:48px;height:48px;">
                <i class="fa fa-user-graduate fs-4 text-danger"></i>
            </div>
            <div class="px-3">
                <h2 class="h4 fw-bold mb-0">REQUISITOS DE EGRESO</h2>
                <p class="text-muted mb-0 small">
                    Validación integral del proceso de egreso de los estudiantes.
                </p>
            </div>
        </div>
        <span class="badge bg-danger p-2">
            {{ $registros->count() }} estudiante(s)
        </span>
    </div>

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- ALERTAS                                                      --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm">
            <i class="bi bi-check-circle-fill me-1"></i>{{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm">
            <i class="bi bi-x-circle-fill me-1"></i>{{ session('error') }}
        </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- TABLA                                                        --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <div class="card border-0 shadow-sm">
        <div class="text-medium">
            <table class="table table-hover align-middle mb-0" id="tableEgreso">
                <thead class="table-light">
                    <tr>
                        <th>Carnet</th>
                        <th>Alumno</th>
                        <th>Carrera</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center">Cumple</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($registros as $row)
                        @php
                            $alumno     = $row['alumno'];
                            $req        = $row['req'];
                            $validacion = $row['validacion'];
                            $esEgresado = $req->estado_egreso->value === 'EGRESADO';
                        @endphp
                        <tr>
                            <td><code class="small">{{ $alumno->codigo_estudiante }}</code></td>
                            <td>
                                <div class="fw-semibold small">{{ $alumno->usuario?->name ?? 'Sin nombre' }}</div>
                                <div class="text-muted" style="font-size: 0.72rem;">
                                    {{ $alumno->correo_institucional }}
                                </div>
                            </td>
                            <td>
                                <small class="fw-semibold">{{ $alumno->carrera?->nombre }}</small><br>
                                <small class="text-muted">{{ $alumno->carrera?->facultad?->nombre }}</small>
                            </td>
                            <td class="text-center">
                                <span class="badge {{ $req->estado_egreso->badge() }}">
                                    {{ $req->estado_egreso->label() }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if($validacion['puede'])
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle-fill"></i> Apto
                                    </span>
                                @else
                                    <span class="badge bg-danger">
                                        {{ count($validacion['bloqueadores']) }} pendiente(s)
                                    </span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <button type="button"
                                            class="btn btn-sm btn-outline-secondary"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalDetalle"
                                            data-alumno-id="{{ $alumno->alumno_id }}"
                                            data-nombre="{{ $alumno->usuario?->name ?? 'Sin nombre' }}"
                                            data-carnet="{{ $alumno->codigo_estudiante }}"
                                            data-carrera="{{ $alumno->carrera?->nombre }}"
                                            data-facultad="{{ $alumno->carrera?->facultad?->nombre }}"
                                            data-estado="{{ $req->estado_egreso->value }}"
                                            data-estado-label="{{ $req->estado_egreso->label() }}"
                                            data-estado-badge="{{ $req->estado_egreso->badge() }}"
                                            data-puede="{{ $validacion['puede'] ? '1' : '0' }}"
                                            data-bloqueadores='@json($validacion['bloqueadores'])'
                                            data-detalles='@json($validacion['detalles'])'
                                            data-registrar-url="{{ route('admin-academico.egresos.registrar', $alumno->alumno_id) }}">
                                        <i class="bi bi-eye"></i> Ver
                                    </button>

                                    @if(!$esEgresado)
                                        <button type="button"
                                                class="btn btn-sm btn-danger"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalGestionar"
                                                data-alumno-id="{{ $alumno->alumno_id }}"
                                                data-nombre="{{ $alumno->usuario?->name }}"
                                                data-carnet="{{ $alumno->codigo_estudiante }}"
                                                data-nivel="{{ $row['nivel'] }}"
                                                data-uv-ganadas="{{ $row['uv_ganadas'] }}"
                                                data-uv-minimas="{{ $row['uv_minimas'] }}"
                                                data-puede-hs="{{ $row['puede_hs'] ? '1' : '0' }}"
                                                data-puede-pas="{{ $row['puede_pas'] ? '1' : '0' }}"
                                                data-horas="{{ $req->horas_sociales_completadas }}"
                                                data-horas-aprobadas="{{ $req->horas_sociales_aprobadas ? '1' : '0' }}"
                                                data-doc-social="{{ $req->documento_constancia_social ? '1' : '0' }}"
                                                data-requiere-pasantia="{{ $req->requiere_pasantia ? '1' : '0' }}"
                                                data-pasantia-aprobada="{{ $req->pasantia_aprobada ? '1' : '0' }}"
                                                data-institucion="{{ $req->institucion_pasantia }}"
                                                data-doc-pasantia="{{ $req->documento_pasantia ? '1' : '0' }}"
                                                data-url-hs="{{ route('admin-academico.egresos.horas-sociales', $alumno->alumno_id) }}"
                                                data-url-pas="{{ route('admin-academico.egresos.pasantia', $alumno->alumno_id) }}"
                                                data-url-doc-social="{{ route('admin-academico.egresos.documento', [$alumno->alumno_id, 'social']) }}"
                                                data-url-doc-pas="{{ route('admin-academico.egresos.documento', [$alumno->alumno_id, 'pasantia']) }}"
                                                data-solvencia="{{ $req->solvencia_financiera ? '1' : '0' }}"
                                                data-url-solvencia="{{ route('admin-academico.egresos.solvencia', $alumno->alumno_id) }}"
                                                data-tiene-arancel-ss="{{ $row['tiene_arancel_ss'] ? '1' : '0' }}">
                                            <i class="bi bi-gear-fill"></i> Gestionar
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                No hay estudiantes registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════ --}}
{{-- MODAL: DETALLE DE VALIDACIÓN                                 --}}
{{-- ═══════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalDetalle" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content border-0 shadow">

            <div class="modal-header bg-danger text-white border-0">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-mortarboard-fill me-1"></i> Validación de Egreso
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body p-4">

                {{-- Encabezado --}}
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <span class="badge" id="mEstadoBadge"></span>
                        <h6 class="fw-bold mt-2 mb-1" id="mNombre"></h6>
                        <small class="text-muted d-block">
                            <i class="bi bi-person-vcard me-1"></i><span id="mCarnet"></span>
                        </small>
                        <small class="text-muted d-block">
                            <i class="bi bi-building me-1"></i><span id="mCarrera"></span>
                        </small>
                    </div>
                </div>

                <hr>

                {{-- Resumen --}}
                <div id="mResumen" class="alert border-0 small mb-3"></div>

                {{-- Requisitos --}}
                <h6 class="fw-bold text-uppercase small text-muted mb-2">Requisitos de egreso</h6>
                <div id="mRequisitos" class="d-flex flex-column gap-2 mb-3"></div>

                {{-- Bloqueadores --}}
                <div id="mBloqueadoresWrapper" class="d-none">
                    <h6 class="fw-bold text-uppercase small text-muted mb-2">Pendientes</h6>
                    <div class="alert alert-warning border-0 small">
                        <ul class="mb-0 ps-3" id="mBloqueadores"></ul>
                    </div>
                </div>

                {{-- Registrar como Egresado --}}
                <div id="wrapperRegistrar" class="d-none">
                    <hr>
                    <h6 class="fw-bold text-uppercase small text-muted mb-2">Registrar como Egresado</h6>

                    <form method="POST" id="formRegistrar" novalidate>
                        @csrf

                        <div class="alert alert-success border-0 small mb-3">
                            <i class="bi bi-check-circle-fill me-1"></i>
                            El alumno cumple con todos los requisitos. Al registrarlo:
                            <ul class="mb-0 ps-3 mt-1">
                                <li>Su estado cambiará de <strong>EN_PROCESO</strong> a <strong>EGRESADO</strong>.</li>
                                <li>Se registrará la fecha de egreso y quién aprobó.</li>
                                <li>Podrá iniciar el proceso de graduación.</li>
                            </ul>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="confirmarEgreso">
                            <label class="form-check-label small" for="confirmarEgreso">
                                Confirmo que he revisado los requisitos y apruebo el egreso.
                            </label>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-danger" id="btnRegistrar" disabled>
                                <i class="bi bi-mortarboard-fill me-1"></i> Registrar como Egresado
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════ --}}
{{-- MODAL: GESTIÓN (Horas sociales, pasantía, documentos)        --}}
{{-- ═══════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalGestionar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content border-0 shadow">

            <div class="modal-header bg-danger text-white border-0">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-gear-fill me-1"></i> Gestionar Egreso
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-0">

                {{-- Encabezado del alumno --}}
                <div class="p-4 pb-2 border-bottom">
                    <h6 class="fw-bold mb-1" id="gNombre"></h6>
                    <small class="text-muted">
                        <i class="bi bi-person-vcard me-1"></i><span id="gCarnet"></span>
                        · Nivel: <span id="gNivel" class="text-uppercase"></span>
                    </small>
                    <div class="mt-2">
                        <span class="badge bg-light text-dark">
                            UV: <span id="gUvGanadas"></span> / <span id="gUvMinimas"></span>
                        </span>
                        <span id="gAvisoUv" class="badge bg-warning text-dark d-none">
                            <i class="bi bi-exclamation-triangle-fill"></i> UV insuficientes
                        </span>
                    </div>
                </div>

                {{-- Tabs --}}
                <ul class="nav nav-tabs px-4 pt-3" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabDatos" type="button">
                            <i class="bi bi-pencil-square me-1"></i> Datos
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabDocumentos" type="button">
                            <i class="bi bi-folder-fill me-1"></i> Documentos
                        </button>
                    </li>
                </ul>

                <div class="tab-content p-4">

                    {{-- ══════════ TAB DATOS ══════════ --}}
                    <div class="tab-pane fade show active" id="tabDatos">

                        {{-- SOLVENCIA FINANCIERA --}}
                        <h6 class="fw-bold text-uppercase small text-muted mb-2">
                            <i class="bi bi-cash-coin me-1"></i> Solvencia Financiera
                        </h6>

                        <div id="solvenciaEstado" class="alert alert-light border small mb-2">
                            <i class="bi bi-hourglass-split me-1"></i> Calculando...
                        </div>

                        <div id="solvenciaAdeudos" class="alert alert-danger border-0 small d-none mb-2">
                            <strong>El alumno no está solvente:</strong>
                            <ul class="mb-0 ps-3" id="listaAdeudos"></ul>
                        </div>

                        <form method="POST" id="formSolvencia" novalidate class="mb-3">
                            @csrf
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    El sistema validará que <strong>todos los ciclos</strong> estén cancelados
                                    (matrícula + cuotas mensuales) antes de marcar como solvente.
                                </small>
                                <button type="submit" class="btn btn-danger btn-sm btn-solvencia">
                                    <i class="bi bi-check2-circle me-1"></i> Marcar solvencia
                                </button>
                            </div>
                        </form>

                        <hr class="my-4">

                        {{-- Horas Sociales --}}
                        <h6 class="fw-bold text-uppercase small text-muted mb-2">
                            <i class="bi bi-people-fill me-1"></i> Horas Sociales
                        </h6>

                        <div id="bloqueoHs" class="alert alert-warning border-0 small d-none">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i>
                            No puedes gestionar horas sociales hasta cumplir las UV mínimas.
                        </div>

                        <form method="POST" id="formHs" enctype="multipart/form-data" novalidate>
                            @csrf
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">Horas completadas</label>
                                    <input type="number" name="horas_sociales_completadas"
                                           id="gHoras" min="0" max="2000"
                                           class="form-control form-control-sm" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">¿Aprobadas?</label>
                                    <select name="horas_sociales_aprobadas" id="gHorasAprobadas"
                                            class="form-select form-select-sm" required>
                                        <option value="0">No</option>
                                        <option value="1">Sí</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">Constancia (PDF/img)</label>
                                    <input type="file" name="documento" class="form-control form-control-sm"
                                           accept=".pdf,.jpg,.jpeg,.png">
                                </div>
                            </div>
                            <div class="text-end mt-2">
                                <button type="submit" class="btn btn-danger btn-sm btn-hs">
                                    <i class="bi bi-save me-1"></i> Guardar horas
                                </button>
                            </div>
                        </form>

                        <hr class="my-4">

                        {{-- Pasantía --}}
                        <h6 class="fw-bold text-uppercase small text-muted mb-2">
                            <i class="bi bi-briefcase-fill me-1"></i> Pasantía <span class="text-muted small">(si la carrera aplica)</span>
                        </h6>

                        <div id="bloqueoPas" class="alert alert-warning border-0 small d-none">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i>
                            No puedes gestionar la pasantía hasta cumplir las UV mínimas.
                        </div>

                        <form method="POST" id="formPas" enctype="multipart/form-data" novalidate>
                            @csrf
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">¿Requiere pasantía?</label>
                                    <select name="requiere_pasantia" id="gRequierePasantia"
                                            class="form-select form-select-sm" required>
                                        <option value="0">No</option>
                                        <option value="1">Sí</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">¿Aprobada?</label>
                                    <select name="pasantia_aprobada" id="gPasantiaAprobada"
                                            class="form-select form-select-sm" required>
                                        <option value="0">No</option>
                                        <option value="1">Sí</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">Institución</label>
                                    <input type="text" name="institucion_pasantia" id="gInstitucion"
                                           maxlength="150" class="form-control form-control-sm">
                                </div>
                                <div class="col-md-12 mt-2">
                                    <label class="form-label small fw-semibold">Constancia de pasantía</label>
                                    <input type="file" name="documento" class="form-control form-control-sm"
                                           accept=".pdf,.jpg,.jpeg,.png">
                                </div>
                            </div>
                            <div class="text-end mt-2">
                                <button type="submit" class="btn btn-danger btn-sm btn-pas">
                                    <i class="bi bi-save me-1"></i> Guardar pasantía
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- ══════════ TAB DOCUMENTOS ══════════ --}}
                    <div class="tab-pane fade" id="tabDocumentos">
                        <div class="row g-3">

                            {{-- Constancia Social --}}
                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body text-center">
                                        <i class="bi bi-file-earmark-person-fill fs-1 text-danger mb-2"></i>
                                        <h6 class="fw-bold">Constancia Social</h6>
                                        <p class="text-muted small mb-2" id="docSocialInfo">
                                            No hay documento subido.
                                        </p>
                                        <a href="#"
                                           id="btnVerDocSocial"
                                           target="_blank"
                                           rel="noopener noreferrer"
                                           class="btn btn-sm btn-outline-danger d-none">
                                            <i class="bi bi-eye me-1"></i> Ver documento
                                        </a>
                                    </div>
                                </div>
                            </div>

                            {{-- Constancia de Pasantía --}}
                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body text-center">
                                        <i class="bi bi-file-earmark-medical-fill fs-1 text-danger mb-2"></i>
                                        <h6 class="fw-bold">Constancia de Pasantía</h6>
                                        <p class="text-muted small mb-2" id="docPasInfo">
                                            No hay documento subido.
                                        </p>
                                        <a href="#"
                                           id="btnVerDocPas"
                                           target="_blank"
                                           rel="noopener noreferrer"
                                           class="btn btn-sm btn-outline-danger d-none">
                                            <i class="bi bi-eye me-1"></i> Ver documento
                                        </a>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════ --}}
{{-- SCRIPTS                                                      --}}
{{-- ═══════════════════════════════════════════════════════════════ --}}
@push('scripts')
<script>
/* ──────────────────────────────────────────────────────────────
 *  MÓDULO 1 · MODAL DETALLE
 * ────────────────────────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', function () {
    const modalDetalle = document.getElementById('modalDetalle');
    if (!modalDetalle) return;

    const formRegistrar = document.getElementById('formRegistrar');
    const checkbox      = document.getElementById('confirmarEgreso');
    const btnRegistrar  = document.getElementById('btnRegistrar');
    const wrapperReg    = document.getElementById('wrapperRegistrar');

    modalDetalle.addEventListener('show.bs.modal', function (event) {
        const btn = event.relatedTarget;
        if (!btn) return;

        const nombre       = btn.getAttribute('data-nombre');
        const carnet       = btn.getAttribute('data-carnet');
        const carrera      = btn.getAttribute('data-carrera');
        const facultad     = btn.getAttribute('data-facultad');
        const estadoBadge  = btn.getAttribute('data-estado-badge');
        const estadoLabel  = btn.getAttribute('data-estado-label');
        const puede        = btn.getAttribute('data-puede') === '1';
        const estadoValue  = btn.getAttribute('data-estado');
        const registrarUrl = btn.getAttribute('data-registrar-url');
        const bloqueadores = JSON.parse(btn.getAttribute('data-bloqueadores') || '[]');
        const detalles     = JSON.parse(btn.getAttribute('data-detalles') || '{}');

        document.getElementById('mNombre').textContent  = nombre;
        document.getElementById('mCarnet').textContent  = carnet;
        document.getElementById('mCarrera').textContent = carrera + (facultad ? ' · ' + facultad : '');

        const badge = document.getElementById('mEstadoBadge');
        badge.className   = 'badge ' + estadoBadge;
        badge.textContent = estadoLabel;

        /* Resumen */
        const resumen = document.getElementById('mResumen');
        if (puede) {
            resumen.className = 'alert alert-success border-0 small mb-3';
            resumen.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i><strong>Apto para egresar.</strong> Cumple con todos los requisitos.';
        } else {
            resumen.className = 'alert alert-warning border-0 small mb-3';
            resumen.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i><strong>No apto aún.</strong> Revisa los pendientes.';
        }

        /* Requisitos */
        const reqWrapper = document.getElementById('mRequisitos');
        reqWrapper.innerHTML = '';

        const items = [];

        if (detalles.materias) {
            items.push({
                titulo: 'Materias aprobadas',
                valor:  detalles.materias.aprobadas + ' / ' + detalles.materias.total,
                cumple: detalles.materias.cumple,
                icon:   'bi-journal-check',
            });
        }
        if (detalles.cum) {
            items.push({
                titulo: 'CUM',
                valor:  detalles.cum.valor + ' / mín. ' + detalles.cum.minimo,
                cumple: detalles.cum.cumple,
                icon:   'bi-graph-up-arrow',
            });
        }
        if (detalles.horas_sociales) {
            items.push({
                titulo: 'Horas sociales',
                valor:  detalles.horas_sociales.completadas + ' / ' + detalles.horas_sociales.requeridas + ' hrs',
                cumple: detalles.horas_sociales.cumple,
                icon:   'bi-people-fill',
            });
        }
        if (detalles.solvencia) {
            items.push({
                titulo: 'Solvencia financiera',
                valor:  detalles.solvencia.aprobada ? 'Aprobada' : 'Pendiente',
                cumple: detalles.solvencia.cumple,
                icon:   'bi-cash-coin',
            });
        }
        if (detalles.arancel_servicio_social) {
            items.push({
                titulo: 'Arancel Servicio Social (TRA-014)',
                valor:  detalles.arancel_servicio_social.pagado ? 'Pagado' : 'No pagado',
                cumple: detalles.arancel_servicio_social.cumple,
                icon:   'bi-receipt',
            });
        }
        if (detalles.constancia_notas) {
            items.push({
                titulo: 'Certificación de Notas (CER-001)',
                valor:  detalles.constancia_notas.pagada ? 'Solicitada' : 'No solicitada',
                cumple: detalles.constancia_notas.cumple,
                icon:   'bi-file-earmark-text',
            });
        }
        if (detalles.constancia_cum) {
            items.push({
                titulo: 'Constancia de CUM (CER-009)',
                valor:  detalles.constancia_cum.pagada ? 'Solicitada' : 'No solicitada',
                cumple: detalles.constancia_cum.cumple,
                icon:   'bi-file-earmark-medical',
            });
        }
        if (detalles.pasantia && detalles.pasantia.requerida) {
            items.push({
                titulo: 'Pasantía',
                valor:  detalles.pasantia.aprobada ? 'Aprobada' : 'Pendiente',
                cumple: detalles.pasantia.cumple,
                icon:   'bi-briefcase-fill',
            });
        }

        items.forEach(function (item) {
            const row = document.createElement('div');
            row.className = 'd-flex justify-content-between align-items-center p-2 rounded border ' +
                (item.cumple ? 'bg-success bg-opacity-10 border-success' : 'bg-danger bg-opacity-10 border-danger');
            row.innerHTML =
                '<div class="d-flex align-items-center gap-2">' +
                    '<i class="bi ' + item.icon + (item.cumple ? ' text-success' : ' text-danger') + '"></i>' +
                    '<span class="small fw-semibold">' + item.titulo + '</span>' +
                '</div>' +
                '<div class="d-flex align-items-center gap-2">' +
                    '<span class="small text-muted">' + item.valor + '</span>' +
                    (item.cumple
                        ? '<i class="bi bi-check-circle-fill text-success"></i>'
                        : '<i class="bi bi-x-circle-fill text-danger"></i>') +
                '</div>';
            reqWrapper.appendChild(row);
        });

        /* Bloqueadores */
        const bloqWrapper = document.getElementById('mBloqueadoresWrapper');
        const bloqList    = document.getElementById('mBloqueadores');
        bloqList.innerHTML = '';
        if (!puede && bloqueadores.length) {
            bloqWrapper.classList.remove('d-none');
            bloqueadores.forEach(function (b) {
                const li = document.createElement('li');
                li.textContent = b;
                bloqList.appendChild(li);
            });
        } else {
            bloqWrapper.classList.add('d-none');
        }

        /* Registrar */
        if (puede && estadoValue === 'EN_PROCESO') {
            wrapperReg.classList.remove('d-none');
            formRegistrar.action = registrarUrl;
            checkbox.checked = false;
            btnRegistrar.disabled = true;
            btnRegistrar.innerHTML = '<i class="bi bi-mortarboard-fill me-1"></i> Registrar como Egresado';
        } else {
            wrapperReg.classList.add('d-none');
        }
    });

    if (checkbox) {
        checkbox.addEventListener('change', function () {
            btnRegistrar.disabled = !this.checked;
        });
    }

    if (formRegistrar) {
        formRegistrar.addEventListener('submit', function () {
            btnRegistrar.disabled = true;
            btnRegistrar.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Procesando...';
        });
    }
});

/* ──────────────────────────────────────────────────────────────
 *  MÓDULO 2 · MODAL GESTIONAR
 * ────────────────────────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', function () {
    const modalGestionar = document.getElementById('modalGestionar');
    if (!modalGestionar) return;

    const formHs       = document.getElementById('formHs');
    const formPas      = document.getElementById('formPas');
    const formSolvencia= document.getElementById('formSolvencia');
    const bloqueoHs    = document.getElementById('bloqueoHs');
    const bloqueoPas   = document.getElementById('bloqueoPas');
    const btnHs        = document.querySelector('.btn-hs');
    const btnPas       = document.querySelector('.btn-pas');
    const btnSolvencia = document.querySelector('.btn-solvencia');

    modalGestionar.addEventListener('show.bs.modal', function (event) {
        const btn = event.relatedTarget;
        if (!btn) return;

        const puedeHs      = btn.getAttribute('data-puede-hs') === '1';
        const puedePas     = btn.getAttribute('data-puede-pas') === '1';
        const tieneDocS    = btn.getAttribute('data-doc-social') === '1';
        const tieneDocP    = btn.getAttribute('data-doc-pasantia') === '1';
        const solvente     = btn.getAttribute('data-solvencia') === '1';
        const urlSolvencia = btn.getAttribute('data-url-solvencia');

        /* Encabezado */
        document.getElementById('gNombre').textContent    = btn.getAttribute('data-nombre');
        document.getElementById('gCarnet').textContent    = btn.getAttribute('data-carnet');
        document.getElementById('gNivel').textContent     = btn.getAttribute('data-nivel') || '—';
        document.getElementById('gUvGanadas').textContent = btn.getAttribute('data-uv-ganadas');
        document.getElementById('gUvMinimas').textContent = btn.getAttribute('data-uv-minimas');
        document.getElementById('gAvisoUv').classList.toggle('d-none', puedeHs && puedePas);

        /* Solvencia financiera */
        const estadoSolvencia = document.getElementById('solvenciaEstado');
        const btnSolv         = document.querySelector('.btn-solvencia');

        if (solvente) {
            estadoSolvencia.className = 'alert alert-success border-0 small mb-2';
            estadoSolvencia.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i><strong>Solvente.</strong> Todos los ciclos han sido cancelados.';
            btnSolv.disabled = true;
            btnSolv.innerHTML = '<i class="bi bi-check2-all me-1"></i> Solvencia aprobada';
        } else {
            estadoSolvencia.className = 'alert alert-warning border-0 small mb-2';
            estadoSolvencia.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i><strong>No solvente.</strong> El sistema validará los ciclos al marcar.';
            btnSolv.disabled = false;
            btnSolv.innerHTML = '<i class="bi bi-check2-circle me-1"></i> Marcar solvencia';
        }

        formSolvencia.action = urlSolvencia;

        /* Form Horas Sociales */
        formHs.action = btn.getAttribute('data-url-hs');
        document.getElementById('gHoras').value          = btn.getAttribute('data-horas');
        document.getElementById('gHorasAprobadas').value = btn.getAttribute('data-horas-aprobadas');
        bloqueoHs.classList.toggle('d-none', puedeHs);
        btnHs.disabled = !puedeHs;

        /* Form Pasantía */
        formPas.action = btn.getAttribute('data-url-pas');
        document.getElementById('gRequierePasantia').value = btn.getAttribute('data-requiere-pasantia');
        document.getElementById('gPasantiaAprobada').value = btn.getAttribute('data-pasantia-aprobada');
        document.getElementById('gInstitucion').value      = btn.getAttribute('data-institucion') || '';
        bloqueoPas.classList.toggle('d-none', puedePas);
        btnPas.disabled = !puedePas;

        /* Documentos */
        const urlSocial = btn.getAttribute('data-url-doc-social');
        const urlPas    = btn.getAttribute('data-url-doc-pas');

        if (tieneDocS) {
            document.getElementById('docSocialInfo').textContent = 'Documento disponible.';
            const a = document.getElementById('btnVerDocSocial');
            a.href = urlSocial;
            a.classList.remove('d-none');
        } else {
            document.getElementById('docSocialInfo').textContent = 'No hay documento subido.';
            document.getElementById('btnVerDocSocial').classList.add('d-none');
        }

        if (tieneDocP) {
            document.getElementById('docPasInfo').textContent = 'Documento disponible.';
            const a = document.getElementById('btnVerDocPas');
            a.href = urlPas;
            a.classList.remove('d-none');
        } else {
            document.getElementById('docPasInfo').textContent = 'No hay documento subido.';
            document.getElementById('btnVerDocPas').classList.add('d-none');
        }
        
        const tieneArancelSS = btn.getAttribute('data-tiene-arancel-ss') === '1';
        if (!tieneArancelSS) {
            // Deshabilitar el form de horas sociales
            formHs.querySelectorAll('input, select, button').forEach(el => el.disabled = true);
            // Mostrar alerta
            document.getElementById('bloqueoHs').classList.remove('d-none');
            document.getElementById('bloqueoHs').innerHTML =
                '<i class="bi bi-exclamation-triangle-fill me-1"></i>' +
                'Debes cancelar el arancel <strong>TRA-014</strong> antes de gestionar las horas sociales.';
        } else {
            formHs.querySelectorAll('input, select, button').forEach(el => el.disabled = false);
            document.getElementById('bloqueoHs').classList.add('d-none');
        }
    });

    /* Spinners */
    formHs.addEventListener('submit', function () {
        btnHs.disabled = true;
        btnHs.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Guardando...';
    });

    formPas.addEventListener('submit', function () {
        btnPas.disabled = true;
        btnPas.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Guardando...';
    });

    formSolvencia.addEventListener('submit', function () {
        if (btnSolvencia) {
            btnSolvencia.disabled = true;
            btnSolvencia.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Validando...';
        }
    });
});
</script>
@endpush
@endsection