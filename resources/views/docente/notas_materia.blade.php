@extends('layouts.app')

@section('title', 'Notas - ' . $materia['nombre'] . ' | NEXUM')

@section('content')
@php
$columnasOrdenadas = [];
foreach($evaluacionesPorPeriodo as $periodoId => $periodoData) {
foreach($periodoData['categorias'] as $categoria) {
foreach($categoria->evaluaciones as $evaluacion) {
$columnasOrdenadas[] = $evaluacion;
}
}
}

$totalEstudiantes = count($estudiantes);
$estadoColumnas = [];
$anteriorCompleta = true;

foreach($columnasOrdenadas as $eval) {
$evaluacionId = $eval->evaluacion_id;

$notasGuardadas = 0;
foreach($estudiantes as $est) {
if (isset($est->notas[$evaluacionId]) && !is_null($est->notas[$evaluacionId])) {
$notasGuardadas++;
}
}

$completa = ($notasGuardadas >= $totalEstudiantes);
$tieneNotas = ($notasGuardadas > 0);

if ($tieneNotas) {
$esEditable = false;
$anteriorCompleta = $completa;
} else {
if ($anteriorCompleta) {
$esEditable = true;
$anteriorCompleta = false;
} else {
$esEditable = false;
$anteriorCompleta = false;
}
}

$estadoColumnas[$evaluacionId] = [
'editable' => $esEditable,
'completa' => $completa,
'tieneNotas' => $tieneNotas,
];
}
@endphp

