@extends('layouts.app')

@section('content')
<div class="container-fluid px-0 py-4">
    <!-- Encabezado de la página -->
    <div class="mb-4">
        <h4 class="fw-bold text-dark d-flex align-items-center">
            <div class="bg-danger text-white rounded-3 p-2 me-3 d-flex justify-content-center align-items-center" style="width: 40px; height: 40px;">
                <i class="bi bi-receipt fs-5"></i>
            </div>
            Récord Financiero
        </h4>
        <p class="text-muted mb-0">Consulta tus credenciales de pago, solvencias y estado de cuenta del ciclo actual.</p>
    </div>

    <div class="row g-4 mb-4">
        <!-- Tarjeta 1: Saldo Pendiente -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-start mb-3">
                        <div class="bg-danger bg-opacity-10 text-danger rounded-3 p-3 me-3">
                            <i class="bi bi-wallet2 fs-4"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-1">Estado de Cuenta</h5>
                            <p class="text-muted small mb-0">Resumen de tu saldo actual en el ciclo lectivo.</p>
                        </div>
                    </div>
                    <div class="mt-4 d-flex justify-content-between align-items-end">
                        <div>
                            <h2 class="fw-bold text-dark mb-0">$70.00</h2>
                            <span class="text-muted small">Total a pagar</span>
                        </div>
                        <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-semibold">Pendiente</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tarjeta 2: Próximo Vencimiento -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-start mb-3">
                        <div class="bg-danger bg-opacity-10 text-danger rounded-3 p-3 me-3">
                            <i class="bi bi-calendar-x fs-4"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-1">Próximo Vencimiento</h5>
                            <p class="text-muted small mb-0">Fecha límite para evitar recargos por mora.</p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <h4 class="fw-bold text-dark mb-1">30 de Septiembre, 2026</h4>
                        <p class="text-muted small mb-0">Cuota Mensual - Ciclo 02</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tarjeta 3: Historial de Cargos (Tabla) -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
                <div>
                    <h5 class="fw-bold mb-1">Historial de Cargos y Pagos</h5>
                    <p class="text-muted small mb-0">Detalle completo de tus transacciones institucionales.</p>
                </div>
                <button class="btn btn-danger rounded-3 px-4 fw-semibold mt-3 mt-md-0">
                    <i class="bi bi-printer me-2"></i>Imprimir Solvencia
                </button>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-secondary fw-semibold py-3 rounded-start">Concepto</th>
                            <th class="text-secondary fw-semibold py-3">Ciclo</th>
                            <th class="text-secondary fw-semibold py-3">Monto</th>
                            <th class="text-secondary fw-semibold py-3">Vencimiento</th>
                            <th class="text-secondary fw-semibold py-3 rounded-end text-center">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Fila Pendiente -->
                        <tr>
                            <td class="py-3">
                                <span class="fw-bold text-dark d-block">Cuota Mensual 3</span>
                                <small class="text-muted">Octubre</small>
                            </td>
                            <td class="text-muted py-3">02-2026</td>
                            <td class="fw-bold py-3">$50.00</td>
                            <td class="text-muted py-3">15/10/2026</td>
                            <td class="text-center py-3">
                                <span class="badge bg-warning text-dark px-3 py-2 rounded-pill w-75">Pendiente</span>
                            </td>
                        </tr>
                        <!-- Fila Pagada -->
                        <tr>
                            <td class="py-3">
                                <span class="fw-bold text-dark d-block">Cuota Mensual 2</span>
                                <small class="text-muted">Septiembre</small>
                            </td>
                            <td class="text-muted py-3">02-2026</td>
                            <td class="fw-bold py-3">$50.00</td>
                            <td class="text-muted py-3">15/09/2026</td>
                            <td class="text-center py-3">
                                <span class="badge bg-success bg-opacity-10 text-success border border-success px-3 py-2 rounded-pill w-75">Pagado</span>
                            </td>
                        </tr>
                         <!-- Fila Pagada 2 -->
                         <tr>
                            <td class="py-3">
                                <span class="fw-bold text-dark d-block">Matrícula</span>
                                <small class="text-muted">Inscripción</small>
                            </td>
                            <td class="text-muted py-3">02-2026</td>
                            <td class="fw-bold py-3">$75.00</td>
                            <td class="text-muted py-3">15/07/2026</td>
                            <td class="text-center py-3">
                                <span class="badge bg-success bg-opacity-10 text-success border border-success px-3 py-2 rounded-pill w-75">Pagado</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
