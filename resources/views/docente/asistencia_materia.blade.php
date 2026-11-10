@extends('layouts.app')

@section('title', 'Asistencia - ' . $materia['nombre'] . ' | NEXUM')

@section('content')
<div class="asistencia-wrapper">
    <div class="container-fluid p-4 p-lg-5">
        @php
        // Calcular los límites de la semana actual (Lunes a Domingo)
        $inicioSemana = now()->startOfWeek();
        $finSemana = now()->endOfWeek();
        @endphp

        <div class="mx-auto" style="max-width: 1500px;" data-seccion-id="{{ $seccionId }}">

            <!-- ENCABEZADO + ACCIONES -->
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-3 mb-4">
                <div>
                    <a href="{{ route('docente.materias') }}" class="text-decoration-none small text-danger fw-semibold mb-2 d-inline-block">
                        <i class="fa-solid fa-arrow-left me-1"></i>Volver a mis materias
                    </a>
                    <h1 class="display-6 fw-bold mb-2" style="letter-spacing: -0.5px;">{{ $materia['nombre'] }}</h1>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge rounded-pill bg-danger">{{ $materia['codigo'] }}</span>
                        <span class="badge rounded-pill bg-white text-danger border">{{ $materia['grupo'] }}</span>
                        <span class="badge rounded-pill bg-white text-muted border">
                            <i class="fa-solid fa-users me-1"></i>{{ $materia['total_estudiantes'] }} estudiantes
                        </span>
                        <span class="badge rounded-pill bg-white text-muted border">
                            <i class="fa-regular fa-calendar me-1"></i>
                            Semana: {{ $inicioSemana->format('d/m') }} - {{ $finSemana->format('d/m') }}
                        </span>
                    </div>
                </div>

                <!-- Botones -->
                <div class="d-flex gap-2 align-items-start flex-wrap">
                    <button type="button" id="btnEditar" class="btn btn-outline-danger rounded-3 px-4">
                        <i class="fa-solid fa-pen me-2"></i>Editar
                    </button>
                    <button type="button" id="btnGuardar" class="btn btn-danger rounded-3 px-4 d-none" disabled>
                        <i class="fa-solid fa-floppy-disk me-2"></i>Guardar
                    </button>
                    <button type="button" id="btnCancelar" class="btn btn-outline-secondary rounded-3 px-4 d-none">
                        <i class="fa-solid fa-xmark me-2"></i>Cancelar
                    </button>
                </div>
            </div>

            <!-- Alerta de modo edición -->
            <div id="alertaEdicion" class="alert border-0 shadow-sm rounded-4 mb-4 d-none" style="background-color: #fff5f5;">
                <div class="d-flex align-items-start">
                    <i class="fa-solid fa-circle-info text-danger me-3 fs-5 mt-1"></i>
                    <div>
                        <strong class="text-dark">Modo edición activo.</strong>
                        <span class="text-dark small d-block mt-1">
                            Solo puedes registrar asistencia de las sesiones de la semana actual ({{ $inicioSemana->format('d/m') }} al {{ $finSemana->format('d/m') }}).
                            Las sesiones anteriores con asistencia ya registrada no se pueden modificar.
                        </span>
                    </div>
                </div>
            </div>

            <!-- TABLA DE ASISTENCIA -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center p-4 border-bottom gap-3">
                    <div>
                        <h5 class="fw-bold mb-1">
                            <i class="fa-solid fa-clipboard-check text-danger me-2"></i>
                            Control de Asistencia
                        </h5>
                        <small class="text-muted">
                            <i class="fa-solid fa-circle-info me-1"></i>
                            Asis: Asistente · Falt: Faltante · Per: Con Permiso
                        </small>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <div class="text-center">
                            <div class="small text-muted mb-1">Sesiones registradas</div>
                            <div class="fw-bold fs-5">{{ count($sesiones) }}</div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    @if($sesiones->isEmpty())
                    <div class="text-center py-5">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width: 64px; height: 64px; background-color: #fff5f5;">
                            <i class="fa-solid fa-calendar-xmark text-danger fs-4"></i>
                        </div>
                        <p class="text-muted mb-0">No hay sesiones de clase registradas para esta materia aún.</p>
                    </div>
                    @else
                    <table class="table align-middle mb-0 tabla-asistencia">
                        <thead>
                            <tr>
                                <th class="ps-4 fw-semibold text-dark sticky-col" style="min-width: 220px; left: 0; z-index: 10;">Estudiante</th>
                                <th class="text-center fw-semibold text-dark sticky-col-porcentaje" style="min-width: 110px; left: 220px; z-index: 10;">
                                    <div>% Asistencia</div>
                                    <small class="text-muted fw-normal th-sub">Mínimo 80%</small>
                                </th>
                                @foreach($sesiones as $sesion)
                                @php
                                $mesesCortos = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
                                $mesCorto = $mesesCortos[(int)$sesion->fecha->format('m') - 1];
                                $fechaSesion = \Carbon\Carbon::parse($sesion->fecha);
                                $enSemanaActual = $fechaSesion->between($inicioSemana, $finSemana);
                                @endphp
                                <th class="text-center fw-semibold text-dark th-sesion-fecha {{ $enSemanaActual ? 'th-semana-actual' : 'th-semana-pasada' }}" style="min-width: 80px;">
                                    <div class="fs-3 fw-bold text-dark lh-1 mb-1">{{ $fechaSesion->format('d') }}</div>
                                    <small class="text-uppercase fw-semibold" style="font-size: 0.7rem; letter-spacing: 0.5px; color: {{ $enSemanaActual ? '#dc3545' : '#6c757d' }};">{{ $mesCorto }}</small>
                                    @if($enSemanaActual)
                                    <div class="mt-1">
                                        <span class="badge bg-danger bg-opacity-10 text-danger" style="font-size: 0.6rem;">Actual</span>
                                    </div>
                                    @endif
                                </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody id="tablaEstudiantes">
                            @foreach($estudiantes as $estudiante)
                            <tr data-detalle-id="{{ $estudiante->detalle_inscripcion_id }}" class="fila-estudiante">
                                <td class="ps-4 sticky-col" style="left: 0; z-index: 5;">
                                    <div class="d-flex align-items-center">
                                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle me-3 flex-shrink-0 avatar-asistencia">
                                            <span class="fw-bold small">{{ strtoupper(substr(optional(optional($estudiante->inscripcion)->alumno)->usuario->name ?? 'U', 0, 1)) }}</span>
                                        </div>
                                        <div>
                                            <div class="fw-medium small mb-0">{{ optional(optional($estudiante->inscripcion)->alumno)->usuario->name ?? 'Sin nombre' }}</div>
                                            <small class="text-muted">{{ optional(optional($estudiante->inscripcion)->alumno)->codigo_estudiante ?? 'S/C' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center sticky-col-porcentaje" style="left: 220px; z-index: 5;">
                                    <span class="fw-bold fs-5 porcentaje-display">—</span>
                                </td>
                                @foreach($sesiones as $sesion)
                                @php
                                $registro = $estudiante->asistencias->firstWhere('id_sesion', $sesion->clase_sesion_id);
                                $estadoActual = $registro ? $registro->estado : '';
                                $fechaSesion = \Carbon\Carbon::parse($sesion->fecha);
                                $enSemanaActual = $fechaSesion->between($inicioSemana, $finSemana);
                                $esEditable = $enSemanaActual && empty($estadoActual);
                                @endphp
                                <td class="text-center">
                                    <select class="form-select form-select-sm asistencia-select"
                                        data-sesion-id="{{ $sesion->clase_sesion_id }}"
                                        data-detalle-id="{{ $estudiante->detalle_inscripcion_id }}"
                                        data-editable="{{ $esEditable ? 'true' : 'false' }}"
                                        @if(!$esEditable) disabled @endif>
                                        <option value="">—</option>
                                        <option value="Asis" {{ $estadoActual === 'Asis' ? 'selected' : '' }}>Asis</option>
                                        <option value="Falt" {{ $estadoActual === 'Falt' ? 'selected' : '' }}>Falt</option>
                                        <option value="Per" {{ $estadoActual === 'Per' ? 'selected' : '' }}>Per</option>
                                    </select>
                                    @if(!empty($estadoActual))
                                    <div class="mt-1">
                                        <i class="fa-solid fa-lock text-muted" style="font-size: 0.65rem;" title="Asistencia registrada previamente"></i>
                                    </div>
                                    @endif
                                </td>
                                @endforeach
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @endif
                </div>

                <!-- Footer con estadísticas -->
                <div class="p-4 border-top" style="background-color: #fafbfc;">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                        <div>
                            <div class="fw-semibold mb-1 text-dark">
                                <i class="fa-solid fa-chart-line me-2 text-danger"></i>
                                Resumen de Asistencia
                            </div>
                            <div class="small text-muted">
                                Estadísticas calculadas en base a la asistencia registrada.
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-4">
                            <div class="text-center">
                                <div class="small text-muted mb-1">Cumplen 80%</div>
                                <div class="fw-bold fs-5 nota-aprobada" id="totalCumplen">0</div>
                            </div>
                            <div class="text-center">
                                <div class="small text-muted mb-1">En riesgo</div>
                                <div class="fw-bold fs-5 text-danger" id="totalRiesgo">0</div>
                            </div>
                            <div class="text-center px-4 py-2 rounded-3" style="background-color: rgba(220, 53, 69, 0.1);">
                                <div class="small text-muted mb-1">Promedio General</div>
                                <div class="fw-bold fs-3 text-danger" id="promedioGeneral">—</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- ==========================================================================
     MODALES PERSONALIZADOS
========================================================================== -->

<!-- Modal 1: Confirmar Cancelación -->
<div class="modal fade" id="modalCancelar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark">
                    <i class="fa-solid fa-triangle-exclamation text-warning me-2"></i>¿Cancelar edición?
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body py-3">
                <p class="text-muted small mb-0">
                    Se perderán todos los cambios no guardados que hayas realizado en la asistencia.
                </p>
            </div>
            <div class="modal-footer border-0 pt-0 pb-4 px-4">
                <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Continuar editando</button>
                <button type="button" class="btn btn-warning px-4 fw-semibold" id="modalCancelarBtnConfirmar">
                    Sí, cancelar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal 2: Confirmar Guardado -->
<div class="modal fade" id="modalGuardar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark">
                    <i class="fa-solid fa-floppy-disk text-danger me-2"></i>¿Guardar asistencia?
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body py-3">
                <p class="text-muted small mb-0">
                    Se registrará la asistencia de todos los estudiantes para las sesiones seleccionadas. Esta acción no se puede deshacer.
                </p>
            </div>
            <div class="modal-footer border-0 pt-0 pb-4 px-4">
                <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Revisar</button>
                <button type="button" class="btn btn-danger px-4 fw-semibold" id="modalGuardarBtnConfirmar">
                    Sí, guardar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal 3: Validación (campos vacíos) -->
<div class="modal fade" id="modalValidacion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark">
                    <i class="fa-solid fa-circle-exclamation text-warning me-2"></i>Campos incompletos
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body py-3">
                <p class="text-muted small mb-0" id="modalValidacionMensaje">
                    Debes registrar el estado de asistencia (Asis, Falt o Per) en todos los estudiantes antes de guardar.
                </p>
            </div>
            <div class="modal-footer border-0 pt-0 pb-4 px-4">
                <button type="button" class="btn btn-warning px-4 fw-semibold w-100" data-bs-dismiss="modal">
                    Entendido
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal 4: Éxito (CON BOTÓN X Y RECARGA AUTOMÁTICA) -->
<div class="modal fade" id="modalExito" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark">
                    <i class="fa-solid fa-circle-check text-success me-2"></i>¡Guardado con éxito!
                </h5>
                <button type="button" class="btn-close" id="modalExitoBtnCerrar" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body py-3">
                <p class="text-muted small mb-0" id="modalExitoMensaje">
                    La asistencia se ha registrado correctamente.
                </p>
            </div>
            <div class="modal-footer border-0 pt-0 pb-4 px-4">
                <button type="button" class="btn btn-success px-4 fw-semibold w-100" id="modalExitoBtnAceptar">
                    Aceptar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal 5: Error (CON BOTÓN X) -->
<div class="modal fade" id="modalError" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark">
                    <i class="fa-solid fa-circle-xmark text-danger me-2"></i>Error al guardar
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body py-3">
                <p class="text-muted small mb-0" id="modalErrorMensaje">
                    Ocurrió un problema al guardar la asistencia. Intenta nuevamente.
                </p>
            </div>
            <div class="modal-footer border-0 pt-0 pb-4 px-4">
                <button type="button" class="btn btn-danger px-4 fw-semibold w-100" data-bs-dismiss="modal">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* ========== FONDO GRIS PARA VISTA DE ASISTENCIA ========== */
    .asistencia-wrapper {
        background-color: #e5e7eb;
        min-height: calc(100vh - 0px);
    }

    /* ========== ZEBRA STRIPING (FILAS INTERCALADAS) ========== */
    .tabla-asistencia tbody tr.fila-estudiante:nth-child(odd) td,
    .tabla-asistencia tbody tr.fila-estudiante:nth-child(odd) .sticky-col,
    .tabla-asistencia tbody tr.fila-estudiante:nth-child(odd) .sticky-col-porcentaje {
        background-color: #ffffff;
    }

    .tabla-asistencia tbody tr.fila-estudiante:nth-child(even) td,
    .tabla-asistencia tbody tr.fila-estudiante:nth-child(even) .sticky-col,
    .tabla-asistencia tbody tr.fila-estudiante:nth-child(even) .sticky-col-porcentaje {
        background-color: #d1d5db;
    }

    /* ========== ENCABEZADO DE SESIONES ========== */
    .th-sesion-fecha {
        vertical-align: middle;
        padding: 14px 8px !important;
    }

    .th-semana-pasada {
        background-color: #f8f9fa !important;
        opacity: 0.75;
    }

    .th-semana-actual {
        background-color: #fff5f5 !important;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const btnEditar = document.getElementById('btnEditar');
        const btnGuardar = document.getElementById('btnGuardar');
        const btnCancelar = document.getElementById('btnCancelar');
        const alertaEdicion = document.getElementById('alertaEdicion');
        const selectsAsistencia = document.querySelectorAll('.asistencia-select');
        const filas = document.querySelectorAll('.fila-estudiante');
        const seccionId = document.querySelector('[data-seccion-id]').dataset.seccionId;
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        let modoEdicion = false;

        // ========== INSTANCIAS DE MODALES (creadas una sola vez) ==========
        const modalCancelar = new bootstrap.Modal(document.getElementById('modalCancelar'));
        const modalGuardar = new bootstrap.Modal(document.getElementById('modalGuardar'));
        const modalValidacion = new bootstrap.Modal(document.getElementById('modalValidacion'));
        const modalExito = new bootstrap.Modal(document.getElementById('modalExito'));
        const modalError = new bootstrap.Modal(document.getElementById('modalError'));

        // ========== LISTENERS DE MODAL ÉXITO (cerrar y recargar) ==========
        // Tanto el botón X como el botón Aceptar cierran y recargan
        document.getElementById('modalExitoBtnCerrar').addEventListener('click', function() {
            modalExito.hide();
        });
        document.getElementById('modalExitoBtnAceptar').addEventListener('click', function() {
            modalExito.hide();
        });
        // Cuando el modal se termina de ocultar, recargar la página
        document.getElementById('modalExito').addEventListener('hidden.bs.modal', function() {
            location.reload();
        });

        // ========== LISTENERS DE MODAL CANCELAR ==========
        document.getElementById('modalCancelarBtnConfirmar').addEventListener('click', function() {
            modalCancelar.hide();
            // Esperar a que se cierre el modal antes de recargar
            document.getElementById('modalCancelar').addEventListener('hidden.bs.modal', function() {
                location.reload();
            }, {
                once: true
            });
        });

        // ========== LISTENERS DE MODAL GUARDAR ==========
        document.getElementById('modalGuardarBtnConfirmar').addEventListener('click', async function() {
            modalGuardar.hide();
            await ejecutarGuardado();
        });

        // ========== FUNCIONES HELPER ==========
        function setMensaje(modalId, mensaje) {
            const elMensaje = document.querySelector(`#${modalId} [id$="Mensaje"]`);
            if (elMensaje) elMensaje.textContent = mensaje;
        }

        function mostrarValidacion(mensaje) {
            setMensaje('modalValidacion', mensaje);
            modalValidacion.show();
        }

        function mostrarExito(mensaje) {
            setMensaje('modalExito', mensaje);
            modalExito.show();
            // El recargo se maneja en el listener hidden.bs.modal del modalExito
        }

        function mostrarError(mensaje) {
            setMensaje('modalError', mensaje);
            modalError.show();
        }

        // ========== LÓGICA DE PORCENTAJES Y ESTADÍSTICAS ==========
        function calcularPorcentaje(fila) {
            const selects = fila.querySelectorAll('.asistencia-select');
            const porcentajeDisplay = fila.querySelector('.porcentaje-display');

            let asistencias = 0;
            let marcadas = 0;

            selects.forEach(select => {
                if (select.value !== '') {
                    marcadas++;
                    if (select.value === 'Asis' || select.value === 'Per') {
                        asistencias++;
                    }
                }
            });

            if (marcadas === 0) {
                porcentajeDisplay.textContent = '—';
                porcentajeDisplay.className = 'fw-bold fs-5 porcentaje-display';
                return null;
            }

            const porcentaje = Math.round((asistencias / marcadas) * 100);
            porcentajeDisplay.textContent = porcentaje + '%';

            if (porcentaje >= 80) {
                porcentajeDisplay.className = 'fw-bold fs-5 porcentaje-display nota-aprobada';
            } else {
                porcentajeDisplay.className = 'fw-bold fs-5 porcentaje-display nota-reprobada';
            }

            return porcentaje;
        }

        function actualizarEstadisticas() {
            let sumaPorcentajes = 0;
            let totalCalculados = 0;
            let cumplen = 0;
            let riesgo = 0;

            filas.forEach(fila => {
                const porcentajeText = fila.querySelector('.porcentaje-display').textContent;
                if (porcentajeText !== '—') {
                    const porcentaje = parseInt(porcentajeText);
                    totalCalculados++;
                    sumaPorcentajes += porcentaje;
                    if (porcentaje >= 80) {
                        cumplen++;
                    } else {
                        riesgo++;
                    }
                }
            });

            const promedioGeneralEl = document.getElementById('promedioGeneral');
            document.getElementById('totalCumplen').textContent = cumplen;
            document.getElementById('totalRiesgo').textContent = riesgo;

            if (totalCalculados > 0) {
                const promedioGen = Math.round(sumaPorcentajes / totalCalculados);
                promedioGeneralEl.textContent = promedioGen + '%';
                promedioGeneralEl.className = promedioGen >= 80 ?
                    'fw-bold fs-3 nota-aprobada' :
                    'fw-bold fs-3 text-danger';
            } else {
                promedioGeneralEl.textContent = '—';
                promedioGeneralEl.className = 'fw-bold fs-3 text-danger';
            }
        }

        function activarModoEdicion() {
            modoEdicion = true;
            selectsAsistencia.forEach(s => {
                if (s.dataset.editable === 'true') {
                    s.disabled = false;
                }
            });
            btnEditar.classList.add('d-none');
            btnGuardar.classList.remove('d-none');
            btnGuardar.disabled = false;
            btnCancelar.classList.remove('d-none');
            alertaEdicion.classList.remove('d-none');
        }

        function desactivarModoEdicion() {
            modoEdicion = false;
            selectsAsistencia.forEach(s => s.disabled = true);
            btnEditar.classList.remove('d-none');
            btnGuardar.classList.add('d-none');
            btnCancelar.classList.add('d-none');
            alertaEdicion.classList.add('d-none');
            btnGuardar.disabled = true;
        }

        // ========== FUNCIÓN DE GUARDADO EXTRAÍDA ==========
        async function ejecutarGuardado() {
            const selectsEditables = Array.from(selectsAsistencia).filter(s => s.dataset.editable === 'true');

            const asistenciasData = selectsEditables
                .filter(select => select.value !== '')
                .map(select => ({
                    id_detalle_inscripcion: parseInt(select.dataset.detalleId),
                    estado: select.value,
                    observacion: null
                }));

            if (asistenciasData.length === 0) {
                mostrarValidacion('No hay datos de asistencia para guardar.');
                return;
            }

            // Agrupar por sesión
            const asistenciasPorSesion = {};
            selectsEditables.forEach(select => {
                if (select.value !== '') {
                    const sesionId = select.dataset.sesionId;
                    if (!asistenciasPorSesion[sesionId]) {
                        asistenciasPorSesion[sesionId] = [];
                    }
                    asistenciasPorSesion[sesionId].push({
                        id_detalle_inscripcion: parseInt(select.dataset.detalleId),
                        estado: select.value,
                        observacion: null
                    });
                }
            });

            btnGuardar.disabled = true;
            btnGuardar.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Guardando...';

            try {
                let todasExitosas = true;
                let mensajeError = '';

                for (const [sesionId, asistencias] of Object.entries(asistenciasPorSesion)) {
                    const response = await fetch(`/docente/asistencia/${seccionId}/guardar`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            id_sesion: parseInt(sesionId),
                            asistencias: asistencias
                        })
                    });

                    const data = await response.json();

                    if (!data.success) {
                        todasExitosas = false;
                        mensajeError = data.message || 'Error al guardar.';
                        break;
                    }
                }

                if (todasExitosas) {
                    // Mostrar modal de éxito (el recargo se hará automáticamente al cerrar)
                    mostrarExito('La asistencia de todas las sesiones se ha registrado correctamente. La página se recargará en un momento.');
                } else {
                    mostrarError(mensajeError);
                    btnGuardar.disabled = false;
                    btnGuardar.innerHTML = '<i class="fa-solid fa-floppy-disk me-2"></i>Guardar';
                }
            } catch (error) {
                console.error('Error:', error);
                mostrarError('Error de conexión con el servidor. Por favor verifica tu conexión e intenta nuevamente.');
                btnGuardar.disabled = false;
                btnGuardar.innerHTML = '<i class="fa-solid fa-floppy-disk me-2"></i>Guardar';
            }
        }

        // ========== EVENTOS DE BOTONES ==========
        if (btnEditar) {
            btnEditar.addEventListener('click', activarModoEdicion);
        }

        if (btnCancelar) {
            btnCancelar.addEventListener('click', function() {
                modalCancelar.show();
            });
        }

        if (btnGuardar) {
            btnGuardar.addEventListener('click', async function() {
                // 1. Validar que todos los selects editables tengan valor
                const selectsEditables = Array.from(selectsAsistencia).filter(s => s.dataset.editable === 'true');
                const selectsVacios = selectsEditables.filter(s => s.value === '');

                if (selectsVacios.length > 0) {
                    mostrarValidacion(
                        `Hay ${selectsVacios.length} ${selectsVacios.length === 1 ? 'campo sin llenar' : 'campos sin llenar'}. Debes marcar el estado de asistencia (Asis, Falt o Per) en todos los estudiantes de la semana actual antes de guardar.`
                    );
                    return;
                }

                // 2. Mostrar modal de confirmación
                modalGuardar.show();
            });
        }

        // ========== EVENTOS DE SELECTS ==========
        selectsAsistencia.forEach(select => {
            select.addEventListener('change', function() {
                const fila = this.closest('.fila-estudiante');
                calcularPorcentaje(fila);
                actualizarEstadisticas();
            });
        });

        // ========== INICIALIZACIÓN ==========
        filas.forEach(fila => calcularPorcentaje(fila));
        actualizarEstadisticas();
    });
</script>
@endpush
@endsection