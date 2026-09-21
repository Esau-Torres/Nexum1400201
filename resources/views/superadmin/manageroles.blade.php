@extends('layouts.app')

@section('title', 'Gestión de Roles y Permisos')

@section('content')
<div class="container-fluid py-4" style="background-color: var(--bg-main); min-height: 100vh;">
    
    {{-- Header del Módulo NEXUM --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3 card p-4 rounded-4 border-0 shadow-sm" style="background-color: #ffffff;">
        <div>
            <h4 class="fw-bold mb-1" style="color: var(--text-primary);">
                <i class="bi bi-shield-lock-fill me-2" style="color: var(--color-accent);"></i>GESTIÓN DE ROLES INSTITUCIONALES (RBAC)
            </h4>
            <span class="text-muted small">Universidad Modular Abierta — Panel de Seguridad y Asignación de Privilegios</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('superadmin.panel-administrativo') }}" class="btn btn-outline-secondary btn-sm rounded-3 shadow-sm">
                <i class="bi bi-people-fill me-1"></i> Directorio de Usuarios
            </a>
            <a href="{{ route('superadmin.createuser') }}" class="btn btn-sm text-white fw-medium shadow-sm" style="background-color: var(--color-accent);">
                <i class="bi bi-person-plus-fill me-1"></i> Nuevo Usuario
            </a>
        </div>
    </div>

    {{-- Grid Dividido: Catálogo de Roles y Asignaciones --}}
    <div class="row g-4">
        
        {{-- PANEL 1: CATÁLOGO DE ROLES INSTITUCIONALES --}}
        <div class="col-12 col-xl-5">
            <div class="card border-0 rounded-4 shadow-sm h-100 overflow-hidden" style="background-color: #ffffff;">
                <div class="card-header bg-white border-bottom p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="fw-bold mb-0 text-dark">
                                <i class="bi bi-tags-fill me-2" style="color: var(--color-accent);"></i>Catálogo de Roles UMA
                            </h6>
                            <small class="text-muted">Estado operativo de cada rol en el ecosistema</small>
                        </div>
                        <span class="badge rounded-pill px-3 py-2 text-dark border" style="background-color: var(--color-special); font-size: 0.75rem;">
                            {{ count($roles) }} Roles
                        </span>
                    </div>
                </div>

                <div class="card-body p-4">
                    <div>
                        <table id="rolesTable" class="table table-hover align-middle mb-0 w-100" style="font-size: 0.88rem;">
                            <thead style="background-color: var(--bg-main); color: var(--text-primary); border-bottom: 2px solid #e2e8f0;">
                                <tr>
                                    <th class="ps-3 py-3 fw-semibold">Rol</th>
                                    <th class="py-3 fw-semibold">Descripción</th>
                                    <th class="py-3 fw-semibold text-center">Estado</th>
                                    <th class="pe-3 py-3 fw-semibold text-end">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($roles as $r)
                                    <tr>
                                        <td class="ps-3 py-3">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-sm" 
                                                     style="width: 32px; height: 32px; min-width: 32px; background-color: var(--color-accent); font-size: 0.75rem;">
                                                    <i class="bi bi-shield-fill"></i>
                                                </div>
                                                <div class="fw-bold text-dark font-monospace" style="font-size: 0.8rem;">
                                                    {{ $r->nombre }}
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3 text-muted small" style="max-width: 180px;">
                                            {{ $r->descripcion ?? 'Sin descripción asignada' }}
                                        </td>
                                        <td class="py-3 text-center">
                                            @if($r->estado)
                                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2" style="font-size: 0.7rem;">
                                                    Activo
                                                </span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2" style="font-size: 0.7rem;">
                                                    Inactivo
                                                </span>
                                            @endif
                                        </td>
                                        <td class="pe-3 py-3 text-end">
                                            <button type="button" 
                                                    class="btn btn-sm btn-light border text-primary btn-edit-role-status"
                                                    data-id="{{ $r->rol_id }}"
                                                    data-name="{{ $r->nombre }}"
                                                    data-status="{{ $r->estado ? '1' : '0' }}"
                                                    title="Modificar Estado del Rol">
                                                <i class="bi bi-toggles"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- PANEL 2: ASIGNACIÓN DE ROLES A USUARIOS --}}
        <div class="col-12 col-xl-7">
            <div class="card border-0 rounded-4 shadow-sm h-100 overflow-hidden" style="background-color: #ffffff;">
                <div class="card-header bg-white border-bottom p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="fw-bold mb-0 text-dark">
                                <i class="bi bi-person-gear me-2" style="color: var(--color-accent);"></i>Asignación de Roles por Cuenta
                            </h6>
                            <small class="text-muted">Control de acceso basado en roles para directivos, docentes y personal</small>
                        </div>
                    </div>
                </div>

                <div class="card-body p-2">
                    <div>
                        <table id="usersAssignTable" class="table table-hover align-middle mb-0 w-100" style="font-size: 0.88rem;">
                            <thead style="background-color: var(--bg-main); color: var(--text-primary); border-bottom: 2px solid #e2e8f0;">
                                <tr>
                                    <th class="ps-3 py-3 fw-semibold">Usuario</th>
                                    <th class="py-3 fw-semibold">Roles Asignados</th>
                                    <th class="py-3 fw-semibold">Sede UMA</th>
                                    <th class="py-3 fw-semibold text-center">Estado</th>
                                    <th class="pe-3 py-3 fw-semibold text-end">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($usuarios as $u)
                                    <tr>
                                        <td class="ps-3 py-3">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-sm" 
                                                     style="width: 36px; height: 36px; min-width: 36px; background-color: var(--color-accent); font-size: 0.8rem;">
                                                    {{ strtoupper(substr($u->name, 0, 2)) }}
                                                </div>
                                                <div class="overflow-hidden">
                                                    <div class="fw-bold text-dark mb-0 text-truncate" style="max-width: 160px;" title="{{ $u->name }}">
                                                        {{ $u->name }}
                                                    </div>
                                                    <small class="text-muted font-monospace d-block text-truncate" style="font-size: 0.72rem;">
                                                        {{ $u->email }}
                                                    </small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3">
                                            <div class="d-flex flex-wrap gap-1" style="max-width: 220px;">
                                                @forelse($u->roles as $rol)
                                                    <span class="badge border fw-medium" style="background-color: var(--color-hover-bg); color: var(--color-accent); font-size: 0.7rem;">
                                                        {{ $rol->nombre }}
                                                    </span>
                                                @empty
                                                    <span class="badge bg-light text-muted border" style="font-size: 0.7rem;">Sin rol asignado</span>
                                                @endforelse
                                            </div>
                                        </td>
                                        <td class="py-3">
                                            <span class="text-dark small" style="font-size: 0.78rem;">
                                                <i class="bi bi-geo-alt-fill me-1" style="color: var(--color-accent);"></i>
                                                {{ $u->regionalactivo->sede ?? 'No Asignada' }}
                                            </span>
                                        </td>
                                        <td class="py-3 text-center">
                                            @if($u->estado === 1)
                                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2" style="font-size: 0.7rem;">Activo</span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2" style="font-size: 0.7rem;">Inactivo</span>
                                            @endif
                                        </td>
                                        <td class="pe-3 py-3 text-end">
                                            <button type="button"
                                                class="btn btn-sm text-white fw-medium shadow-sm btn-manage-user-roles"
                                                data-id="{{ $u->id }}"
                                                data-name="{{ $u->name }}"
                                                data-email="{{ $u->email }}"
                                                data-roles="{{ json_encode($u->roles->where('estado', true)->pluck('rol_id')->toArray()) }}"
                                                data-inactive-roles="{{ json_encode($u->roles->where('estado', false)->pluck('rol_id')->toArray()) }}"
                                                data-is-current="{{ auth()->id() === $u->id ? 'true' : 'false' }}"
                                                title="Modificar roles de usuario"
                                                style="background-color: var(--color-accent);">
                                                <i class="bi bi-shield-shaded me-1"></i> Asignar Roles
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- MODAL 1: MODIFICAR ESTADO DE UN ROL (GLOBAL) --}}
<div class="modal fade" id="modalEditRoleStatus" tabindex="-1" aria-labelledby="modalEditRoleStatusLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom px-4 py-3" style="background-color: var(--bg-main);">
                <h6 class="modal-title fw-bold mb-0" id="modalEditRoleStatusLabel" style="color: var(--text-primary);">
                    <i class="bi bi-toggles me-2" style="color: var(--color-accent);"></i>Modificar Estado del Rol
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="formUpdateRoleStatus" method="POST" action="" data-route="{{ route('superadmin.roles.update-status') }}">
                @csrf
                @method('PUT')

                <input type="hidden" id="roleStatusModalId" name="role_id" value="">
                
                <div class="modal-body p-4">
                    <div class="p-3 rounded-3 mb-3 border d-flex align-items-center gap-3" 
                        style="background-color: var(--color-hover-bg); border-color: rgba(220, 53, 69, 0.2) !important;">
                        <i class="bi bi-shield-check fs-3" style="color: var(--color-accent);"></i>
                        <div>
                            <span class="text-muted small d-block">Rol seleccionado:</span>
                            <strong class="text-dark font-monospace" id="roleStatusModalName">SUPER_ADMIN</strong>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label small fw-semibold text-dark mb-1">
                            Estado Operativo <span class="text-danger">*</span>
                        </label>
                        <select class="form-select form-select-sm" name="estado" id="roleStatusModalSelect" required>
                            <option value="1">Activo (Habilitado para asignación)</option>
                            <option value="0">Inactivo (Deshabilitado temporalmente)</option>
                        </select>
                        <small class="text-muted d-block mt-2" style="font-size: 0.75rem;">
                            Deshabilitar un rol impide que se asigne a nuevas cuentas institucionales.
                        </small>
                    </div>

                    {{--  Advertencia  --}}
                    <div id="roleImpactWarning" class="alert alert-warning p-3 rounded-3 mt-3 d-none">
                        <div class="d-flex align-items-start gap-2">
                            <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                            <div class="flex-grow-1">
                                <strong class="d-block mb-1">
                                    Este rol está asignado a <span id="impactAffectedCount">0</span> usuario(s).
                                </strong>
                                <p class="mb-2 small">
                                    Al desactivarlo, perderán los privilegios asociados de forma inmediata.
                                </p>
                                <div id="orphanUsersBlock" class="d-none">
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                        <i class="bi bi-person-x-fill me-1"></i>
                                        <span id="impactOrphanCount">0</span> quedarán sin roles activos
                                    </span>
                                    <button type="button" class="btn btn-sm btn-link p-0 ms-2" 
                                            data-bs-toggle="collapse" data-bs-target="#orphanUserList">
                                        Ver usuarios
                                    </button>
                                    <div class="collapse mt-2" id="orphanUserList">
                                        <ul id="orphanUserListItems" class="small mb-0 ps-3"></ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 bg-light px-4 py-3 rounded-bottom-4">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="button" id="btnSubmitRoleStatus"
                            class="btn btn-sm text-white fw-medium shadow-sm" 
                            style="background-color: var(--color-accent);">
                        <i class="bi bi-check2-circle me-1"></i> Guardar Estado
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL 2: ASIGNACIÓN DE ROLES POR USUARIO (GLOBAL) --}}
<div class="modal fade" id="modalManageUserRoles" tabindex="-1" aria-labelledby="modalManageUserRolesLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom px-4 py-3" style="background-color: var(--bg-main);">
                <div>
                    <h5 class="modal-title fw-bold mb-0" id="modalManageUserRolesLabel" style="color: var(--text-primary);">
                        Privilegios y Roles de Acceso (RBAC)
                    </h5>
                    <small class="text-muted" id="modalUserRoleSubtitle">Expediente de Usuario</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="formUpdateUserRoles" method="POST" action="" data-route="{{ route('superadmin.users.assign-roles', ['userId' => '__USER_ID__']) }}">
                @csrf
                @method('PUT')

                <input type="hidden" id="userIdHiddenInput" name="user_id" value="">

                <div class="modal-body p-4">
                    <div class="alert p-2 d-flex align-items-center mb-3 rounded-3 border" style="background-color: var(--color-special); font-size: 0.8rem;">
                        <i class="bi bi-shield-exclamation me-2 fs-5" style="color: var(--color-accent);"></i>
                        <span>Seleccione los roles que habilitarán módulos específicos para este usuario en NEXUM.</span>
                    </div>

                    <div id="selfRoleWarning" class="alert alert-warning p-2 d-flex align-items-center mb-3 rounded-3 d-none" style="font-size: 0.8rem;">
                        <i class="bi bi-exclamation-triangle-fill me-2 fs-6"></i>
                        <span><strong>Protección de Sesión:</strong> Estás editando tu propia cuenta. El rol <code>SUPER_ADMIN</code> permanecerá protegido.</span>
                    </div>

                    <h6 class="small fw-bold border-bottom pb-2 mb-3" style="color: var(--color-accent);">
                        Catálogo de Roles Disponibles:
                    </h6>

                    <div class="row g-3">
                        @foreach ($roles as $rol)
                            @php
                                $esInactivo = !$rol->estado;
                            @endphp

                            <div class="col-12 col-md-6 role-item {{ $esInactivo ? 'd-none' : '' }}"
                                data-role-id="{{ $rol->rol_id }}"
                                data-role-name="{{ $rol->nombre }}"
                                data-role-active="{{ $rol->estado ? '1' : '0' }}">

                                <div class="p-3 rounded-3 border h-100 role-wrapper"
                                    style="background-color: #ffffff; border-color: #e2e8f0;">

                                    <div class="form-check form-switch d-flex justify-content-between align-items-center ps-0 mb-0">
                                        <div class="me-3">
                                            <label class="form-check-label fw-bold text-dark d-block mb-0 font-monospace"
                                                for="rol_switch_{{ $rol->rol_id }}">
                                                {{ $rol->nombre }}
                                            </label>
                                            <small class="text-muted d-block">
                                                {{ $rol->descripcion ?? 'Acceso operativo en el módulo institucional.' }}
                                            </small>

                                            @if($esInactivo)
                                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle mt-1 role-inactive-badge">
                                                    <i class="bi bi-pause-circle-fill me-1"></i> Rol inactivo
                                                </span>
                                            @endif
                                        </div>

                                        <input class="form-check-input user-role-checkbox flex-shrink-0"
                                            type="checkbox"
                                            name="roles[]"
                                            value="{{ $rol->rol_id }}"
                                            id="rol_switch_{{ $rol->rol_id }}"
                                            data-role-name="{{ $rol->nombre }}"
                                            data-role-active="{{ $rol->estado ? '1' : '0' }}"
                                            style="width: 2.4rem; height: 1.25rem;">
                                    </div>
                                </div>
                            </div>

                        @endforeach
                    </div>
                </div>

                <div class="modal-footer border-0 bg-light px-4 py-3 rounded-bottom-4">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">
                        Cerrar
                    </button>
                    <button type="button" 
                            id="btnSubmitUserRoles"
                            class="btn btn-sm text-white fw-medium shadow-sm" 
                            style="background-color: var(--color-accent);">
                        <i class="bi bi-check2-circle me-1"></i> Aplicar Roles y Privilegios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection