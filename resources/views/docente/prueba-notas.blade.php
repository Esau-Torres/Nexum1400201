@extends('layouts.app')

@section('title', 'Prueba de Notas - NEXUM')

@section('content')
<div class="container-fluid p-4 p-lg-5">
    <div class="mx-auto" style="max-width: 1400px;">

        <!-- ============================================ -->
        <!-- ENCABEZADO CENTRADO                          -->
        <!-- ============================================ -->
        <div class="text-center mb-5">
            <h1 class="display-5 fw-bold mb-3" style="letter-spacing: -0.5px;">Prueba de Notas</h1>
            <div class="mx-auto rounded mb-4" style="width: 50px; height: 3px; background-color: #dc3545;"></div>

            <!-- Card Instructiva centrada, fondo blanco -->
            <div class="card border shadow-sm rounded-4 mx-auto" style="max-width: 800px;">
                <div class="card-body p-4">
                    <p class="text-dark mb-3 fw-medium">
                        <i class="fa-solid fa-calculator text-danger me-2"></i>
                        Cada período: <strong>Laboratorio (40%)</strong> + <strong>Parcial (60%)</strong>.
                        Períodos 1 y 2 valen <strong>30%</strong> cada uno, Período 3 vale <strong>40%</strong>.
                        Se aprueba con <strong>6.0 o superior</strong>.
                    </p>
                </div>
            </div>
        </div>

        <!-- ============================================ -->
        <!-- CARD CON TABLA DE MATERIAS                  -->
        <!-- ============================================ -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <!-- Header de la tabla -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center p-4 p-lg-4 gap-3"
                style="background-color: #ffffff;">
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
                    <button type="button" id="btnAgregarMateria" class="btn btn-danger btn-sm rounded-3 px-3">
                        <i class="fa-solid fa-plus me-1"></i>Agregar Materia
                    </button>
                    <button type="button" id="btnLimpiar" class="btn btn-outline-danger btn-sm rounded-3 px-3">
                        <i class="fa-solid fa-eraser me-1"></i>Limpiar Notas
                    </button>
                </div>
            </div>

            <!-- Tabla -->
            <div class="table-responsive px-3 px-lg-4 pb-2">
                <table class="table align-middle mb-0 tabla-notas">
                    <thead>
                        <tr>
                            <th class="ps-3 fw-semibold text-dark" style="min-width: 190px;">Materia</th>
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
                            <th class="text-center fw-semibold text-dark" style="min-width: 100px;">
                                <div>Nota Final</div>
                                <small class="text-muted fw-normal th-sub">1 decimal</small>
                            </th>
                            <th class="text-center fw-semibold text-dark" style="min-width: 110px;">Estado</th>
                            <th class="text-center fw-semibold text-dark pe-3" style="min-width: 65px;">Acción</th>
                        </tr>
                    </thead>
                    <tbody id="tablaMaterias">
                        <!-- Materia 1 -->
                        <tr data-materia="1" class="fila-materia">
                            <td class="ps-3">
                                <input type="text" class="form-control form-control-sm nombre-materia"
                                    value="Materia 1" placeholder="Nombre de la materia">
                            </td>
                            <td><input type="number" class="form-control form-control-sm text-center nota-input" step="0.1" min="0" max="10" placeholder="0.0"></td>
                            <td><input type="number" class="form-control form-control-sm text-center nota-input" step="0.1" min="0" max="10" placeholder="0.0"></td>
                            <td><input type="number" class="form-control form-control-sm text-center nota-input" step="0.1" min="0" max="10" placeholder="0.0"></td>
                            <td><input type="number" class="form-control form-control-sm text-center nota-input" step="0.1" min="0" max="10" placeholder="0.0"></td>
                            <td><input type="number" class="form-control form-control-sm text-center nota-input" step="0.1" min="0" max="10" placeholder="0.0"></td>
                            <td><input type="number" class="form-control form-control-sm text-center nota-input" step="0.1" min="0" max="10" placeholder="0.0"></td>
                            <td class="text-center"><span class="promedio-raw-display">—</span></td>
                            <td class="text-center"><span class="fw-bold fs-5 promedio-display">—</span></td>
                            <td class="text-center"><span class="estado-badge badge bg-secondary">Sin calificar</span></td>
                            <td class="text-center pe-3">
                                <button type="button" class="btn btn-sm btn-quitar-materia" title="Quitar materia">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </td>
                        </tr>

                        <!-- Materia 2 -->
                        <tr data-materia="2" class="fila-materia">
                            <td class="ps-3">
                                <input type="text" class="form-control form-control-sm nombre-materia"
                                    value="Materia 2" placeholder="Nombre de la materia">
                            </td>
                            <td><input type="number" class="form-control form-control-sm text-center nota-input" step="0.1" min="0" max="10" placeholder="0.0"></td>
                            <td><input type="number" class="form-control form-control-sm text-center nota-input" step="0.1" min="0" max="10" placeholder="0.0"></td>
                            <td><input type="number" class="form-control form-control-sm text-center nota-input" step="0.1" min="0" max="10" placeholder="0.0"></td>
                            <td><input type="number" class="form-control form-control-sm text-center nota-input" step="0.1" min="0" max="10" placeholder="0.0"></td>
                            <td><input type="number" class="form-control form-control-sm text-center nota-input" step="0.1" min="0" max="10" placeholder="0.0"></td>
                            <td><input type="number" class="form-control form-control-sm text-center nota-input" step="0.1" min="0" max="10" placeholder="0.0"></td>
                            <td class="text-center"><span class="promedio-raw-display">—</span></td>
                            <td class="text-center"><span class="fw-bold fs-5 promedio-display">—</span></td>
                            <td class="text-center"><span class="estado-badge badge bg-secondary">Sin calificar</span></td>
                            <td class="text-center pe-3">
                                <button type="button" class="btn btn-sm btn-quitar-materia" title="Quitar materia">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </td>
                        </tr>

                        <!-- Materia 3 -->
                        <tr data-materia="3" class="fila-materia">
                            <td class="ps-3">
                                <input type="text" class="form-control form-control-sm nombre-materia"
                                    value="Materia 3" placeholder="Nombre de la materia">
                            </td>
                            <td><input type="number" class="form-control form-control-sm text-center nota-input" step="0.1" min="0" max="10" placeholder="0.0"></td>
                            <td><input type="number" class="form-control form-control-sm text-center nota-input" step="0.1" min="0" max="10" placeholder="0.0"></td>
                            <td><input type="number" class="form-control form-control-sm text-center nota-input" step="0.1" min="0" max="10" placeholder="0.0"></td>
                            <td><input type="number" class="form-control form-control-sm text-center nota-input" step="0.1" min="0" max="10" placeholder="0.0"></td>
                            <td><input type="number" class="form-control form-control-sm text-center nota-input" step="0.1" min="0" max="10" placeholder="0.0"></td>
                            <td><input type="number" class="form-control form-control-sm text-center nota-input" step="0.1" min="0" max="10" placeholder="0.0"></td>
                            <td class="text-center"><span class="promedio-raw-display">—</span></td>
                            <td class="text-center"><span class="fw-bold fs-5 promedio-display">—</span></td>
                            <td class="text-center"><span class="estado-badge badge bg-secondary">Sin calificar</span></td>
                            <td class="text-center pe-3">
                                <button type="button" class="btn btn-sm btn-quitar-materia" title="Quitar materia">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </td>
                        </tr>

                        <!-- Materia 4 -->
                        <tr data-materia="4" class="fila-materia">
                            <td class="ps-3">
                                <input type="text" class="form-control form-control-sm nombre-materia"
                                    value="Materia 4" placeholder="Nombre de la materia">
                            </td>
                            <td><input type="number" class="form-control form-control-sm text-center nota-input" step="0.1" min="0" max="10" placeholder="0.0"></td>
                            <td><input type="number" class="form-control form-control-sm text-center nota-input" step="0.1" min="0" max="10" placeholder="0.0"></td>
                            <td><input type="number" class="form-control form-control-sm text-center nota-input" step="0.1" min="0" max="10" placeholder="0.0"></td>
                            <td><input type="number" class="form-control form-control-sm text-center nota-input" step="0.1" min="0" max="10" placeholder="0.0"></td>
                            <td><input type="number" class="form-control form-control-sm text-center nota-input" step="0.1" min="0" max="10" placeholder="0.0"></td>
                            <td><input type="number" class="form-control form-control-sm text-center nota-input" step="0.1" min="0" max="10" placeholder="0.0"></td>
                            <td class="text-center"><span class="promedio-raw-display">—</span></td>
                            <td class="text-center"><span class="fw-bold fs-5 promedio-display">—</span></td>
                            <td class="text-center"><span class="estado-badge badge bg-secondary">Sin calificar</span></td>
                            <td class="text-center pe-3">
                                <button type="button" class="btn btn-sm btn-quitar-materia" title="Quitar materia">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Footer con promedio general de materias -->
            <div class="p-4 p-lg-4" style="background-color: #fff9e1; border-top: 1px solid #f1f3f5;">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div>
                        <div class="fw-semibold mb-1 text-dark">
                            <i class="fa-solid fa-chart-line me-2 text-danger"></i>
                            Promedio General de Materias
                        </div>
                        <div class="small text-muted">
                            Calculado en base al promedio redondeado de cada materia registrada.
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-4">
                        <div class="text-center">
                            <div class="small text-muted mb-1">Aprobadas</div>
                            <div class="fw-bold fs-5 nota-aprobada" id="totalAprobadas">0</div>
                        </div>
                        <div class="text-center">
                            <div class="small text-muted mb-1">Reprobadas</div>
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

