@extends('layouts.app')

@section('title', 'Prueba de Notas - NEXUM')

@section('content')
<div class="container-fluid p-4">

    <!-- Encabezado de página -->
    <div class="mb-4">
        <h1 class="h3 fw-bold mb-1">Prueba de Notas</h1>
        <p class="text-muted mb-0">Simula las calificaciones de tus materias y visualiza el promedio final en tiempo real.</p>
    </div>

    <!-- Card Instructiva -->
    <div class="card border-0 shadow-sm rounded-4 mb-4" style="background-color: #fff5f5; border-left: 4px solid #dc3545 !important;">
        <div class="card-body p-4">
            <div class="d-flex align-items-start">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle me-3 flex-shrink-0"
                    style="width: 40px; height: 40px; background-color: rgba(220, 53, 69, 0.1);">
                    <i class="fa-solid fa-circle-info text-danger"></i>
                </div>
                <div>
                    <h6 class="fw-bold mb-2 text-danger">
                        <i class="fa-solid fa-calculator me-1"></i>
                        ¿Cómo se calcula el promedio de cada materia?
                    </h6>
                    <p class="mb-1 small text-dark">La evaluación se divide en <strong>3 períodos</strong>. Cada período incluye un <strong>Laboratorio (40%)</strong> y un <strong>Parcial (60%)</strong>.</p>
                    <ul class="mb-0 small text-dark ps-3">
                        <li><strong>Período 1:</strong> vale el 30% de la nota final</li>
                        <li><strong>Período 2:</strong> vale el 30% de la nota final</li>
                        <li><strong>Período 3:</strong> vale el 40% de la nota final</li>
                    </ul>
                    <p class="mt-2 mb-0 small text-muted">
                        <i class="fa-solid fa-circle-check text-success me-1"></i>
                        Se aprueba una materia con un promedio final de <strong>6.0 o superior</strong>.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Card con tabla de materias -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <!-- Header de la tabla -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center p-4 border-bottom gap-3">
                <div>
                    <h5 class="fw-bold mb-1">
                        <i class="fa-solid fa-book text-danger me-2"></i>
                        Mis Materias
                    </h5>
                    <small class="text-muted">
                        <i class="fa-solid fa-layer-group me-1"></i>
                        <span id="contadorMaterias">4</span> de 6 materias registradas
                    </small>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" id="btnAgregarMateria" class="btn btn-danger btn-sm">
                        <i class="fa-solid fa-plus me-1"></i>Agregar Materia
                    </button>
                    <button type="button" id="btnLimpiar" class="btn btn-outline-danger btn-sm">
                        <i class="fa-solid fa-eraser me-1"></i>Limpiar Notas
                    </button>
                </div>
            </div>

            <!-- Tabla con scroll horizontal en móvil -->
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4 fw-semibold text-dark" style="min-width: 200px;">Materia</th>
                            <th class="text-center fw-semibold text-dark" style="min-width: 110px;">
                                <div>Lab 1</div>
                                <small class="text-muted fw-normal">Período 1</small>
                            </th>
                            <th class="text-center fw-semibold text-dark" style="min-width: 110px;">
                                <div>Parcial 1</div>
                                <small class="text-muted fw-normal">Período 1</small>
                            </th>
                            <th class="text-center fw-semibold text-dark" style="min-width: 110px;">
                                <div>Lab 2</div>
                                <small class="text-muted fw-normal">Período 2</small>
                            </th>
                            <th class="text-center fw-semibold text-dark" style="min-width: 110px;">
                                <div>Parcial 2</div>
                                <small class="text-muted fw-normal">Período 2</small>
                            </th>
                            <th class="text-center fw-semibold text-dark" style="min-width: 110px;">
                                <div>Lab 3</div>
                                <small class="text-muted fw-normal">Período 3</small>
                            </th>
                            <th class="text-center fw-semibold text-dark" style="min-width: 110px;">
                                <div>Parcial 3</div>
                                <small class="text-muted fw-normal">Período 3</small>
                            </th>
                            <th class="text-center fw-semibold text-dark" style="min-width: 130px;">
                                Promedio Final
                            </th>
                            <th class="text-center fw-semibold text-dark" style="min-width: 130px;">
                                Estado
                            </th>
                            <th class="text-center fw-semibold text-dark pe-4" style="min-width: 80px;">
                                Acción
                            </th>
                        </tr>
                    </thead>
                    <tbody id="tablaMaterias">
                        <!-- Materia 1 -->
                        <tr data-materia="1" class="fila-materia">
                            <td class="ps-4">
                                <input type="text" class="form-control form-control-sm nombre-materia"
                                    value="Materia 1" placeholder="Nombre de la materia">
                            </td>
                            <td><input type="number" class="form-control form-control-sm text-center nota-input" step="0.1" min="0" max="10" placeholder="0.0"></td>
                            <td><input type="number" class="form-control form-control-sm text-center nota-input" step="0.1" min="0" max="10" placeholder="0.0"></td>
                            <td><input type="number" class="form-control form-control-sm text-center nota-input" step="0.1" min="0" max="10" placeholder="0.0"></td>
                            <td><input type="number" class="form-control form-control-sm text-center nota-input" step="0.1" min="0" max="10" placeholder="0.0"></td>
                            <td><input type="number" class="form-control form-control-sm text-center nota-input" step="0.1" min="0" max="10" placeholder="0.0"></td>
                            <td><input type="number" class="form-control form-control-sm text-center nota-input" step="0.1" min="0" max="10" placeholder="0.0"></td>
                            <td class="text-center">
                                <span class="fw-bold fs-5 promedio-display">—</span>
                            </td>
                            <td class="text-center">
                                <span class="estado-badge badge bg-secondary px-3 py-2">Sin calificar</span>
                            </td>
                            <td class="text-center pe-4">
                                <button type="button" class="btn btn-sm btn-outline-danger btn-quitar-materia" title="Quitar materia">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </td>
                        </tr>

                        <!-- Materia 2 -->
                        <tr data-materia="2" class="fila-materia">
                            <td class="ps-4">
                                <input type="text" class="form-control form-control-sm nombre-materia"
                                    value="Materia 2" placeholder="Nombre de la materia">
                            </td>
                            <td><input type="number" class="form-control form-control-sm text-center nota-input" step="0.1" min="0" max="10" placeholder="0.0"></td>
                            <td><input type="number" class="form-control form-control-sm text-center nota-input" step="0.1" min="0" max="10" placeholder="0.0"></td>
                            <td><input type="number" class="form-control form-control-sm text-center nota-input" step="0.1" min="0" max="10" placeholder="0.0"></td>
                            <td><input type="number" class="form-control form-control-sm text-center nota-input" step="0.1" min="0" max="10" placeholder="0.0"></td>
                            <td><input type="number" class="form-control form-control-sm text-center nota-input" step="0.1" min="0" max="10" placeholder="0.0"></td>
                            <td><input type="number" class="form-control form-control-sm text-center nota-input" step="0.1" min="0" max="10" placeholder="0.0"></td>
                            <td class="text-center">
                                <span class="fw-bold fs-5 promedio-display">—</span>
                            </td>
                            <td class="text-center">
                                <span class="estado-badge badge bg-secondary px-3 py-2">Sin calificar</span>
                            </td>
                            <td class="text-center pe-4">
                                <button type="button" class="btn btn-sm btn-outline-danger btn-quitar-materia" title="Quitar materia">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </td>
                        </tr>

                        <!-- Materia 3 -->
                        <tr data-materia="3" class="fila-materia">
                            <td class="ps-4">
                                <input type="text" class="form-control form-control-sm nombre-materia"
                                    value="Materia 3" placeholder="Nombre de la materia">
                            </td>
                            <td><input type="number" class="form-control form-control-sm text-center nota-input" step="0.1" min="0" max="10" placeholder="0.0"></td>
                            <td><input type="number" class="form-control form-control-sm text-center nota-input" step="0.1" min="0" max="10" placeholder="0.0"></td>
                            <td><input type="number" class="form-control form-control-sm text-center nota-input" step="0.1" min="0" max="10" placeholder="0.0"></td>
                            <td><input type="number" class="form-control form-control-sm text-center nota-input" step="0.1" min="0" max="10" placeholder="0.0"></td>
                            <td><input type="number" class="form-control form-control-sm text-center nota-input" step="0.1" min="0" max="10" placeholder="0.0"></td>
                            <td><input type="number" class="form-control form-control-sm text-center nota-input" step="0.1" min="0" max="10" placeholder="0.0"></td>
                            <td class="text-center">
                                <span class="fw-bold fs-5 promedio-display">—</span>
                            </td>
                            <td class="text-center">
                                <span class="estado-badge badge bg-secondary px-3 py-2">Sin calificar</span>
                            </td>
                            <td class="text-center pe-4">
                                <button type="button" class="btn btn-sm btn-outline-danger btn-quitar-materia" title="Quitar materia">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </td>
                        </tr>

                        <!-- Materia 4 -->
                        <tr data-materia="4" class="fila-materia">
                            <td class="ps-4">
                                <input type="text" class="form-control form-control-sm nombre-materia"
                                    value="Materia 4" placeholder="Nombre de la materia">
                            </td>
                            <td><input type="number" class="form-control form-control-sm text-center nota-input" step="0.1" min="0" max="10" placeholder="0.0"></td>
                            <td><input type="number" class="form-control form-control-sm text-center nota-input" step="0.1" min="0" max="10" placeholder="0.0"></td>
                            <td><input type="number" class="form-control form-control-sm text-center nota-input" step="0.1" min="0" max="10" placeholder="0.0"></td>
                            <td><input type="number" class="form-control form-control-sm text-center nota-input" step="0.1" min="0" max="10" placeholder="0.0"></td>
                            <td><input type="number" class="form-control form-control-sm text-center nota-input" step="0.1" min="0" max="10" placeholder="0.0"></td>
                            <td><input type="number" class="form-control form-control-sm text-center nota-input" step="0.1" min="0" max="10" placeholder="0.0"></td>
                            <td class="text-center">
                                <span class="fw-bold fs-5 promedio-display">—</span>
                            </td>
                            <td class="text-center">
                                <span class="estado-badge badge bg-secondary px-3 py-2">Sin calificar</span>
                            </td>
                            <td class="text-center pe-4">
                                <button type="button" class="btn btn-sm btn-outline-danger btn-quitar-materia" title="Quitar materia">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Footer con promedio general de materias -->
            <div class="p-4 border-top" style="background-color: #fff9e1;">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div>
                        <div class="small text-muted mb-1">
                            <i class="fa-solid fa-chart-line me-1 text-danger"></i>
                            Promedio General de Materias
                        </div>
                        <div class="small text-muted">
                            Calculado en base al promedio final de cada materia registrada.
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-4">
                        <div class="text-center">
                            <div class="small text-muted mb-1">Materias Aprobadas</div>
                            <div class="fw-bold fs-5 text-success" id="totalAprobadas">0</div>
                        </div>
                        <div class="text-center">
                            <div class="small text-muted mb-1">Materias Reprobadas</div>
                            <div class="fw-bold fs-5 text-danger" id="totalReprobadas">0</div>
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
        const tablaMaterias = document.getElementById('tablaMaterias');
        const btnAgregar = document.getElementById('btnAgregarMateria');
        const btnLimpiar = document.getElementById('btnLimpiar');
        const contadorMaterias = document.getElementById('contadorMaterias');

        const MIN_MATERIAS = 4;
        const MAX_MATERIAS = 6;
        let contadorId = 4; // Para generar IDs únicos de nuevas materias

        /**
         * Valida que solo se ingrese un número con máximo 1 decimal entre 0 y 10.
         */
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

        /**
         * Calcula el promedio final de una fila (materia).
         */
        function calcularPromedio(fila) {
            const inputs = fila.querySelectorAll('.nota-input');
            const promedioDisplay = fila.querySelector('.promedio-display');
            const estadoBadge = fila.querySelector('.estado-badge');

            const valores = Array.from(inputs).map(input => {
                const val = parseFloat(input.value);
                return isNaN(val) ? null : val;
            });

            const tieneNotas = valores.some(v => v !== null);

            if (!tieneNotas) {
                promedioDisplay.textContent = '—';
                promedioDisplay.className = 'fw-bold fs-5 promedio-display';
                estadoBadge.textContent = 'Sin calificar';
                estadoBadge.className = 'estado-badge badge bg-secondary px-3 py-2';
                return null;
            }

            const [lab1, par1, lab2, par2, lab3, par3] = valores.map(v => v === null ? 0 : v);

            const periodo1 = (lab1 * 0.4 + par1 * 0.6) * 0.3;
            const periodo2 = (lab2 * 0.4 + par2 * 0.6) * 0.3;
            const periodo3 = (lab3 * 0.4 + par3 * 0.6) * 0.4;

            const promedioFinal = periodo1 + periodo2 + periodo3;
            const promedioRedondeado = parseFloat(promedioFinal.toFixed(1));

            promedioDisplay.textContent = promedioRedondeado.toFixed(1);

            if (promedioRedondeado >= 6) {
                promedioDisplay.className = 'fw-bold fs-5 promedio-display text-success';
                estadoBadge.innerHTML = '<i class="fa-solid fa-circle-check me-1"></i>Aprobada';
                estadoBadge.className = 'estado-badge badge bg-success px-3 py-2';
            } else {
                promedioDisplay.className = 'fw-bold fs-5 promedio-display text-danger';
                estadoBadge.innerHTML = '<i class="fa-solid fa-circle-xmark me-1"></i>Reprobada';
                estadoBadge.className = 'estado-badge badge bg-danger px-3 py-2';
            }

            return promedioRedondeado;
        }

        /**
         * Actualiza el promedio general de todas las materias.
         */
        function actualizarPromedioGeneral() {
            const filas = document.querySelectorAll('.fila-materia');
            let sumaPromedios = 0;
            let totalCalificadas = 0;
            let aprobadas = 0;
            let reprobadas = 0;

            filas.forEach(fila => {
                const promedioText = fila.querySelector('.promedio-display').textContent;
                if (promedioText !== '—') {
                    const promedio = parseFloat(promedioText);
                    totalCalificadas++;
                    sumaPromedios += promedio;
                    if (promedio >= 6) {
                        aprobadas++;
                    } else {
                        reprobadas++;
                    }
                }
            });

            const promedioGeneralEl = document.getElementById('promedioGeneral');
            const totalAprobadasEl = document.getElementById('totalAprobadas');
            const totalReprobadasEl = document.getElementById('totalReprobadas');

            if (totalCalificadas > 0) {
                const promedioGen = (sumaPromedios / totalCalificadas).toFixed(1);
                promedioGeneralEl.textContent = promedioGen;
                promedioGeneralEl.className = promedioGen >= 6 ?
                    'fw-bold fs-3 text-success' :
                    'fw-bold fs-3 text-danger';
            } else {
                promedioGeneralEl.textContent = '—';
                promedioGeneralEl.className = 'fw-bold fs-3 text-danger';
            }

            totalAprobadasEl.textContent = aprobadas;
            totalReprobadasEl.textContent = reprobadas;
        }

        /**
         * Actualiza los contadores y el estado de los botones.
         */
        function actualizarControles() {
            const filas = document.querySelectorAll('.fila-materia');
            const total = filas.length;
            contadorMaterias.textContent = total;

            // Deshabilitar agregar si llegó al máximo
            if (total >= MAX_MATERIAS) {
                btnAgregar.disabled = true;
                btnAgregar.classList.add('disabled');
            } else {
                btnAgregar.disabled = false;
                btnAgregar.classList.remove('disabled');
            }

            // Deshabilitar botones de quitar si está en el mínimo
            const botonesQuitar = document.querySelectorAll('.btn-quitar-materia');
            botonesQuitar.forEach(btn => {
                if (total <= MIN_MATERIAS) {
                    btn.disabled = true;
                    btn.classList.add('disabled');
                    btn.title = 'Mínimo 4 materias';
                } else {
                    btn.disabled = false;
                    btn.classList.remove('disabled');
                    btn.title = 'Quitar materia';
                }
            });
        }

        /**
         * Adjunta los event listeners a una fila nueva.
         */
        function configurarFila(fila) {
            fila.querySelectorAll('.nota-input').forEach(input => {
                input.addEventListener('input', function() {
                    validarInput(this);
                    calcularPromedio(fila);
                    actualizarPromedioGeneral();
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

            const btnQuitar = fila.querySelector('.btn-quitar-materia');
            if (btnQuitar) {
                btnQuitar.addEventListener('click', function() {
                    const totalFilas = document.querySelectorAll('.fila-materia').length;
                    if (totalFilas <= MIN_MATERIAS) {
                        alert(`No se pueden tener menos de ${MIN_MATERIAS} materias.`);
                        return;
                    }
                    if (confirm('¿Estás seguro de que deseas quitar esta materia?')) {
                        fila.remove();
                        actualizarControles();
                        actualizarPromedioGeneral();
                    }
                });
            }
        }

        /**
         * Genera el HTML de una nueva fila de materia.
         */
        function crearFilaMateria(numero) {
            const tr = document.createElement('tr');
            tr.setAttribute('data-materia', numero);
            tr.className = 'fila-materia';
            tr.innerHTML = `
            <td class="ps-4">
                <input type="text" class="form-control form-control-sm nombre-materia" 
                       value="Materia ${numero}" placeholder="Nombre de la materia">
            </td>
            <td><input type="number" class="form-control form-control-sm text-center nota-input" step="0.1" min="0" max="10" placeholder="0.0"></td>
            <td><input type="number" class="form-control form-control-sm text-center nota-input" step="0.1" min="0" max="10" placeholder="0.0"></td>
            <td><input type="number" class="form-control form-control-sm text-center nota-input" step="0.1" min="0" max="10" placeholder="0.0"></td>
            <td><input type="number" class="form-control form-control-sm text-center nota-input" step="0.1" min="0" max="10" placeholder="0.0"></td>
            <td><input type="number" class="form-control form-control-sm text-center nota-input" step="0.1" min="0" max="10" placeholder="0.0"></td>
            <td><input type="number" class="form-control form-control-sm text-center nota-input" step="0.1" min="0" max="10" placeholder="0.0"></td>
            <td class="text-center">
                <span class="fw-bold fs-5 promedio-display">—</span>
            </td>
            <td class="text-center">
                <span class="estado-badge badge bg-secondary px-3 py-2">Sin calificar</span>
            </td>
            <td class="text-center pe-4">
                <button type="button" class="btn btn-sm btn-outline-danger btn-quitar-materia" title="Quitar materia">
                    <i class="fa-solid fa-trash-can"></i>
                </button>
            </td>
        `;
            return tr;
        }

        // Configurar filas iniciales
        document.querySelectorAll('.fila-materia').forEach(configurarFila);

        // Botón agregar materia
        btnAgregar.addEventListener('click', function() {
            const totalFilas = document.querySelectorAll('.fila-materia').length;
            if (totalFilas >= MAX_MATERIAS) {
                alert(`No puedes agregar más de ${MAX_MATERIAS} materias.`);
                return;
            }

            contadorId++;
            const nuevaFila = crearFilaMateria(contadorId);
            tablaMaterias.appendChild(nuevaFila);
            configurarFila(nuevaFila);
            actualizarControles();

            // Auto-focus en el input de nombre de la nueva materia
            nuevaFila.querySelector('.nombre-materia').focus();
            nuevaFila.querySelector('.nombre-materia').select();
        });

        // Botón limpiar todas las notas
        btnLimpiar.addEventListener('click', function() {
            const tieneNotas = Array.from(document.querySelectorAll('.nota-input')).some(i => i.value !== '');
            if (!tieneNotas) return;

            if (confirm('¿Estás seguro de que deseas borrar todas las calificaciones?')) {
                document.querySelectorAll('.nota-input').forEach(input => {
                    input.value = '';
                });

                document.querySelectorAll('.fila-materia').forEach(fila => {
                    const promedioDisplay = fila.querySelector('.promedio-display');
                    const estadoBadge = fila.querySelector('.estado-badge');
                    promedioDisplay.textContent = '—';
                    promedioDisplay.className = 'fw-bold fs-5 promedio-display';
                    estadoBadge.textContent = 'Sin calificar';
                    estadoBadge.className = 'estado-badge badge bg-secondary px-3 py-2';
                });

                actualizarPromedioGeneral();
            }
        });

        // Inicializar estado de controles
        actualizarControles();
    });
</script>
@endpush

@endsection