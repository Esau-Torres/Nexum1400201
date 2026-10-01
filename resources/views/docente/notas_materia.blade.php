@extends('layouts.app')

@php
// ============================================
// DATOS QUEMADOS (temporal)
// DEFINIDOS PRIMERO para que estén disponibles
// en todas las secciones de la vista
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

@section('title', 'Notas - ' . $materia['nombre'] . ' | NEXUM')

@section('content')
<div class="container-fluid p-4 p-lg-5">
    <div class="mx-auto" style="max-width: 1300px;">

        <!-- ============================================ -->
        <!-- ENCABEZADO + ACCIONES                        -->
        <!-- ============================================ -->
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-3 mb-4">
            <div>
                <!-- Botón volver -->
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

            <!-- Botones de acción -->
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
            <div class="d-flex align-items-center">
                <i class="fa-solid fa-circle-info text-danger me-3 fs-5"></i>
                <div>
                    <strong class="text-dark">Modo edición activo.</strong>
                    <span class="text-dark small">Ingresa las calificaciones de todos los estudiantes. El botón "Guardar" se habilitará cuando todas las notas estén completas.</span>
                </div>
            </div>
        </div>

        <!-- ============================================ -->
        <!-- TABLA DE NOTAS                               -->
        <!-- ============================================ -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <!-- Header de la tabla -->
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

            <!-- Tabla -->
            <div class="table-responsive">
                <table class="table align-middle mb-0 tabla-notas-materia">
                    <thead>
                        <tr>
                            <th class="ps-4 fw-semibold text-dark" style="min-width: 220px;">Estudiante</th>
                            <th class="text-center fw-semibold text-dark" style="min-width: 85px;">
                                <div>Lab 1</div>
                                <small class="text-muted fw-normal th-sub">Período 1</small>
                            </th>
                            <th class="text-center fw-semibold text-dark" style="min-width: 85px;">
                                <div>Parcial 1</div>
                                <small class="text-muted fw-normal th-sub">Período 1</small>
                            </th>
                            <th class="text-center fw-semibold text-dark" style="min-width: 85px;">
                                <div>Lab 2</div>
                                <small class="text-muted fw-normal th-sub">Período 2</small>
                            </th>
                            <th class="text-center fw-semibold text-dark" style="min-width: 85px;">
                                <div>Parcial 2</div>
                                <small class="text-muted fw-normal th-sub">Período 2</small>
                            </th>
                            <th class="text-center fw-semibold text-dark" style="min-width: 85px;">
                                <div>Lab 3</div>
                                <small class="text-muted fw-normal th-sub">Período 3</small>
                            </th>
                            <th class="text-center fw-semibold text-dark" style="min-width: 85px;">
                                <div>Parcial 3</div>
                                <small class="text-muted fw-normal th-sub">Período 3</small>
                            </th>
                            <th class="text-center fw-semibold text-dark" style="min-width: 100px;">
                                <div>Promedio</div>
                                <small class="text-muted fw-normal th-sub">3 decimales</small>
                            </th>
                            <th class="text-center fw-semibold text-dark pe-4" style="min-width: 100px;">
                                <div>Nota Final</div>
                                <small class="text-muted fw-normal th-sub">1 decimal</small>
                            </th>
                        </tr>
                    </thead>
                    <tbody id="tablaEstudiantes">
                        @foreach($estudiantes as $estudiante)
                        <tr data-codigo="{{ $estudiante['codigo'] }}" class="fila-estudiante">
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle me-3 flex-shrink-0 avatar-notas">
                                        <span class="fw-bold small">{{ strtoupper(substr($estudiante['nombre'], 0, 1)) }}</span>
                                    </div>
                                    <div>
                                        <div class="fw-medium small mb-0">{{ $estudiante['nombre'] }}</div>
                                        <small class="text-muted">{{ $estudiante['codigo'] }}</small>
                                    </div>
                                </div>
                            </td>
                            <td><input type="number" class="form-control form-control-sm text-center nota-input" step="0.1" min="0" max="10" placeholder="0.0" disabled></td>
                            <td><input type="number" class="form-control form-control-sm text-center nota-input" step="0.1" min="0" max="10" placeholder="0.0" disabled></td>
                            <td><input type="number" class="form-control form-control-sm text-center nota-input" step="0.1" min="0" max="10" placeholder="0.0" disabled></td>
                            <td><input type="number" class="form-control form-control-sm text-center nota-input" step="0.1" min="0" max="10" placeholder="0.0" disabled></td>
                            <td><input type="number" class="form-control form-control-sm text-center nota-input" step="0.1" min="0" max="10" placeholder="0.0" disabled></td>
                            <td><input type="number" class="form-control form-control-sm text-center nota-input" step="0.1" min="0" max="10" placeholder="0.0" disabled></td>
                            <td class="text-center"><span class="promedio-raw-display">—</span></td>
                            <td class="text-center pe-4"><span class="fw-bold fs-5 promedio-display">—</span></td>
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

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const btnEditar = document.getElementById('btnEditar');
        const btnGuardar = document.getElementById('btnGuardar');
        const btnCancelar = document.getElementById('btnCancelar');
        const alertaEdicion = document.getElementById('alertaEdicion');
        const inputsNotas = document.querySelectorAll('.nota-input');
        const filas = document.querySelectorAll('.fila-estudiante');
        const progresoNotas = document.getElementById('progresoNotas');

        let modoEdicion = false;

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
                const promedioText = fila.querySelector('.promedio-display').textContent;
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
            document.getElementById('totalAprobados').textContent = aprobados;
            document.getElementById('totalReprobados').textContent = reprobados;

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

        function actualizarProgreso() {
            const totalInputs = inputsNotas.length;
            const inputsLlenos = Array.from(inputsNotas).filter(input => input.value.trim() !== '').length;
            const porcentaje = Math.round((inputsLlenos / totalInputs) * 100);

            progresoNotas.textContent = porcentaje + '%';

            if (inputsLlenos === totalInputs && modoEdicion) {
                btnGuardar.disabled = false;
            } else {
                btnGuardar.disabled = true;
            }
        }

        function activarModoEdicion() {
            modoEdicion = true;
            inputsNotas.forEach(input => input.disabled = false);
            btnEditar.classList.add('d-none');
            btnGuardar.classList.remove('d-none');
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

        btnEditar.addEventListener('click', activarModoEdicion);

        btnCancelar.addEventListener('click', function() {
            if (confirm('¿Estás seguro de que deseas cancelar? Se perderán los cambios no guardados.')) {
                desactivarModoEdicion();
            }
        });

        btnGuardar.addEventListener('click', function() {
            const datos = [];
            filas.forEach(fila => {
                const inputs = fila.querySelectorAll('.nota-input');
                datos.push({
                    codigo_estudiante: fila.dataset.codigo,
                    lab1: inputs[0].value,
                    parcial1: inputs[1].value,
                    lab2: inputs[2].value,
                    parcial2: inputs[3].value,
                    lab3: inputs[4].value,
                    parcial3: inputs[5].value
                });
            });
            console.log('Datos a guardar:', datos);

            alert('✓ Notas guardadas correctamente (simulado).\n\nEn el futuro, estos datos se enviarán a la base de datos.');
            desactivarModoEdicion();
        });

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