@push('styles')
<style>
    /* ========== TABLA DE NOTAS ========== */
    .tabla-notas {
        border-collapse: separate;
        border-spacing: 0 10px;
    }

    .tabla-notas thead th {
        padding: 10px 8px;
        font-size: 0.85rem;
        white-space: nowrap;
        border: none;
        color: #495057;
    }

    .tabla-notas thead th .th-sub {
        font-size: 0.68rem;
        display: block;
        margin-top: 1px;
    }

    /* Filas tipo card con separación - AUMENTADAS DE ALTURA */
    .tabla-notas tbody td {
        padding: 28px 8px;
        background-color: #fafbfc;
        border-top: 1px solid #eef0f2;
        border-bottom: 1px solid #eef0f2;
        vertical-align: middle;
        transition: background-color 0.2s ease;
    }

    .tabla-notas tbody td:first-child {
        border-left: 1px solid #eef0f2;
        border-radius: 14px 0 0 14px;
    }

    .tabla-notas tbody td:last-child {
        border-right: 1px solid #eef0f2;
        border-radius: 0 14px 14px 0;
    }

    .tabla-notas tbody tr:hover td {
        background-color: #f5f6f8;
    }

    /* ========== INPUTS DE NOTAS (MÁS ESTRECHOS) ========== */
    .tabla-notas .nota-input {
        width: 54px;
        height: 44px;
        border: 1.5px solid #e2e6ea;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.95rem;
        transition: all 0.2s ease;
        background-color: #ffffff;
        margin: 0 auto;
        display: block;
        color: #212529;
    }

    .tabla-notas .nota-input:focus {
        border-color: #dc3545;
        box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.12);
        outline: none;
        background-color: #fff5f5;
    }

    .tabla-notas .nota-input:hover:not(:focus) {
        border-color: #ced4da;
    }

    /* Quitar flechas del input number */
    .tabla-notas .nota-input::-webkit-outer-spin-button,
    .tabla-notas .nota-input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    .tabla-notas .nota-input[type=number] {
        -moz-appearance: textfield;
        appearance: textfield;
    }

    /* ========== INPUT DE NOMBRE ========== */
    .tabla-notas .nombre-materia {
        border: 1.5px solid transparent;
        background-color: #f1f3f5;
        border-radius: 10px;
        font-weight: 600;
        height: 44px;
        transition: all 0.2s ease;
        color: #212529;
    }

    .tabla-notas .nombre-materia:hover {
        background-color: #e9ecef;
    }

    .tabla-notas .nombre-materia:focus {
        border-color: #dc3545;
        background-color: #ffffff;
        box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.1);
    }

    /* ========== COLUMNAS DE PROMEDIO ========== */
    .promedio-raw-display {
        font-size: 0.9rem;
        font-weight: 500;
        color: #adb5bd;
        font-family: 'Courier New', monospace;
    }

    /* ========== COLORES DE ESTADO ========== */
    .nota-aprobada {
        color: #16a34a !important;
    }

    .nota-reprobada {
        color: #dc3545 !important;
    }

    /* ========== BADGE DE ESTADO ========== */
    .estado-badge {
        font-size: 0.72rem;
        font-weight: 600;
        border-radius: 8px;
        min-width: 98px;
        padding: 8px 12px !important;
    }

    .badge-aprobado {
        background-color: #dcfce7 !important;
        color: #16a34a !important;
    }

    .badge-reprobado {
        background-color: #fde8ea !important;
        color: #dc3545 !important;
    }

    .badge-sin-calificar {
        background-color: #e9ecef !important;
        color: #6c757d !important;
    }

    /* ========== BOTÓN QUITAR MATERIA ========== */
    .btn-quitar-materia {
        width: 34px;
        height: 34px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        border: 1.5px solid #f1c0c4;
        color: #dc3545;
        background-color: #ffffff;
        transition: all 0.2s ease;
    }

    .btn-quitar-materia:hover:not(:disabled) {
        background-color: #dc3545;
        border-color: #dc3545;
        color: #ffffff;
        transform: scale(1.05);
    }

    .btn-quitar-materia:disabled {
        opacity: 0.35;
        cursor: not-allowed;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tablaMaterias = document.getElementById('tablaMaterias');
        const btnAgregar = document.getElementById('btnAgregarMateria');
        const btnLimpiar = document.getElementById('btnLimpiar');
        const contadorMaterias = document.getElementById('contadorMaterias');

        const MIN_MATERIAS = 4;
        const MAX_MATERIAS = 6;
        let contadorId = 4;

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
            const estadoBadge = fila.querySelector('.estado-badge');

            const valores = Array.from(inputs).map(input => {
                const val = parseFloat(input.value);
                return isNaN(val) ? null : val;
            });

            const tieneNotas = valores.some(v => v !== null);

            if (!tieneNotas) {
                promedioRawDisplay.textContent = '—';
                promedioDisplay.textContent = '—';
                promedioDisplay.className = 'fw-bold fs-5 promedio-display';
                estadoBadge.textContent = 'Sin calificar';
                estadoBadge.className = 'estado-badge badge badge-sin-calificar';
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
                estadoBadge.innerHTML = '<i class="fa-solid fa-circle-check me-1"></i>Aprobada';
                estadoBadge.className = 'estado-badge badge badge-aprobado';
            } else {
                promedioDisplay.className = 'fw-bold fs-5 promedio-display nota-reprobada';
                estadoBadge.innerHTML = '<i class="fa-solid fa-circle-xmark me-1"></i>Reprobada';
                estadoBadge.className = 'estado-badge badge badge-reprobado';
            }

            return promedioRedondeado;
        }

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
                const promedioGenExacto = sumaPromedios / totalCalificadas;
                const promedioGen = redondearEspecial(promedioGenExacto, 1);
                promedioGeneralEl.textContent = promedioGen.toFixed(1);
                promedioGeneralEl.className = promedioGen >= 6 ?
                    'fw-bold fs-3 nota-aprobada' :
                    'fw-bold fs-3 text-danger';
            } else {
                promedioGeneralEl.textContent = '—';
                promedioGeneralEl.className = 'fw-bold fs-3 text-danger';
            }

            totalAprobadasEl.textContent = aprobadas;
            totalReprobadasEl.textContent = reprobadas;
        }

        function actualizarControles() {
            const filas = document.querySelectorAll('.fila-materia');
            const total = filas.length;
            contadorMaterias.textContent = total;

            if (total >= MAX_MATERIAS) {
                btnAgregar.disabled = true;
                btnAgregar.classList.add('disabled');
            } else {
                btnAgregar.disabled = false;
                btnAgregar.classList.remove('disabled');
            }

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

        function crearFilaMateria(numero) {
            const tr = document.createElement('tr');
            tr.setAttribute('data-materia', numero);
            tr.className = 'fila-materia';
            tr.innerHTML = `
                <td class="ps-3">
                    <input type="text" class="form-control form-control-sm nombre-materia" 
                           value="Materia ${numero}" placeholder="Nombre de la materia">
                </td>
                <td><input type="number" class="form-control form-control-sm text-center nota-input" step="0.1" min="0" max="10" placeholder="0.0"></td>
                <td><input type="number" class="form-control form-control-sm text-center nota-input" step="0.1" min="0" max="10" placeholder="0.0"></td>
                <td><input type="number" class="form-control form-control-sm text-center nota-input" step="0.1" min="0" max="10" placeholder="0.0"></td>
                <td><input type="number" class="form-control form-control-sm text-center nota-input" step="0.1" min="0" max="10" placeholder="0.0"></td>
                <td><input type="number" class="form-control form-control-sm text-center nota-input" step="0.1" min="0" max="10" placeholder="0.0"></td>
                <td><input type="number" class="form-control form-control-sm text-center nota-input" step="0.1" min="0" max="10" placeholder="0.0"></td>
                <td class="text-center"><span class="promedio-raw-display">—</span></td>
                <td class="text-center"><span class="fw-bold fs-5 promedio-display">—</span></td>
                <td class="text-center"><span class="estado-badge badge badge-sin-calificar">Sin calificar</span></td>
                <td class="text-center pe-3">
                    <button type="button" class="btn btn-sm btn-quitar-materia" title="Quitar materia">
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                </td>
            `;
            return tr;
        }

        document.querySelectorAll('.fila-materia').forEach(configurarFila);

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

            nuevaFila.querySelector('.nombre-materia').focus();
            nuevaFila.querySelector('.nombre-materia').select();
        });

        btnLimpiar.addEventListener('click', function() {
            const tieneNotas = Array.from(document.querySelectorAll('.nota-input')).some(i => i.value !== '');
            if (!tieneNotas) return;

            if (confirm('¿Estás seguro de que deseas borrar todas las calificaciones?')) {
                document.querySelectorAll('.nota-input').forEach(input => {
                    input.value = '';
                });

                document.querySelectorAll('.fila-materia').forEach(fila => {
                    const promedioRawDisplay = fila.querySelector('.promedio-raw-display');
                    const promedioDisplay = fila.querySelector('.promedio-display');
                    const estadoBadge = fila.querySelector('.estado-badge');

                    promedioRawDisplay.textContent = '—';
                    promedioDisplay.textContent = '—';
                    promedioDisplay.className = 'fw-bold fs-5 promedio-display';
                    estadoBadge.textContent = 'Sin calificar';
                    estadoBadge.className = 'estado-badge badge badge-sin-calificar';
                });

                actualizarPromedioGeneral();
            }
        });

        actualizarControles();
    });
</script>
@endpush

@endsection