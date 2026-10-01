@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">

    <!-- Encabezado de la página -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-0">Gestión de promociones</h2>
            <span class="text-muted">Configuración de descuentos por pronto pago, becas y beneficios especiales</span>
        </div>
        <!-- Botón para abrir Modal de Nueva Promoción -->
        <button type="button" class="btn text-white px-4 py-2" style="background-color: #dc3545; border-radius: 6px;" data-bs-toggle="modal" data-bs-target="#modalCrearPromocion">
            <i class="fa-solid fa-plus me-2"></i> Nueva promoción
        </button>
    </div>

    <!-- Contenedor Principal (Tabla) -->
    <div class="card shadow-sm border-0" style="border-radius: 10px;">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="text-muted" style="background-color: #f8f9fa;">
                        <tr>
                            <th class="fw-semibold py-3 ps-3">Nombre y requisitos</th>
                            <th class="fw-semibold py-3">Descuento</th>
                            <th class="fw-semibold py-3">Aplica a (arancel)</th>
                            <th class="fw-semibold py-3">Vigencia</th>
                            <th class="fw-semibold py-3 text-center">Estado</th>
                            <th class="fw-semibold py-3 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @forelse($promociones as $promocion)
                            <tr>
                                <!-- 1. Nombre y Solvencia (Tu código adaptado) -->
                                <td class="ps-3 py-3">
                                    <span class="fw-bold text-dark d-block" style="font-size: 0.95rem;">
                                        {{ $promocion->nombre }}
                                    </span>

                                    <!-- Lógica visual para la solvencia -->
                                    @if($promocion->requiere_solvencia_hasta)
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 mt-1" style="font-size: 0.75rem;">
                                            <i class="fa-solid fa-file-invoice-dollar me-1"></i> Solvencia requerida al: {{ \Carbon\Carbon::parse($promocion->requiere_solvencia_hasta)->format('d/m/Y') }}
                                        </span>
                                    @else
                                        <small class="text-muted d-block mt-1" style="font-size: 0.8rem;">
                                            <i class="fa-solid fa-check-circle text-success me-1"></i> Sin restricción de solvencia
                                        </small>
                                    @endif
                                </td>

                                <!-- 2. Beneficio (Porcentaje) -->
                                <td class="py-3">
                                    <span class="badge bg-success px-3 py-2" style="font-size: 0.85rem;">
                                        <i class="fa-solid fa-percent me-1"></i> {{ floatval($promocion->porcentaje_descuento) }}% DESC.
                                    </span>
                                </td>

                                <!-- 3. Aplica a (Concepto) -->
                                <td class="py-3">
                                    @php
                                        $arancel = \App\Models\Adfinanciero\ConceptoPago::where('concepto_pago_id', $promocion->id_concepto_aplicable)->first();
                                    @endphp

                                    @if($arancel)
                                        <div style="max-width: 280px;">
                                            <!-- Icono sutil y el nombre original en mayúsculas directo de la BD -->
                                            <span class="text-dark fw-medium lh-sm d-block" style="font-size: 0.85rem;">
                                                <i class="fa-solid fa-tag text-secondary me-1" style="font-size: 0.8rem;"></i>
                                                {{ $arancel->nombre }}
                                            </span>
                                        </div>
                                    @else
                                        <span class="text-danger small fw-bold">
                                            <i class="fa-solid fa-triangle-exclamation"></i> Error de enlace
                                        </span>
                                    @endif
                                </td>

                                <!-- 4. Vigencia -->
                                <td class="py-3">
                                    @if($promocion->fecha_inicio && $promocion->fecha_fin)
                                        <div class="text-dark" style="font-size: 0.85rem;">
                                            <i class="fa-regular fa-calendar text-muted me-1"></i>
                                            {{ \Carbon\Carbon::parse($promocion->fecha_inicio)->format('d/m/Y') }} <br>
                                            <span class="text-muted ms-3">al {{ \Carbon\Carbon::parse($promocion->fecha_fin)->format('d/m/Y') }}</span>
                                        </div>
                                    @else
                                        <span class="badge bg-light text-dark border"><i class="fa-solid fa-infinity me-1"></i> Permanente</span>
                                    @endif
                                </td>

                                <!-- 5. Estado (Booleano con validación de expiración) -->
                                <td class="py-3 text-center">
                                    @php
                                        $expirada = $promocion->fecha_fin && \Carbon\Carbon::parse($promocion->fecha_fin)->isPast();
                                    @endphp

                                    @if($expirada)
                                        <span class="badge rounded-pill bg-secondary px-3 py-2 fw-normal">Expirada</span>
                                    @elseif($promocion->estado)
                                        <span class="badge rounded-pill bg-dark px-3 py-2 fw-normal">Activa</span>
                                    @else
                                        <span class="badge rounded-pill border text-danger px-3 py-2 fw-normal">Inactiva</span>
                                    @endif
                                </td>

                                <!-- 6. Acciones -->
                                <td class="py-3 text-center">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" title="Editar promoción" data-bs-toggle="modal" data-bs-target="#modalEditarPromocion{{ $promocion->promocion_id }}">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-5">
                                    <i class="fa-solid fa-tags fs-3 mb-3 d-block"></i>
                                    No hay promociones ni descuentos registrados en el sistema.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ==============================================
     ZONA DE MODALES (Aislada de la tabla)