<div class="notas-wrapper">
    <div class="container-fluid p-4 p-lg-5" data-seccion-id="{{ $seccionId }}">
        <div class="mx-auto" style="max-width: 1500px;">

            <!-- ENCABEZADO + ACCIONES -->
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-3 mb-4">
                <div>
                    <a href="{{ route('docente.materias') }}" class="text-decoration-none small text-danger fw-semibold mb-2 d-inline-block">
                        <i class="fa-solid fa-arrow-left me-1"></i>Volver a mis materias
                    </a>
                    <h1 class="display-6 fw-bold mb-2" style="letter-spacing: -0.5px;">{{ $materia['nombre'] }}</h1>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge rounded-pill bg-danger">{{ $materia['codigo'] }}</span>
                        <span class="badge rounded-pill bg-white text-danger border">Grupo {{ $materia['grupo'] }}</span>
                        <span class="badge rounded-pill bg-white text-muted border">
                            <i class="fa-solid fa-calendar me-1"></i>{{ $materia['ciclo'] }}
                        </span>
                        <span class="badge rounded-pill bg-white text-muted border">
                            <i class="fa-solid fa-users me-1"></i>{{ $materia['total_estudiantes'] }} estudiantes
                        </span>
                    </div>
                </div>

                <div class="d-flex gap-2 align-items-start">
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
                            Solo puedes editar la siguiente columna pendiente.
                            Debes completar todas las calificaciones antes de guardar.
                            <strong>Una vez guardadas, las notas no podrán modificarse.</strong>
                        </span>
                    </div>
                </div>
            </div>

            <!-- TABLA DE NOTAS -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center p-4 border-bottom gap-3">
                    <div>
                        <h5 class="fw-bold mb-1">
                            <i class="fa-solid fa-table-list text-danger me-2"></i>
                            Registro de Calificaciones
                        </h5>
                        <small class="text-muted">
                            <i class="fa-solid fa-circle-info me-1"></i>
                            Sistema de evaluación: Lab (40%) + Parcial (60%) por período
                        </small>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <div class="text-center">
                            <div class="small text-muted mb-1">Completado</div>
                            <div class="fw-bold fs-5" id="progresoNotas">0%</div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle mb-0 tabla-notas-materia">
                        <thead>
                            <tr>
                                <th class="ps-4 fw-semibold text-dark sticky-col" style="min-width: 220px; left: 0; z-index: 10;">Estudiante</th>
                                <th class="text-center fw-semibold text-dark sticky-col-porcentaje" style="min-width: 110px; left: 220px; z-index: 10;">
                                    <div>% Asistencia</div>
                                    <small class="text-muted fw-normal th-sub">Mínimo 80%</small>
                                </th>
                                @foreach($evaluacionesPorPeriodo as $periodoId => $periodoData)
                                @foreach($periodoData['categorias'] as $categoria)
                                @foreach($categoria->evaluaciones as $evaluacion)
                                @php
                                $estado = $estadoColumnas[$evaluacion->evaluacion_id] ?? ['editable' => false, 'completa' => false, 'tieneNotas' => false];
                                @endphp
                                <th class="text-center fw-semibold text-dark th-evaluacion {{ $estado['editable'] ? 'th-columna-activa' : '' }} {{ (!$estado['editable'] && !$estado['tieneNotas']) ? 'th-columna-bloqueada' : '' }}" style="min-width: 130px;">
                                    <div class="d-flex align-items-center justify-content-center gap-2">
                                        @if($estado['completa'] && $estado['tieneNotas'])
                                        <i class="fa-solid fa-lock text-muted" style="font-size: 0.75rem;" title="Columna cerrada"></i>
                                        @endif
                                        <div>
                                            <div>{{ $evaluacion->nombre_evaluacion }}</div>
                                            <small class="text-muted fw-normal th-sub">
                                                {{ $categoria->tipo_categoria }} · P{{ $periodoData['periodo']->numero_periodo }}
                                            </small>
                                        </div>
                                    </div>
                                </th>
                                @endforeach
                                @endforeach
                                @endforeach
                                <th class="text-center fw-semibold text-dark" style="min-width: 110px;">
                                    <div>Promedio</div>
                                    <small class="text-muted fw-normal th-sub">3 decimales</small>
                                </th>
                                <th class="text-center fw-semibold text-dark pe-4" style="min-width: 110px;">
                                    <div>Nota Final</div>
                                    <small class="text-muted fw-normal th-sub">1 decimal</small>
                                </th>
                            </tr>
                        </thead>
                        <tbody id="tablaEstudiantes">
                            @forelse($estudiantes as $estudiante)
                            <tr data-codigo="{{ $estudiante->codigo }}" data-detalle-id="{{ $estudiante->detalle_inscripcion_id }}" class="fila-estudiante">
                                <td class="ps-4 sticky-col" style="left: 0; z-index: 5;">
                                    <div class="d-flex align-items-center">
                                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle me-3 flex-shrink-0 avatar-notas">
                                            <span class="fw-bold small">{{ strtoupper(substr($estudiante->nombre, 0, 1)) }}</span>
                                        </div>
                                        <div>
                                            <div class="fw-medium small mb-0">{{ $estudiante->nombre }}</div>
                                            <small class="text-muted">{{ $estudiante->codigo }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center sticky-col-porcentaje" style="left: 220px; z-index: 5;">
                                    <span class="fw-bold fs-5 porcentaje-display">—</span>
                                </td>
                                @foreach($evaluacionesPorPeriodo as $periodoId => $periodoData)
                                @foreach($periodoData['categorias'] as $categoria)
                                @foreach($categoria->evaluaciones as $evaluacion)
                                @php
                                $notaPrevia = $estudiante->notas[$evaluacion->evaluacion_id] ?? null;
                                $estado = $estadoColumnas[$evaluacion->evaluacion_id] ?? ['editable' => false, 'completa' => false, 'tieneNotas' => false];
                                $esInputEditable = $estado['editable'] && is_null($notaPrevia);
                                @endphp
                                <td class="text-center">
                                    <div class="d-flex align-items-center justify-content-center gap-1 nota-cell-wrapper">
                                        @if(!is_null($notaPrevia))
                                        <i class="fa-solid fa-lock text-muted flex-shrink-0" style="font-size: 0.7rem;" title="Nota registrada previamente"></i>
                                        @endif
                                        <input type="number"
                                            class="form-control form-control-sm text-center nota-input"
                                            step="0.1" min="0" max="10" placeholder="0.0"
                                            data-evaluacion-id="{{ $evaluacion->evaluacion_id }}"
                                            data-detalle-inscripcion-id="{{ $estudiante->detalle_inscripcion_id }}"
                                            data-editable="{{ $esInputEditable ? 'true' : 'false' }}"
                                            @if(!is_null($notaPrevia)) data-nota-previa="true" @endif
                                            value="{{ $notaPrevia ?? '' }}"
                                            disabled>
                                    </div>
                                </td>
                                @endforeach
                                @endforeach
                                @endforeach
                                <td class="text-center"><span class="promedio-raw-display">—</span></td>
                                <td class="text-center pe-4"><span class="fw-bold fs-5 promedio-display">—</span></td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="100" class="text-center py-5">
                                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3"
                                        style="width: 64px; height: 64px; background-color: #fff5f5;">
                                        <i class="fa-solid fa-users text-danger fs-4"></i>
                                    </div>
                                    <p class="text-muted mb-0">No hay estudiantes inscritos en esta sección.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Footer con estadísticas -->
                <div class="p-4 border-top" style="background-color: #fafbfc;">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                        <div>
                            <div class="fw-semibold mb-1 text-dark">
                                <i class="fa-solid fa-chart-line me-2 text-danger"></i>
                                Resumen de la Materia
                            </div>
                            <div class="small text-muted">
                                Estadísticas calculadas en base a las notas registradas.
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-4">
                            <div class="text-center">
                                <div class="small text-muted mb-1">Aprobados</div>
                                <div class="fw-bold fs-5 nota-aprobada" id="totalAprobados">0</div>
                            </div>
                            <div class="text-center">
                                <div class="small text-muted mb-1">Reprobados</div>
                                <div class="fw-bold fs-5 text-danger" id="totalReprobados">0</div>
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
                    Se perderán todos los cambios no guardados que hayas realizado en las calificaciones.
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
                    <i class="fa-solid fa-triangle-exclamation text-danger me-2"></i>Confirmar guardado definitivo
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body py-3">
                <p class="text-dark small mb-3 fw-semibold">
                    ¿Estás completamente seguro de guardar estas calificaciones?
                </p>
                <div class="p-3 rounded-3 border-start border-4 border-danger mb-2" style="background-color: #fff5f5;">
                    <div class="d-flex align-items-start">
                        <i class="fa-solid fa-lock text-danger me-2 mt-1"></i>
                        <div class="small text-dark">
                            Una vez guardadas, <strong>estas calificaciones quedarán bloqueadas permanentemente</strong> y
                            <strong>no podrás modificarlas ni corregirlas</strong> en el futuro.
                        </div>
                    </div>
                </div>
                <div class="p-3 rounded-3 border-start border-4 border-warning" style="background-color: #fffbea;">
                    <div class="d-flex align-items-start">
                        <i class="fa-solid fa-circle-info text-warning me-2 mt-1"></i>
                        <div class="small text-dark">
                            Asegúrate de que todas las calificaciones sean correctas antes de confirmar.
                            Esta acción <strong>no se puede deshacer</strong>.
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 pb-4 px-4">
                <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Revisar nuevamente</button>
                <button type="button" class="btn btn-danger px-4 fw-semibold" id="modalGuardarBtnConfirmar">
                    <i class="fa-solid fa-lock me-1"></i>Sí, guardar definitivamente
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal 3: Validación -->
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
                    Debes completar todas las calificaciones antes de guardar.
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

<!-- Modal 4: Éxito -->
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
                    Las calificaciones se han registrado correctamente y quedan bloqueadas.
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

<!-- Modal 5: Error -->
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
                    Ocurrió un problema al guardar las calificaciones. Intenta nuevamente.
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
    .notas-wrapper {
        background-color: #e5e7eb;
        min-height: 100vh;
    }

    /* Zebra striping */
    .tabla-notas-materia tbody tr.fila-estudiante:nth-child(odd) td,
    .tabla-notas-materia tbody tr.fila-estudiante:nth-child(odd) .sticky-col,
    .tabla-notas-materia tbody tr.fila-estudiante:nth-child(odd) .sticky-col-porcentaje {
        background-color: #ffffff;
    }

    .tabla-notas-materia tbody tr.fila-estudiante:nth-child(even) td,
    .tabla-notas-materia tbody tr.fila-estudiante:nth-child(even) .sticky-col,
    .tabla-notas-materia tbody tr.fila-estudiante:nth-child(even) .sticky-col-porcentaje {
        background-color: #d1d5db;
    }

    .tabla-notas-materia tbody tr.fila-estudiante:nth-child(odd):hover td,
    .tabla-notas-materia tbody tr.fila-estudiante:nth-child(odd):hover .sticky-col,
    .tabla-notas-materia tbody tr.fila-estudiante:nth-child(odd):hover .sticky-col-porcentaje {
        background-color: #ffffff;
    }

    .tabla-notas-materia tbody tr.fila-estudiante:nth-child(even):hover td,
    .tabla-notas-materia tbody tr.fila-estudiante:nth-child(even):hover .sticky-col,
    .tabla-notas-materia tbody tr.fila-estudiante:nth-child(even):hover .sticky-col-porcentaje {
        background-color: #d1d5db;
    }

    /* ========== INPUTS DE NOTAS (ancho intermedio: ni tan ancho ni tan estrecho) ========== */
    .nota-input {
        width: 90px !important;
        max-width: 90px !important;
        font-weight: 600;
        font-size: 0.95rem !important;
        letter-spacing: 0.5px;
        padding: 0.375rem 0.4rem;
    }

    .nota-cell-wrapper {
        padding: 0.25rem 0.35rem;
    }

    /* ========== COLUMNA ACTIVA (editable): único acento de color ========== */
    .th-columna-activa {
        background-color: #fff5f5 !important;
        border-top: 3px solid #dc3545 !important;
    }

    /* ========== COLUMNAS BLOQUEADAS (futuras): solo atenuadas, sin color ========== */
    .th-columna-bloqueada {
        opacity: 0.55;
    }

    /* Inputs no editables */
    .nota-input:disabled {
        background-color: #f8f9fa;
        color: #495057;
        cursor: not-allowed;
        opacity: 0.85;
    }

    .nota-input[data-editable="true"]:not(:disabled) {
        background-color: #fff9f9;
        border-color: #dc3545;
        cursor: text;
    }

    .nota-input[data-editable="true"]:not(:disabled):focus {
        background-color: #ffffff;
        border-color: #dc3545;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.15);
    }

    #modalValidacionMensaje {
        white-space: pre-line;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const seccionId = document.querySelector('[data-seccion-id]').dataset.seccionId;
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const guardarUrl = '/docente/notas/' + seccionId + '/guardar';

        const btnEditar = document.getElementById('btnEditar');
        const btnGuardar = document.getElementById('btnGuardar');
        const btnCancelar = document.getElementById('btnCancelar');
        const alertaEdicion = document.getElementById('alertaEdicion');
        const inputsNotas = document.querySelectorAll('.nota-input');
        const filas = document.querySelectorAll('.fila-estudiante');
        const progresoNotas = document.getElementById('progresoNotas');

        let modoEdicion = false;

        const modalCancelar = new bootstrap.Modal(document.getElementById('modalCancelar'));
        const modalGuardar = new bootstrap.Modal(document.getElementById('modalGuardar'));
        const modalValidacion = new bootstrap.Modal(document.getElementById('modalValidacion'));
        const modalExito = new bootstrap.Modal(document.getElementById('modalExito'));
        const modalError = new bootstrap.Modal(document.getElementById('modalError'));

        function mostrarValidacion(mensaje) {
            const el = document.getElementById('modalValidacionMensaje');
            if (el) el.textContent = mensaje;
            modalValidacion.show();
        }

        function mostrarExito(mensaje) {
            const el = document.getElementById('modalExitoMensaje');
            if (el) el.textContent = mensaje;
            modalExito.show();
        }

        function mostrarError(mensaje) {
            const el = document.getElementById('modalErrorMensaje');
            if (el) el.textContent = mensaje;
            modalError.show();
        }

        document.getElementById('modalExitoBtnCerrar').addEventListener('click', function() {
            modalExito.hide();
        });
        document.getElementById('modalExitoBtnAceptar').addEventListener('click', function() {
            modalExito.hide();
        });
        document.getElementById('modalExito').addEventListener('hidden.bs.modal', function() {
            location.reload();
        });

        document.getElementById('modalCancelarBtnConfirmar').addEventListener('click', function() {
            modalCancelar.hide();
            document.getElementById('modalCancelar').addEventListener('hidden.bs.modal', function() {
                location.reload();
            }, {
                once: true
            });
        });

        document.getElementById('modalGuardarBtnConfirmar').addEventListener('click', async function() {
            modalGuardar.hide();
            await ejecutarGuardado();
        });

        function redondearEspecial(valor, decimales = 1) {
            const multiplicador = Math.pow(10, decimales);
            return Math.floor(valor * multiplicador) / multiplicador;
        }

        function validarInput(input) {
            let value = input.value;
            if (value === '') return;
            value = value.replace(',', '.');
            const regex = /^\d+(\.\d)?$/;
            if (!regex.test(value)) {
                const parts = value.split('.');
                if (parts.length > 1 && parts[1].length > 1) {
                    input.value = parts[0] + '.' + parts[1].substring(0, 1);
                }
            }
            const num = parseFloat(input.value);
            if (num > 10) input.value = '10.0';
            if (num < 0) input.value = '0.0';
        }

        function calcularPromedio(fila) {
            const inputs = fila.querySelectorAll('.nota-input');
            const promedioRawDisplay = fila.querySelector('.promedio-raw-display');
            const promedioDisplay = fila.querySelector('.promedio-display');
            if (!promedioRawDisplay || !promedioDisplay) return;

            const valores = Array.from(inputs).map(input => {
                const val = parseFloat(input.value);
                return isNaN(val) ? null : val;
            });

            const tieneNotas = valores.some(v => v !== null);
            if (!tieneNotas) {
                promedioRawDisplay.textContent = '—';
                promedioDisplay.textContent = '—';
                promedioDisplay.className = 'fw-bold fs-5 promedio-display';
                return null;
            }

            const [lab1, par1, lab2, par2, lab3, par3] = valores.map(v => v === null ? 0 : v);
            const periodo1 = (lab1 * 0.4 + par1 * 0.6) * 0.3;
            const periodo2 = (lab2 * 0.4 + par2 * 0.6) * 0.3;
            const periodo3 = (lab3 * 0.4 + par3 * 0.6) * 0.4;
            const promedioFinal = periodo1 + periodo2 + periodo3;

            promedioRawDisplay.textContent = promedioFinal.toFixed(3);
            const promedioRedondeado = redondearEspecial(promedioFinal, 1);
            promedioDisplay.textContent = promedioRedondeado.toFixed(1);

            if (promedioRedondeado >= 6) {
                promedioDisplay.className = 'fw-bold fs-5 promedio-display nota-aprobada';
            } else {
                promedioDisplay.className = 'fw-bold fs-5 promedio-display nota-reprobada';
            }
            return promedioRedondeado;
        }

        function actualizarEstadisticas() {
            let sumaPromedios = 0;
            let totalCalificados = 0;
            let aprobados = 0;
            let reprobados = 0;

            filas.forEach(fila => {
                const promedioDisplay = fila.querySelector('.promedio-display');
                if (!promedioDisplay) return;
                const promedioText = promedioDisplay.textContent;
                if (promedioText !== '—') {
                    const promedio = parseFloat(promedioText);
                    totalCalificados++;
                    sumaPromedios += promedio;
                    if (promedio >= 6) {
                        aprobados++;
                    } else {
                        reprobados++;
                    }
                }
            });

            const promedioGeneralEl = document.getElementById('promedioGeneral');
            const totalAprobadosEl = document.getElementById('totalAprobados');
            const totalReprobadosEl = document.getElementById('totalReprobados');

            if (totalAprobadosEl) totalAprobadosEl.textContent = aprobados;
            if (totalReprobadosEl) totalReprobadosEl.textContent = reprobados;

            if (promedioGeneralEl) {
                if (totalCalificados > 0) {
                    const promedioGen = redondearEspecial(sumaPromedios / totalCalificados, 1);
                    promedioGeneralEl.textContent = promedioGen.toFixed(1);
                    promedioGeneralEl.className = promedioGen >= 6 ?
                        'fw-bold fs-3 nota-aprobada' :
                        'fw-bold fs-3 text-danger';
                } else {
                    promedioGeneralEl.textContent = '—';
                    promedioGeneralEl.className = 'fw-bold fs-3 text-danger';
                }
            }
        }

        function actualizarProgreso() {
            const totalInputs = inputsNotas.length;
            const inputsLlenos = Array.from(inputsNotas).filter(input => input.value.trim() !== '').length;
            const porcentaje = totalInputs > 0 ? Math.round((inputsLlenos / totalInputs) * 100) : 0;
            if (progresoNotas) progresoNotas.textContent = porcentaje + '%';
        }

        function activarModoEdicion() {
            modoEdicion = true;
            inputsNotas.forEach(input => {
                if (input.dataset.editable === 'true') {
                    input.disabled = false;
                }
            });
            btnEditar.classList.add('d-none');
            btnGuardar.classList.remove('d-none');
            btnGuardar.disabled = false;
            btnCancelar.classList.remove('d-none');
            alertaEdicion.classList.remove('d-none');
            actualizarProgreso();
        }

        function desactivarModoEdicion() {
            modoEdicion = false;
            inputsNotas.forEach(input => input.disabled = true);
            btnEditar.classList.remove('d-none');
            btnGuardar.classList.add('d-none');
            btnCancelar.classList.add('d-none');
            alertaEdicion.classList.add('d-none');
            btnGuardar.disabled = true;
        }

        function validarNotasPrevias() {
            const notasPreviasVacias = [];
            inputsNotas.forEach(input => {
                if (input.dataset.notaPrevia === 'true' && input.value.trim() === '') {
                    const fila = input.closest('.fila-estudiante');
                    const nombre = fila.querySelector('.fw-medium').textContent;
                    notasPreviasVacias.push(nombre);
                }
            });
            return notasPreviasVacias;
        }

        async function ejecutarGuardado() {
            const inputsEditables = Array.from(inputsNotas).filter(i => i.dataset.editable === 'true');

            const evaluacionesMap = {};
            inputsEditables.forEach(input => {
                if (input.value.trim() === '') return;
                const evaluacionId = input.dataset.evaluacionId;
                const detalleInscripcionId = input.dataset.detalleInscripcionId;
                const nota = input.value.trim();

                if (!evaluacionesMap[evaluacionId]) {
                    evaluacionesMap[evaluacionId] = [];
                }
                evaluacionesMap[evaluacionId].push({
                    detalle_inscripcion_id: parseInt(detalleInscripcionId),
                    nota: parseFloat(nota)
                });
            });

            const evaluaciones = Object.entries(evaluacionesMap).map(([evaluacionId, calificaciones]) => ({
                evaluacion_id: parseInt(evaluacionId),
                calificaciones: calificaciones
            }));

            if (evaluaciones.length === 0) {
                mostrarValidacion('No hay calificaciones para guardar.');
                return;
            }

            btnGuardar.disabled = true;
            btnGuardar.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Guardando...';

            try {
                const response = await fetch(guardarUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        evaluaciones: evaluaciones
                    })
                });

                const data = await response.json();

                if (data.success) {
                    mostrarExito('Las calificaciones se han registrado correctamente y quedan bloqueadas permanentemente. La página se recargará en un momento.');
                } else {
                    mostrarError(data.message || 'Error al guardar las calificaciones.');
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

        if (btnEditar) {
            btnEditar.addEventListener('click', function(e) {
                e.preventDefault();
                activarModoEdicion();
            });
        }

        if (btnCancelar) {
            btnCancelar.addEventListener('click', function(e) {
                e.preventDefault();
                modalCancelar.show();
            });
        }

        if (btnGuardar) {
            btnGuardar.addEventListener('click', function(e) {
                e.preventDefault();

                const inputsEditables = Array.from(inputsNotas).filter(i => i.dataset.editable === 'true');
                const inputsVacios = inputsEditables.filter(i => i.value.trim() === '');

                if (inputsVacios.length > 0) {
                    mostrarValidacion(
                        `Hay ${inputsVacios.length} ${inputsVacios.length === 1 ? 'calificación sin registrar' : 'calificaciones sin registrar'} en la columna activa.\n\nDebes completar TODAS las calificaciones de la columna activa antes de poder guardar. No se permite guardar parcialmente.`
                    );
                    return;
                }

                const notasPreviasVacias = validarNotasPrevias();
                if (notasPreviasVacias.length > 0) {
                    mostrarValidacion(
                        'No se puede guardar: las siguientes calificaciones ya estaban registradas y no pueden dejarse vacías:\n\n' + [...new Set(notasPreviasVacias)].join('\n')
                    );
                    return;
                }

                modalGuardar.show();
            });
        }

        inputsNotas.forEach(input => {
            input.addEventListener('input', function() {
                validarInput(this);
                const fila = this.closest('.fila-estudiante');
                calcularPromedio(fila);
                actualizarEstadisticas();
                actualizarProgreso();
            });

            input.addEventListener('blur', function() {
                if (this.value !== '') {
                    const num = parseFloat(this.value);
                    if (!isNaN(num)) {
                        this.value = num.toFixed(1);
                    }
                }
            });
        });

        filas.forEach(fila => calcularPromedio(fila));
        actualizarEstadisticas();
        actualizarProgreso();
    });
</script>
@endpush

@endsection