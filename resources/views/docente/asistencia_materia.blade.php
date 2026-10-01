@extends('layouts.app')

@php
// ============================================
// DATOS QUEMADOS (temporal)
// ============================================
$materia = $materia ?? [
'nombre' => 'Programación I',
'codigo' => 'INF-101',
'grupo' => 'A',
'aula' => 'Aula 12',
'horario' => 'Lun - Mié · 07:00 - 08:30'
];

$estudiantes = $estudiantes ?? [
['codigo' => 'STU-2024-001', 'nombre' => 'María González'],
['codigo' => 'STU-2024-002', 'nombre' => 'Juan Ramírez'],
['codigo' => 'STU-2024-003', 'nombre' => 'Ana López'],
['codigo' => 'STU-2024-004', 'nombre' => 'Carlos Mendoza'],
['codigo' => 'STU-2024-005', 'nombre' => 'Lucía Fernández'],
['codigo' => 'STU-2024-006', 'nombre' => 'Pedro Sánchez'],
['codigo' => 'STU-2024-007', 'nombre' => 'Isabella Castro'],
['codigo' => 'STU-2024-008', 'nombre' => 'Diego Herrera'],
];
@endphp

@section('title', 'Asistencia - ' . $materia['nombre'] . ' | NEXUM')

@section('content')
<div class="container-fluid p-4 p-lg-5">
    <div class="mx-auto" style="max-width: 1500px;">

        <!-- ============================================ -->
        <!-- ENCABEZADO + ACCIONES                        -->
        <!-- ============================================ -->
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
                        <i class="fa-solid fa-users me-1"></i>{{ count($estudiantes) }} estudiantes
                    </span>
                </div>
            </div>

            <!-- Selector de fecha + Botones -->
            <div class="d-flex gap-2 align-items-start flex-wrap">
                <div class="input-group" style="max-width: 200px;">
                    <span class="input-group-text bg-white border-end-0">
                        <i class="fa-regular fa-calendar text-danger"></i>
                    </span>
                    <input type="date" id="fechaAsistencia" class="form-control border-start-0" value="{{ date('Y-m-d') }}">
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
                    <span class="text-dark small">Registra la asistencia de todos los estudiantes. El botón "Guardar" se habilitará cuando todas las sesiones estén marcadas.</span>
                </div>
            </div>
        </div>

        <!-- ============================================ -->
        <!-- TABLA DE ASISTENCIA                          -->
        <!-- ============================================ -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <!-- Header de la tabla -->
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
                        <div class="fw-bold fs-5" id="progresoAsistencia">0 de 40</div>
                    </div>
                </div>
            </div>

            <!-- Tabla con scroll horizontal -->
            <div class="table-responsive">
                <table class="table align-middle mb-0 tabla-asistencia">
                    <thead>
                        <tr>
                            <th class="ps-4 fw-semibold text-dark sticky-col" style="min-width: 220px; left: 0; z-index: 10;">Estudiante</th>
                            <th class="text-center fw-semibold text-dark sticky-col-porcentaje" style="min-width: 110px; left: 220px; z-index: 10;">
                                <div>% Asistencia</div>
                                <small class="text-muted fw-normal th-sub">Mínimo 80%</small>
                            </th>
                            @for($i = 1; $i <= 40; $i++)
                                <th class="text-center fw-semibold text-dark sesion-header" style="min-width: 90px;">
                                <div>Sesión {{ $i }}</div>
                                </th>
                                @endfor
                        </tr>
                    </thead>
                    <tbody id="tablaEstudiantes">
                        @foreach($estudiantes as $estudiante)
                        <tr data-codigo="{{ $estudiante['codigo'] }}" class="fila-estudiante">
                            <td class="ps-4 sticky-col" style="left: 0; z-index: 5;">
                                <div class="d-flex align-items-center">
                                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle me-3 flex-shrink-0 avatar-asistencia">
                                        <span class="fw-bold small">{{ strtoupper(substr($estudiante['nombre'], 0, 1)) }}</span>
                                    </div>
                                    <div>
                                        <div class="fw-medium small mb-0">{{ $estudiante['nombre'] }}</div>
                                        <small class="text-muted">{{ $estudiante['codigo'] }}</small>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center sticky-col-porcentaje" style="left: 220px; z-index: 5;">
                                <span class="fw-bold fs-5 porcentaje-display">—</span>
                            </td>
                            @for($i = 1; $i <= 40; $i++)
                                <td class="text-center">
                                <select class="form-select form-select-sm asistencia-select" disabled>
                                    <option value="">—</option>
                                    <option value="Asis">Asis</option>
                                    <option value="Falt">Falt</option>
                                    <option value="Per">Per</option>
                                </select>
                                </td>
                                @endfor
                        </tr>
                        @endforeach
                    </tbody>
                </table>
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

        let modoEdicion = false;
        const TOTAL_SESIONES = 40;

        // ============================================
        // CÁLCULO DE PORCENTAJE POR FILA
        // ============================================
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

            // Color según el umbral del 80%
            if (porcentaje >= 80) {
                porcentajeDisplay.className = 'fw-bold fs-5 porcentaje-display nota-aprobada';
            } else {
                porcentajeDisplay.className = 'fw-bold fs-5 porcentaje-display nota-reprobada';
            }

            return porcentaje;
        }

        // ============================================
        // ESTADÍSTICAS GENERALES
        // ============================================
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

        // ============================================
        // PROGRESO Y VALIDACIÓN PARA GUARDAR
        // ============================================
        function actualizarProgreso() {
            const totalSelects = selectsAsistencia.length;
            const selectsMarcados = Array.from(selectsAsistencia).filter(s => s.value !== '').length;

            progresoAsistencia.textContent = `${selectsMarcados} de ${totalSelects}`;

            // Habilitar botón guardar solo si TODO está completo
            if (selectsMarcados === totalSelects && modoEdicion) {
                btnGuardar.disabled = false;
            } else {
                btnGuardar.disabled = true;
            }
        }

        // ============================================
        // MODO EDICIÓN
        // ============================================
        function activarModoEdicion() {
            modoEdicion = true;
            selectsAsistencia.forEach(s => s.disabled = false);
            btnEditar.classList.add('d-none');
            btnGuardar.classList.remove('d-none');
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

        // ============================================
        // EVENT LISTENERS
        // ============================================

        btnEditar.addEventListener('click', activarModoEdicion);

        btnCancelar.addEventListener('click', function() {
            if (confirm('¿Estás seguro de que deseas cancelar? Se perderán los cambios no guardados.')) {
                desactivarModoEdicion();
            }
        });

        btnGuardar.addEventListener('click', function() {
            const datos = [];
            filas.forEach(fila => {
                const selects = fila.querySelectorAll('.asistencia-select');
                const asistencias = [];
                selects.forEach(s => asistencias.push(s.value));

                datos.push({
                    codigo_estudiante: fila.dataset.codigo,
                    asistencias: asistencias
                });
            });
            console.log('Datos a guardar:', datos);

            alert('✓ Asistencia guardada correctamente (simulado).\n\nEn el futuro, estos datos se enviarán a la base de datos.');
            desactivarModoEdicion();
        });

        selectsAsistencia.forEach(select => {
            select.addEventListener('change', function() {
                const fila = this.closest('.fila-estudiante');
                calcularPorcentaje(fila);
                actualizarEstadisticas();
                actualizarProgreso();
            });
        });

        // Inicialización
        filas.forEach(fila => calcularPorcentaje(fila));
        actualizarEstadisticas();
        actualizarProgreso();
    });
</script>
@endpush

@endsection