=============================================== -->

<!-- MODAL: Crear Nueva Promoción -->
<div class="modal fade" id="modalCrearPromocion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Nueva promoción o descuento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('adfinanciero.promociones.store') }}" method="POST">
                @csrf
                <div class="modal-body">

                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label class="form-label fw-bold">Nombre de la promoción <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" class="form-control" placeholder="Ej. Pronto Pago Mensualidad" maxlength="150" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-dark">Porcentaje (%) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="porcentaje_descuento" class="form-control" placeholder="Ej. 10.00" max="100" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Aplica al arancel / concepto <span class="text-danger">*</span></label>
                        <select name="id_concepto_aplicable" class="form-select" required>
                            <option value="">-- Seleccione el arancel --</option>
                            @foreach($conceptos as $concepto)
                            <option value="{{ $concepto->concepto_pago_id }}">
                                [{{ $concepto->codigo_concepto }}] {{ $concepto->nombre }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-secondary"><i class="fa-regular fa-calendar"></i> Fecha inicio (opcional)</label>
                            <input type="datetime-local" name="fecha_inicio" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-secondary"><i class="fa-regular fa-calendar"></i> Fecha fin (opcional)</label>
                            <input type="datetime-local" name="fecha_fin" class="form-control">
                        </div>
                    </div>

                    <div class="row mb-3 p-3 bg-light rounded border">
                        <div class="col-md-12">
                            <label class="form-label fw-bold text-danger"><i class="fa-solid fa-lock me-1"></i> Requiere solvencia hasta (opcional)</label>
                            <input type="date" name="requiere_solvencia_hasta" class="form-control">
                            <small class="text-muted">El estudiante no recibirá el descuento si tiene cuotas pendientes anteriores a esta fecha.</small>
                        </div>
                    </div>

                    <!-- Estado Booleano -->
                    <input type="hidden" name="estado" value="1">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn text-white" style="background-color: #dc3545;">Guardar promoción</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODALES: Editar Promoción -->
@foreach($promociones as $promocion)
<div class="modal fade" id="modalEditarPromocion{{ $promocion->promocion_id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Editar: {{ $promocion->nombre }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('adfinanciero.promociones.update', $promocion->promocion_id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">

                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label class="form-label fw-bold">Nombre de la promoción <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" class="form-control" value="{{ $promocion->nombre }}" maxlength="150" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-dark">Porcentaje (%) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="porcentaje_descuento" class="form-control" value="{{ $promocion->porcentaje_descuento }}" max="100" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Aplica al arancel / concepto <span class="text-danger">*</span></label>
                        <select name="id_concepto_aplicable" class="form-select" required>
                            @foreach($conceptos as $concepto)
                            <option value="{{ $concepto->concepto_pago_id }}">
                                [{{ $concepto->codigo_concepto }}] {{ $concepto->nombre }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-secondary"><i class="fa-regular fa-calendar"></i> Fecha inicio</label>
                            <!-- Se formatea para el input datetime-local -->
                            <input type="datetime-local" name="fecha_inicio" class="form-control" value="{{ $promocion->fecha_inicio ? \Carbon\Carbon::parse($promocion->fecha_inicio)->format('Y-m-d\TH:i') : '' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-secondary"><i class="fa-regular fa-calendar"></i> Fecha fin</label>
                            <input type="datetime-local" name="fecha_fin" class="form-control" value="{{ $promocion->fecha_fin ? \Carbon\Carbon::parse($promocion->fecha_fin)->format('Y-m-d\TH:i') : '' }}">
                        </div>
                    </div>

                    <div class="row mb-3 p-3 bg-light rounded border">
                        <div class="col-md-12">
                            <label class="form-label fw-bold text-danger"><i class="fa-solid fa-lock me-1"></i> Requiere solvencia hasta</label>
                            <input type="date" name="requiere_solvencia_hasta" class="form-control" value="{{ $promocion->requiere_solvencia_hasta ? \Carbon\Carbon::parse($promocion->requiere_solvencia_hasta)->format('Y-m-d') : '' }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Estado</label>
                        <select name="estado" class="form-select">
                            <!-- 1 es true, 0 es false en PostgreSQL boolean -->
                            <option value="1" {{ $promocion->estado ? 'selected' : '' }}>Activo</option>
                            <option value="0" {{ !$promocion->estado ? 'selected' : '' }}>Inactivo</option>
                        </select>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn text-white" style="background-color: #dc3545;">Actualizar promoción</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

@endsection
