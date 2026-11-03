@extends('layouts.app')

@section('title', 'Asistencia - ' . $materia['nombre'] . ' | NEXUM')

@section('content')
<div class="container-fluid p-4 p-lg-5">
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
                    <span class="badge rounded-pill bg-light text-danger border">Grupo {{ $materia['grupo'] }}</span>
                    <span class="badge rounded-pill bg-light text-muted border">
                        <i class="fa-solid fa-users me-1"></i>{{ $materia['total_estudiantes'] }} estudiantes
                    </span>
                </div>
            </div>

            <!-- Selector de fecha (para crear nueva sesión) + Botones -->
            <div class="d-flex gap-2 align-items-start flex-wrap">
                <div class="input-group" style="max-width: 200px;">
                    <span class="input-group-text bg-white border-end-0">
                        <i class="fa-regular fa-calendar text-danger"></i>
                    </span>
                    <input type="date" id="fechaSesion" class="form-control border-start-0" value="{{ date('Y-m-d') }}">
                </div>
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
            <div class="d-flex align-items-center">
                <i class="fa-solid fa-circle-info text-danger me-3 fs-5"></i>
                <div>
                    <strong class="text-dark">Modo edición activo.</strong>
                    <span class="text-dark small">Registra la asistencia de todos los estudiantes para la sesión seleccionada.</span>
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
                            <th class="text-center fw-semibold text-dark" style="min-width: 90px;">
                                <div>{{ $sesion->fecha->format('d/m') }}</div>
                                <small class="text-muted fw-normal th-sub">Sesión</small>
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
                            @endphp
                            <td class="text-center">
                                <select class="form-select form-select-sm asistencia-select"
                                    data-sesion-id="{{ $sesion->clase_sesion_id }}"
                                    data-detalle-id="{{ $estudiante->detalle_inscripcion_id }}"
                                    disabled>
                                    <option value="">—</option>
                                    <option value="Asis" {{ $estadoActual === 'Asis' ? 'selected' : '' }}>Asis</option>
                                    <option value="Falt" {{ $estadoActual === 'Falt' ? 'selected' : '' }}>Falt</option>
                                    <option value="Per" {{ $estadoActual === 'Per' ? 'selected' : '' }}>Per</option>
                                </select>
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

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const btnEditar = document.getElementById('btnEditar');
        const btnGuardar = document.getElementById('btnGuardar');
        const btnCancelar = document.getElementById('btnCancelar');
        const alertaEdicion = document.getElementById('alertaEdicion');
        const selectsAsistencia = document.querySelectorAll('.asistencia-select');
        const filas = document.querySelectorAll('.fila-estudiante');
        const progresoAsistencia = document.getElementById('progresoAsistencia');
        const seccionId = document.querySelector('[data-seccion-id]').dataset.seccionId;
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        let modoEdicion = false;

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

        function actualizarProgreso() {
            const totalSelects = selectsAsistencia.length;
            const selectsMarcados = Array.from(selectsAsistencia).filter(s => s.value !== '').length;
            progresoAsistencia.textContent = `${selectsMarcados} de ${totalSelects}`;

            if (selectsMarcados === totalSelects && modoEdicion) {
                btnGuardar.disabled = false;
            } else {
                btnGuardar.disabled = true;
            }
        }

        function activarModoEdicion() {
            modoEdicion = true;
            selectsAsistencia.forEach(s => s.disabled = false);
            btnEditar.classList.add('d-none');
            btnGuardar.classList.remove('d-none');
            btnGuardar.disabled = false;
            btnCancelar.classList.remove('d-none');
            alertaEdicion.classList.remove('d-none');
            actualizarProgreso();
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

        if (btnEditar) {
            btnEditar.addEventListener('click', function() {
                activarModoEdicion();
            });
        }

        if (btnCancelar) {
            btnCancelar.addEventListener('click', function() {
                if (confirm('¿Estás seguro de que deseas cancelar? Se perderán los cambios no guardados.')) {
                    location.reload();
                }
            });
        }

        if (btnGuardar) {
            btnGuardar.addEventListener('click', async function() {
                if (!confirm('¿Estás seguro de que deseas guardar la asistencia?')) {
                    return;
                }

                const asistenciasData = [];
                selectsAsistencia.forEach(select => {
                    if (select.value !== '') {
                        asistenciasData.push({
                            id_detalle_inscripcion: parseInt(select.dataset.detalleId),
                            estado: select.value,
                            observacion: null
                        });
                    }
                });

                // Obtenemos el ID de la primera sesión (en un sistema real, esto vendría de un selector de sesión activa)
                const primeraSesion = selectsAsistencia[0]?.dataset.sesionId;

                if (!primeraSesion || asistenciasData.length === 0) {
                    alert('⚠️ No hay datos de asistencia para guardar.');
                    return;
                }

                btnGuardar.disabled = true;
                btnGuardar.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Guardando...';

                try {
                    const response = await fetch(`/docente/asistencia/${seccionId}/guardar`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            id_sesion: parseInt(primeraSesion),
                            asistencias: asistenciasData
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        alert('✅ ' + data.message);
                        location.reload();
                    } else {
                        alert('❌ ' + (data.message || 'Error al guardar.'));
                        btnGuardar.disabled = false;
                        btnGuardar.innerHTML = '<i class="fa-solid fa-floppy-disk me-2"></i>Guardar';
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('❌ Error de conexión al guardar.');
                    btnGuardar.disabled = false;
                    btnGuardar.innerHTML = '<i class="fa-solid fa-floppy-disk me-2"></i>Guardar';
                }
            });
        }

        selectsAsistencia.forEach(select => {
            select.addEventListener('change', function() {
                const fila = this.closest('.fila-estudiante');
                calcularPorcentaje(fila);
                actualizarEstadisticas();
                actualizarProgreso();
            });
        });

        filas.forEach(fila => calcularPorcentaje(fila));
        actualizarEstadisticas();
        actualizarProgreso();
    });
</script>
@endpush
@endsection