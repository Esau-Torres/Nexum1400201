@extends('layouts.app')

@section('title', 'Administración de Usuarios - NEXUM UMA')

@section('content')
<div class="container-fluid py-4" style="background-color: var(--bg-main); min-height: 100vh;">
    
    {{-- Header del Módulo --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3 card p-4 rounded-4 border-0 shadow-sm" style="background-color: #ffffff;">
        <div>
            <h4 class="fw-bold mb-1" style="color: var(--text-primary);">
                <i class="bi bi-people-fill me-2" style="color: var(--color-accent);"></i>USUARIOS REGISTRADOS
            </h4>
            <span class="text-muted small">Panel de Control y Administración de Cuentas Institucionales UMA</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('superadmin.createuser') }}" class="btn text-white fw-medium shadow-sm" style="background-color: var(--color-accent);">
                <i class="bi bi-person-plus-fill me-1"></i> Nuevo Usuario
            </a>
        </div>
    </div>

    {{-- Tabla Principal --}}
    <div class="card border-0 rounded-4 shadow-sm overflow-hidden" style="background-color: #ffffff;">
        <div class="card-body p-4">
            <div>
                <table id="usersTable" class="table table-hover align-middle mb-0 w-100" style="font-size: 0.9rem;">
                    <thead style="background-color: var(--bg-main); color: var(--text-primary); border-bottom: 2px solid #e2e8f0;">
                        <tr>
                            <th class="ps-3 py-3 fw-semibold">Usuario</th>
                            <th class="py-3 fw-semibold">Correo Institucional</th>
                            <th class="py-3 fw-semibold">Roles Asignados</th>
                            <th class="py-3 fw-semibold">Sede UMA</th>
                            <th class="py-3 fw-semibold text-center">Estado</th>
                            <th class="pe-3 py-3 fw-semibold text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($usuarios as $u)
                            <tr>
                                <td class="ps-3 py-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-sm" 
                                             style="width: 38px; height: 38px; min-width: 38px; background-color: var(--color-accent); font-size: 0.85rem;">
                                            {{ strtoupper(substr($u->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark mb-0">{{ $u->name }}</div>
                                            <small class="text-muted font-monospace" style="font-size: 0.75rem;">ID: UMA-{{ str_pad($u->id, 5, '0', STR_PAD_LEFT) }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 font-monospace text-muted">{{ $u->email }}</td>
                                <td class="py-3">
                                    @forelse($u->roles as $rol)
                                        <span class="badge border fw-medium me-1" style="background-color: var(--color-hover-bg); color: var(--color-accent); font-size: 0.75rem;">
                                            {{ $rol->nombre }}
                                        </span>
                                    @empty
                                        <span class="badge bg-light text-muted border" style="font-size: 0.75rem;">Sin rol</span>
                                    @endforelse
                                </td>
                                <td class="py-3">
                                    <span class="text-dark small">
                                        <i class="bi bi-geo-alt-fill me-1" style="color: var(--color-accent);"></i>
                                        {{ $u->regionalactivo->sede ?? 'No Asignada' }} 
                                    </span>
                                </td>
                                <td class="py-3 text-center">
                                    @if($u->estado === 1)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3">Activada</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3">Inactivo</span>
                                    @endif
                                </td>
                                <td class="pe-3 py-3 text-end">
                                    <div class="btn-group gap-1">
                                        <button type="button" class="btn btn-sm btn-light border text-primary" data-bs-toggle="modal" data-bs-target="#editUserModal{{ $u->id }}" title="Modificar Datos Personales">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-light border text-secondary" data-bs-toggle="modal" data-bs-target="#viewDetailsModal{{ $u->id }}" title="Ver Expediente Completo">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Modales generados dinámicamente --}}
@foreach ($usuarios as $u)
     @php
        $editandoEste = session('edit_user_id') == $u->id;
        $old = fn($key, $default) => $editandoEste ? old($key, $default) : $default;
    @endphp

    {{-- Modal Edición de Datos Personales --}}
    <div class="modal fade" id="editUserModal{{ $u->id }}" tabindex="-1" aria-labelledby="editUserLabel{{ $u->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-bottom px-4 py-3" style="background-color: var(--bg-main);">
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="editUserLabel{{ $u->id }}" style="color: var(--text-primary);">
                            Modificar Información Personal
                        </h5>
                        <small class="text-muted">ID Usuario: UMA-{{ str_pad($u->id, 5, '0', STR_PAD_LEFT) }} | {{ $u->name }}</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <form action="{{ route('superadmin.users.update', $u->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body p-4">
                        <div class="alert p-2 d-flex align-items-center mb-4 rounded-3 border" style="background-color: var(--color-special); font-size: 0.8rem;">
                            <i class="bi bi-shield-lock-fill me-2 fs-6" style="color: var(--color-accent);"></i>
                            <span>Tipo de documento, género y estado civil no son modificables desde esta interfaz según normativas institucionales.</span>
                        </div>

                        {{-- Contenedor reactivo Alpine para heredar máscaras y validaciones --}}
                        <div class="row g-3" x-data="validation">
                            {{-- CAMPOS NO MODIFICABLES (Tipo Documento, Género, Estado Civil) --}}
                            <div class="col-12 col-md-4">
                                <label class="form-label small fw-bold text-muted mb-1">Tipo de Documento</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light text-muted border"><i class="bi bi-lock"></i></span>
                                    <input type="text" class="form-control bg-light text-muted border" value="{{ $u->tipodocumentoidentidad->nombre ?? 'DUI' }}" disabled readonly>
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label small fw-bold text-muted mb-1">Género</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light text-muted border"><i class="bi bi-lock"></i></span>
                                    <input type="text" class="form-control bg-light text-muted border" value="{{ $u->genero === 'M' ? 'Masculino' : ($u->genero === 'F' ? 'Femenino' : $u->genero) }}" disabled readonly>
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label small fw-bold text-muted mb-1">Estado Civil</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light text-muted border"><i class="bi bi-lock"></i></span>
                                    <input type="text" class="form-control bg-light text-muted border" value="{{ $u->estado_civil ?? 'No especificado' }}" disabled readonly>
                                </div>
                            </div>

                            {{-- SECCIÓN: CAMPOS EDITABLES --}}
                            <div class="col-12 mt-3 mb-1">
                                <h6 class="small fw-bold border-bottom pb-1" style="color: var(--color-accent);">Datos Actualizables</h6>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-semibold text-dark mb-1">Nombre Completo <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control form-control-sm @if($editandoEste) @error('name') is-invalid @enderror @endif" value="{{ $old('name', $u->name) }}" required>
                                  @if($editandoEste)
                                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                @endif
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-semibold text-dark mb-1">Correo Institucional <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control form-control-sm @if($editandoEste) @error('email') is-invalid @enderror @endif" value="{{ $old('email', $u->email) }}" required>
                                @if($editandoEste)
                                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                @endif
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label small fw-semibold text-dark mb-1">Número de Documento <span class="text-danger">*</span></label>
                                <input
                                    type="text"
                                    name="documento_identidad"
                                    id="documento_identidad_{{ $u->id }}"
                                    class="form-control form-control-sm @if($editandoEste) @error('documento_identidad') is-invalid @enderror @endif"
                                    value="{{ $old('documento_identidad', $u->documento_identidad) }}"
                                    required>
                                @if($editandoEste)
                                    @error('documento_identidad') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                @endif
                            </div>

                            {{-- Fecha de Nacimiento resuelta con Carbon seguro --}}
                            <div class="col-12 col-md-4">
                                <label class="form-label small fw-semibold text-dark mb-1">Fecha de Nacimiento <span class="text-danger">*</span></label>
                                @php
                                    $fechaNac = $u->fecha_nacimiento
                                        ? \Carbon\Carbon::parse($u->fecha_nacimiento)->format('Y-m-d')
                                        : '';
                                @endphp
                                <input type="date" name="fecha_nacimiento"
                                       id="fecha_nacimiento_{{ $u->id }}"
                                       class="form-control form-control-sm @if($editandoEste) @error('fecha_nacimiento') is-invalid @enderror @endif"
                                       value="{{ $old('fecha_nacimiento', $fechaNac) }}"
                                       :max="fechaLimite" required>
                                @if($editandoEste)
                                    @error('fecha_nacimiento') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                @endif
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label small fw-semibold text-dark mb-1">Celular</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">+503</span>
                                    <input type="text" name="celular" id="celular_{{ $u->id }}" 
                                            class="form-control form-control-sm @if($editandoEste) @error('celular') is-invalid @enderror @endif" 
                                            value="{{ $old('celular', $u->celular) }}" 
                                            pattern="[0-9]{4}-[0-9]{4}" 
                                            placeholder="0000-0000" 
                                            maxlength="9" 
                                            x-on:input="aplicarMascara($event)">
                                    @if($editandoEste)
                                        @error('celular') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    @endif
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-semibold text-dark mb-1">Sede Regional UMA <span class="text-danger">*</span></label>
                                <select class="form-select form-select-sm @if($editandoEste) @error('id_regional_activo') is-invalid @enderror @endif"
                                        name="id_regional_activo" required>
                                    @foreach($regionales as $rg)
                                        <option value="{{ $rg->regional_activo_id }}"
                                            {{ $old('id_regional_activo', $u->id_regional_activo) == $rg->regional_activo_id ? 'selected' : '' }}>
                                            {{ $rg->sede }}
                                        </option>
                                    @endforeach
                                </select>
                                @if($editandoEste)
                                    @error('id_regional_activo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                @endif
                            </div>

                            {{-- Estado: Protegido si el usuario autenticado es el mismo --}}
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-semibold text-dark mb-1">Estado de Cuenta <span class="text-danger">*</span></label>
                                @if(auth()->id() === $u->id)
                                    <div class="input-group input-group-sm">
                                        <input type="text" class="form-control bg-light text-muted border"
                                               value="Activo (Cuenta en Sesión)" disabled readonly>
                                        <input type="hidden" name="estado" value="1">
                                        <span class="input-group-text bg-light text-muted border"
                                              title="No puedes alterar el estado de tu propia cuenta activa">
                                            <i class="bi bi-lock-fill"></i>
                                        </span>
                                    </div>
                                @else
                                    <select class="form-select form-select-sm @if($editandoEste) @error('estado') is-invalid @enderror @endif"
                                            name="estado" required>
                                        <option value="1" {{ $old('estado', (string)$u->estado) === '1' ? 'selected' : '' }}>Activo</option>
                                        <option value="0" {{ $old('estado', (string)$u->estado) === '0' ? 'selected' : '' }}>Inactivo</option>
                                    </select>
                                    @if($editandoEste)
                                        @error('estado') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    @endif
                                @endif
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-semibold text-dark mb-1">Dirección de Residencia</label>
                                <textarea name="direccion"
                                          class="form-control form-control-sm @if($editandoEste) @error('direccion') is-invalid @enderror @endif"
                                          rows="2">{{ $old('direccion', $u->direccion) }}</textarea>
                                @if($editandoEste)
                                    @error('direccion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 bg-light px-4 py-3 rounded-bottom-4">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-sm text-white fw-medium shadow-sm" style="background-color: var(--color-accent);">
                            Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Expediente (Regla 2: Solo lectura y sección condicional para DOCENTE) --}}
    <div class="modal fade" id="viewDetailsModal{{ $u->id }}" tabindex="-1" aria-labelledby="viewDetailsLabel{{ $u->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-bottom px-4 py-3" style="background-color: var(--bg-main);">
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="viewDetailsLabel{{ $u->id }}" style="color: var(--text-primary);">
                            EXPEDIENTE INSTITUCIONAL — UMA
                        </h5>
                        <span class="text-muted small">ID Registro: UMA-{{ str_pad($u->id, 5, '0', STR_PAD_LEFT) }} | Solo Lectura</span>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="d-flex align-items-center gap-3 p-3 rounded-3 mb-3" style="background-color: var(--color-hover-bg); border: 1px solid rgba(220, 53, 69, 0.2);">
                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold" style="width: 48px; height: 48px; background-color: var(--color-accent);">
                            {{ strtoupper(substr($u->name, 0, 2)) }}
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0 text-dark">{{ $u->name }}</h6>
                            <small class="text-muted">{{ $u->email }}</small>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <div class="p-3 rounded-3 bg-light border h-100">
                                <span class="text-muted d-block small fw-bold mb-3">DATOS PERSONALES Y DOCUMENTACIÓN:</span>
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="small fw-semibold">Tipo de Documento:</span>
                                    <span class="small text-dark">{{ $u->tipodocumentoidentidad->nombre ?? 'N/D' }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="small fw-semibold">Número Documento:</span>
                                    <span class="small text-dark font-monospace">{{ $u->documento_identidad ?? 'No especificado' }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="small fw-semibold">Género:</span>
                                    <span class="small text-dark">{{ $u->genero ?? 'Indefinido' }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="small fw-semibold">Estado Civil:</span>
                                    <span class="small text-dark">{{ $u->estado_civil ?? 'No especificado' }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="small fw-semibold">Fecha Nacimiento:</span>
                                    <span class="small text-dark">{{ $u->fecha_nacimiento ? \Carbon\Carbon::parse($u->fecha_nacimiento)->format('d/m/Y') : 'No especificado' }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="small fw-semibold">Celular:</span>
                                    <span class="small text-dark">{{ $u->celular ?? 'N/D' }}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="small fw-semibold">Dirección:</span>
                                    <span class="small text-dark text-end ms-2">{{ $u->direccion ?? 'N/D' }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="p-3 rounded-3 bg-light border h-100">
                                <span class="text-muted d-block small fw-bold mb-3">SEGURIDAD Y ROLES:</span>
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="small fw-semibold">Sede UMA:</span>
                                    <span class="small text-dark">{{ $u->regionalactivo->sede ?? 'N/D' }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="small fw-semibold">Estado del Usuario:</span>
                                    <span class="badge {{ $u->estado === 1 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
                                        {{ $u->estado === 1 ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </div>
                                <div class="mb-2">
                                    <span class="small fw-semibold d-block mb-1">Roles Asignados:</span>
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach($u->roles as $rol)
                                            <span class="badge border" style="background-color: var(--color-hover-bg); color: var(--color-accent); font-size: 0.75rem;">
                                                {{ $rol->nombre }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if($u->hasRole('DOCENTE'))
                            <div class="col-12">
                                <div class="p-3 rounded-3 border" style="background-color: var(--color-hover-bg); border-color: var(--color-special) !important;">
                                    <div class="d-flex align-items-center mb-4">
                                        <i class="bi bi-mortarboard-fill me-2 fs-5" style="color: var(--color-accent);"></i>
                                        <h6 class="fw-bold mb-0 text-dark">INFORMACIÓN PLANTA DOCENTE:</h6>
                                    </div>
                                    @if($u->docente)
                                        <div class="row g-2  d-flex justify-content-center text-center">
                                            <div class="col-12 col-md-4">
                                                <small class="text-muted d-block">Código de Empleado:</small>
                                                <span class="fw-bold text-dark font-monospace">{{ $u->docente->codigo_empleado }}</span>
                                            </div>
                                            <div class="col-12 col-md-4">
                                                <small class="text-muted d-block">Correo Docente:</small>
                                                <span class="fw-bold text-dark font-monospace">{{ $u->docente->correo_institucional }}</span>
                                            </div>
                                            <div class="col-12 col-md-4">
                                                <small class="text-muted d-block ">Estado Docente:</small>
                                                <span class="badge bg-success-subtle text-success border border-primary-subtle">
                                                    {{ $u->docente->estado }}
                                                </span>
                                            </div>
                                        </div>
                                    @else
                                        <small class="text-muted">El usuario posee el rol DOCENTE pero aún no cuenta con un registro asociado en la tabla <code>docentes</code>.</small>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light px-4 py-3 rounded-bottom-4">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
@endforeach
@endsection