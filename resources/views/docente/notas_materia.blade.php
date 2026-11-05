@extends('layouts.app')

@section('title', 'Notas - ' . $materia['nombre'] . ' | NEXUM')

@section('content')
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
                    <span class="badge rounded-pill bg-light text-danger border">Grupo {{ $materia['grupo'] }}</span>
                    <span class="badge rounded-pill bg-light text-muted border">
                        <i class="fa-solid fa-calendar me-1"></i>{{ $materia['ciclo'] }}
                    </span>
                    <span class="badge rounded-pill bg-light text-muted border">
                        <i class="fa-solid fa-users me-1"></i>{{ $materia['total_estudiantes'] }} estudiantes
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

        <!-- Mensajes flash -->
        @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">
            <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">
            <i class="fa-solid fa-circle-xmark me-2"></i>{{ session('error') }}
        </div>
        @endif

        <!-- Alerta de modo edición -->
        <div id="alertaEdicion" class="alert border-0 shadow-sm rounded-4 mb-4 d-none" style="background-color: #fff5f5;">
            <div class="d-flex align-items-center">
                <i class="fa-solid fa-circle-info text-danger me-3 fs-5"></i>
                <div>
                    <strong class="text-dark">Modo edición activo.</strong>
                    <span class="text-dark small">Puedes modificar las notas libremente. Se te pedirá confirmación antes de guardar. Las notas previamente guardadas no pueden dejarse vacías.</span>
                </div>
            </div>
        </div>

        <!-- TABLA DE NOTAS -->
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

            <!-- Tabla con scroll horizontal -->
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
                            <th class="text-center fw-semibold text-dark" style="min-width: 95px;">
                                <div>{{ $evaluacion->nombre_evaluacion }}</div>
                                <small class="text-muted fw-normal th-sub">
                                    {{ $categoria->tipo_categoria }} · P{{ $periodoData['periodo']->numero_periodo }}
                                </small>
                            </th>
                            @endforeach
                            @endforeach
                            @endforeach
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
                            @endphp
                            <td class="text-center">
                                <input type="number"
                                    class="form-control form-control-sm text-center nota-input"
                                    step="0.1" min="0" max="10" placeholder="0.0"
                                    data-evaluacion-id="{{ $evaluacion->evaluacion_id }}"
                                    data-detalle-inscripcion-id="{{ $estudiante->detalle_inscripcion_id }}"
                                    @if(!is_null($notaPrevia)) data-nota-previa="true" @endif
                                    value="{{ $notaPrevia ?? '' }}"
                                    disabled>
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

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('✅ Script de notas cargado correctamente');

        // Obtener datos desde atributos data-* del HTML
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

        console.log('Elementos encontrados:', {
            btnEditar: !!btnEditar,
            btnGuardar: !!btnGuardar,
            btnCancelar: !!btnCancelar,
            inputsNotas: inputsNotas.length,
            filas: filas.length,
            seccionId: seccionId
        });

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
            console.log('🟢 Activando modo edición');
            modoEdicion = true;
            inputsNotas.forEach(input => input.disabled = false);
            btnEditar.classList.add('d-none');
            btnGuardar.classList.remove('d-none');
            btnGuardar.disabled = false;
            btnCancelar.classList.remove('d-none');
            alertaEdicion.classList.remove('d-none');
            actualizarProgreso();
            console.log('✅ Modo edición activado');
        }

        function desactivarModoEdicion() {
            console.log('🔴 Desactivando modo edición');
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

        // Event listener del botón Editar
        if (btnEditar) {
            btnEditar.addEventListener('click', function(e) {
                e.preventDefault();
                console.log('🖱️ Click en botón Editar');
                activarModoEdicion();
            });
        } else {
            console.error('❌ Botón Editar no encontrado');
        }

        // Event listener del botón Cancelar
        if (btnCancelar) {
            btnCancelar.addEventListener('click', function(e) {
                e.preventDefault();
                if (confirm('¿Estás seguro de que deseas cancelar? Se perderán los cambios no guardados.')) {
                    location.reload();
                }
            });
        }

        // Event listener del botón Guardar
        if (btnGuardar) {
            btnGuardar.addEventListener('click', async function(e) {
                e.preventDefault();
                console.log('🖱️ Click en botón Guardar');

                const notasPreviasVacias = validarNotasPrevias();
                if (notasPreviasVacias.length > 0) {
                    alert('❌ No se puede guardar: las siguientes calificaciones ya estaban registradas y no pueden dejarse vacías:\n\n' + [...new Set(notasPreviasVacias)].join('\n'));
                    return;
                }

                if (!confirm('¿Estás seguro de que deseas guardar los cambios en las calificaciones?')) {
                    return;
                }

                const evaluacionesMap = {};
                inputsNotas.forEach(input => {
                    const evaluacionId = input.dataset.evaluacionId;
                    const detalleInscripcionId = input.dataset.detalleInscripcionId;
                    const nota = input.value.trim();
                    if (nota === '') return;
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
                    alert('⚠️ No hay calificaciones para guardar.');
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
                        alert('✅ ' + data.message);
                        location.reload();
                    } else {
                        alert('❌ ' + (data.message || 'Error al guardar las calificaciones.'));
                        btnGuardar.disabled = false;
                        btnGuardar.innerHTML = '<i class="fa-solid fa-floppy-disk me-2"></i>Guardar';
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('❌ Error de conexión al guardar las calificaciones.');
                    btnGuardar.disabled = false;
                    btnGuardar.innerHTML = '<i class="fa-solid fa-floppy-disk me-2"></i>Guardar';
                }
            });
        } else {
            console.error('❌ Botón Guardar no encontrado');
        }

        // Event listeners de los inputs
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

        // Inicialización
        filas.forEach(fila => calcularPromedio(fila));
        actualizarEstadisticas();
        actualizarProgreso();

        console.log('✅ Script inicializado correctamente');
    });
</script>
@endpush

@endsection