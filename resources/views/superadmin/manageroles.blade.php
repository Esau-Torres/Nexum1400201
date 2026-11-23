@extends('layouts.app')

@section('title', 'Gestión de Roles y Permisos')

@push('styles')
    @vite('resources/css/roles-permisos.css')
@endpush

@section('content')
<div class="container-fluid py-4 roles-view">

    {{-- HEADER --}}
    <div class="rp-header mb-4">
        <div class="rp-header-main">
            <div class="rp-header-icon">
                <i class="bi bi-shield-lock-fill"></i>
            </div>

            <div class="rp-header-content">
                <div class="rp-eyebrow">SEGURIDAD INSTITUCIONAL · NEXUM</div>
                <h1>Gestión de Roles y Permisos</h1>
                <p>Administra los roles institucionales y controla los privilegios de acceso de las cuentas UMA.</p>
            </div>
        </div>

        <div class="rp-header-actions">
            <a href="{{ route('superadmin.panel-administrativo') }}"
               class="btn rp-btn-secondary">
                <i class="bi bi-people-fill me-1"></i>
                Directorio de Usuarios
            </a>

            <a href="{{ route('superadmin.createuser') }}"
               class="btn rp-btn-primary">
                <i class="bi bi-person-plus-fill me-1"></i>
                Nuevo Usuario
            </a>
        </div>
    </div>

    {{-- AVISO DE SEGURIDAD --}}
    <div class="rp-security-banner mb-4">
        <div class="rp-security-icon">
            <i class="bi bi-shield-check"></i>
        </div>

        <div class="rp-security-content">
            <strong>Administración de acceso institucional</strong>
            <p>
                Los roles determinan los módulos y privilegios disponibles para cada cuenta.
                Verifica cuidadosamente los cambios antes de aplicarlos, especialmente al
                modificar el estado de un rol o los privilegios de una cuenta.
            </p>
        </div>
    </div>

    {{-- PESTAÑAS --}}
    <div class="rp-tabs-container">

        <ul class="nav nav-tabs rp-tabs"
            id="rolesManagementTabs"
            role="tablist">

            <li class="nav-item" role="presentation">
                <button class="nav-link active"
                        id="roles-tab"
                        data-bs-toggle="tab"
                        data-bs-target="#roles-pane"
                        type="button"
                        role="tab"
                        aria-controls="roles-pane"
                        aria-selected="true">

                    <i class="bi bi-shield-lock me-2"></i>
                    Catálogo de Roles

                    <span class="rp-tab-count">{{ count($roles) }}</span>
                </button>
            </li>

            <li class="nav-item" role="presentation">
                <button class="nav-link"
                        id="assignments-tab"
                        data-bs-toggle="tab"
                        data-bs-target="#assignments-pane"
                        type="button"
                        role="tab"
                        aria-controls="assignments-pane"
                        aria-selected="false">

                    <i class="bi bi-person-gear me-2"></i>
                    Asignación de Roles

                    <span class="rp-tab-count">{{ count($usuarios) }}</span>
                </button>
            </li>
        </ul>

        <div class="tab-content rp-tab-content" id="rolesManagementTabContent">

            {{-- TAB 1: CATÁLOGO DE ROLES --}}
            <div class="tab-pane fade show active"
                 id="roles-pane"
                 role="tabpanel"
                 aria-labelledby="roles-tab"
                 tabindex="0">

                <div class="rp-panel">

                    <div class="rp-panel-header">
                        <div class="rp-panel-title-wrap">
                            <div class="rp-panel-icon">
                                <i class="bi bi-tags-fill"></i>
                            </div>

                            <div>
                                <h2>Catálogo de Roles UMA</h2>
                                <p>Consulta y administra el estado operativo de los roles institucionales.</p>
                            </div>
                        </div>

                        <span class="rp-panel-count">
                            {{ count($roles) }} roles
                        </span>
                    </div>

                    <div class="rp-panel-body">
                        <div class="rp-table-wrapper">
                            <table id="rolesTable" class="rp-table">
                                <thead>
                                    <tr>
                                        <th>Rol</th>
                                        <th>Descripción</th>
                                        <th class="text-center">Estado</th>
                                        <th class="text-end">Acción</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach ($roles as $r)
                                        <tr>
                                            <td>
                                                <div class="rp-role-cell">
                                                    <div class="rp-role-avatar">
                                                        <i class="bi bi-shield-fill"></i>
                                                    </div>

                                                    <span class="rp-role-name">
                                                        {{ $r->nombre }}
                                                    </span>
                                                </div>
                                            </td>

                                            <td>
                                                <span class="rp-description">
                                                    {{ $r->descripcion ?? 'Sin descripción asignada' }}
                                                </span>
                                            </td>

                                            <td class="text-center">
                                                @if($r->estado)
                                                    <span class="rp-status rp-status-active">
                                                        <span class="rp-status-dot"></span>
                                                        Activo
                                                    </span>
                                                @else
                                                    <span class="rp-status rp-status-inactive">
                                                        <span class="rp-status-dot"></span>
                                                        Inactivo
                                                    </span>
                                                @endif
                                            </td>

                                            <td class="text-end">
                                                <button type="button"
                                                        class="rp-icon-button btn-edit-role-status"
                                                        data-id="{{ $r->rol_id }}"
                                                        data-name="{{ $r->nombre }}"
                                                        data-status="{{ $r->estado ? '1' : '0' }}"
                                                        title="Modificar Estado del Rol"
                                                        aria-label="Modificar estado de {{ $r->nombre }}">
                                                    <i class="bi bi-toggles"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="rp-panel-footnote">
                            <i class="bi bi-info-circle-fill"></i>
                            <span>
                                Al desactivar un rol, los usuarios que lo tengan asignado
                                perderán los privilegios asociados. Antes de confirmar,
                                revisa el impacto que muestra el sistema.
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TAB 2: ASIGNACIÓN DE ROLES --}}
            <div class="tab-pane fade"
                 id="assignments-pane"
                 role="tabpanel"
                 aria-labelledby="assignments-tab"
                 tabindex="0">

                <div class="rp-panel">

                    <div class="rp-panel-header">
                        <div class="rp-panel-title-wrap">
                            <div class="rp-panel-icon rp-panel-icon-users">
                                <i class="bi bi-person-gear"></i>
                            </div>

                            <div>
                                <h2>Asignación de Roles por Cuenta</h2>
                                <p>Consulta las cuentas institucionales y administra sus privilegios de acceso.</p>
                            </div>
                        </div>

                        <span class="rp-panel-count">
                            {{ count($usuarios) }} usuarios
                        </span>
                    </div>

                    <div class="rp-panel-body">
                        <div class="rp-table-wrapper">
                            <table id="usersAssignTable" class="rp-table rp-users-table">
                                <thead>
                                    <tr>
                                        <th>Usuario</th>
                                        <th>Roles asignados</th>
                                        <th>Sede UMA</th>
                                        <th class="text-center">Estado</th>
                                        <th class="text-end">Acción</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach ($usuarios as $u)
                                        <tr>
                                            <td>
                                                <div class="rp-user-cell">
                                                    <div class="rp-user-avatar">
                                                        {{ strtoupper(substr($u->name, 0, 2)) }}
                                                    </div>

                                                    <div class="rp-user-info">
                                                        <span class="rp-user-name" title="{{ $u->name }}">
                                                            {{ $u->name }}
                                                        </span>

                                                        <span class="rp-user-email">
                                                            {{ $u->email }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </td>

                                            <td>
                                                <div class="rp-user-roles">
                                                    @forelse($u->roles as $rol)
                                                        <span class="rp-role-chip">
                                                            {{ $rol->nombre }}
                                                        </span>
                                                    @empty
                                                        <span class="rp-no-role">
                                                            Sin rol asignado
                                                        </span>
                                                    @endforelse
                                                </div>
                                            </td>

                                            <td>
                                                <div class="rp-location">
                                                    <i class="bi bi-geo-alt-fill"></i>
                                                    <span>
                                                        {{ $u->regionalactivo->sede ?? 'No Asignada' }}
                                                    </span>
                                                </div>
                                            </td>

                                            <td class="text-center">
                                                @if($u->estado === 1)
                                                    <span class="rp-status rp-status-active">
                                                        <span class="rp-status-dot"></span>
                                                        Activo
                                                    </span>
                                                @else
                                                    <span class="rp-status rp-status-inactive">
                                                        <span class="rp-status-dot"></span>
                                                        Inactivo
                                                    </span>
                                                @endif
                                            </td>

                                            <td class="text-end">
                                                <button type="button"
                                                        class="rp-manage-button btn-manage-user-roles"
                                                        data-id="{{ $u->id }}"
                                                        data-name="{{ $u->name }}"
                                                        data-email="{{ $u->email }}"
                                                        data-roles="{{ json_encode($u->roles->where('estado', true)->pluck('rol_id')->toArray()) }}"
                                                        data-inactive-roles="{{ json_encode($u->roles->where('estado', false)->pluck('rol_id')->toArray()) }}"
                                                        data-is-current="{{ auth()->id() === $u->id ? 'true' : 'false' }}"
                                                        title="Modificar roles de usuario">
                                                    <i class="bi bi-shield-shaded"></i>
                                                    Asignar Roles
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="rp-panel-footnote">
                            <i class="bi bi-info-circle-fill"></i>
                            <span>
                                Utiliza la acción de cada cuenta para consultar y modificar
                                los roles asignados. Los roles inactivos no estarán disponibles
                                para nuevas asignaciones.
                            </span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- =====================================================
         MODAL 1: MODIFICAR ESTADO DE UN ROL
    ====================================================== --}}
    <div class="modal fade rp-modal"
         id="modalEditRoleStatus"
         tabindex="-1"
         aria-labelledby="modalEditRoleStatusLabel"
         aria-hidden="true">

        {{-- Se elimina modal-dialog-centered --}}
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">

                <div class="modal-header">
                    <div class="rp-modal-heading">
                        <div class="rp-modal-icon">
                            <i class="bi bi-toggles"></i>
                        </div>

                        <div>
                            <h6 class="modal-title" id="modalEditRoleStatusLabel">
                                Modificar Estado del Rol
                            </h6>
                            <p>Actualización del estado operativo institucional</p>
                        </div>
                    </div>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Cerrar"></button>
                </div>

                <form id="formUpdateRoleStatus"
                      method="POST"
                      action="{{ url('/superadmin/roles/status') }}">

                    @csrf
                    @method('PUT')

                    <input type="hidden"
                           id="roleStatusModalId"
                           name="role_id"
                           value="">

                    <div class="modal-body">

                        <div class="rp-selected-role">
                            <div class="rp-selected-role-icon">
                                <i class="bi bi-shield-check"></i>
                            </div>

                            <div>
                                <span class="rp-modal-label">Rol seleccionado:</span>
                                <strong id="roleStatusModalName">SUPER_ADMIN</strong>
                            </div>
                        </div>

                        <div class="mb-2">
                            <label class="rp-form-label mb-2" for="roleStatusModalSelect">
                                Estado Operativo <span class="text-danger">*</span>
                            </label>

                            <select class="form-select"
                                    name="estado"
                                    id="roleStatusModalSelect"
                                    required>
                                <option value="1">Activo (Habilitado para asignación)</option>
                                <option value="0">Inactivo (Deshabilitado temporalmente)</option>
                            </select>

                            <p class="rp-form-help">
                                Deshabilitar un rol impide que se asigne a nuevas cuentas institucionales.
                            </p>
                        </div>

                        {{-- Advertencia de impacto: IDs originales --}}
                        <div id="roleImpactWarning"
                             class="rp-impact-alert d-none mt-3">

                            <div class="rp-impact-icon">
                                <i class="bi bi-exclamation-triangle-fill"></i>
                            </div>

                            <div class="rp-impact-content">
                                <strong class="d-block mb-1">
                                    Este rol está asignado a
                                    <span id="impactAffectedCount">0</span> usuario(s).
                                </strong>

                                <p class="mb-2 small">
                                    Al desactivarlo, perderán los privilegios asociados de forma inmediata.
                                </p>

                                <div id="orphanUsersBlock" class="d-none">
                                    <span class="rp-orphan-badge">
                                        <i class="bi bi-person-x-fill me-1"></i>
                                        <span id="impactOrphanCount">0</span>
                                        quedarán sin roles activos
                                    </span>

                                    <button type="button"
                                            class="btn btn-sm rp-collapse-button ms-2"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#orphanUserList"
                                            aria-expanded="false"
                                            aria-controls="orphanUserList">
                                        Ver usuarios
                                    </button>

                                    <div class="collapse mt-2" id="orphanUserList">
                                        <ul id="orphanUserListItems" class="rp-orphan-list"></ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button"
                                class="btn rp-btn-secondary"
                                data-bs-dismiss="modal">
                            Cancelar
                        </button>

                        <button type="button"
                                id="btnSubmitRoleStatus"
                                class="btn rp-btn-primary">
                            <i class="bi bi-check2-circle me-1"></i>
                            Guardar Estado
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- =====================================================
         MODAL 2: ASIGNACIÓN DE ROLES POR USUARIO
    ====================================================== --}}
    <div class="modal fade rp-modal"
         id="modalManageUserRoles"
         tabindex="-1"
         aria-labelledby="modalManageUserRolesLabel"
         aria-hidden="true">

        {{-- Se elimina modal-dialog-centered --}}
        <div class="modal-dialog modal-dialog-scrollable modal-lg">
            <div class="modal-content">

                <div class="modal-header">
                    <div class="rp-modal-heading">
                        <div class="rp-modal-icon">
                            <i class="bi bi-person-lock"></i>
                        </div>

                        <div>
                            <h5 class="modal-title" id="modalManageUserRolesLabel">
                                Privilegios y Roles de Acceso (RBAC)
                            </h5>
                            <p id="modalUserRoleSubtitle">Expediente de Usuario</p>
                        </div>
                    </div>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Cerrar"></button>
                </div>

                <form id="formUpdateUserRoles"
                      method="POST"
                      action="{{ url('/superadmin/users') }}"
                      data-route-prefix="{{ url('/superadmin/users') }}">

                    @csrf
                    @method('PUT')

                    <input type="hidden"
                           id="userIdHiddenInput"
                           name="user_id"
                           value="">

                    <div class="modal-body">

                        <div class="rp-modal-info-banner">
                            <i class="bi bi-shield-exclamation"></i>
                            <span>
                                Seleccione los roles que habilitarán módulos específicos
                                para este usuario en NEXUM.
                            </span>
                        </div>

                        <div id="selfRoleWarning"
                             class="rp-self-warning alert p-3 d-none mb-3">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            <span>
                                <strong>Protección de Sesión:</strong>
                                Estás editando tu propia cuenta. El rol
                                <code>SUPER_ADMIN</code> permanecerá protegido.
                            </span>
                        </div>

                        <div class="rp-roles-section-header">
                            <div>
                                <h6>Catálogo de Roles Disponibles</h6>
                                <p>Selecciona los privilegios que tendrá esta cuenta.</p>
                            </div>

                            <div class="rp-roles-section-icon">
                                <i class="bi bi-list-check"></i>
                            </div>
                        </div>

                        <div class="row g-3">
                            @foreach ($roles as $rol)
                                @php
                                    $esInactivo = !$rol->estado;
                                @endphp

                                <div class="col-12 col-md-6 role-item {{ $esInactivo ? 'd-none' : '' }}"
                                     data-role-id="{{ $rol->rol_id }}"
                                     data-role-name="{{ $rol->nombre }}"
                                     data-role-active="{{ $rol->estado ? '1' : '0' }}">

                                    <div class="rp-role-option role-wrapper">

                                        <div class="rp-role-option-header">
                                            <div class="rp-role-option-info">
                                                <div class="rp-role-option-icon">
                                                    <i class="bi bi-shield-fill"></i>
                                                </div>

                                                <div class="rp-role-option-text">
                                                    <label class="rp-role-option-name"
                                                           for="rol_switch_{{ $rol->rol_id }}">
                                                        {{ $rol->nombre }}
                                                    </label>

                                                    <p>
                                                        {{ $rol->descripcion ?? 'Acceso operativo en el módulo institucional.' }}
                                                    </p>
                                                </div>
                                            </div>

                                            <div class="form-check form-switch rp-role-switch">
                                                <input class="form-check-input user-role-checkbox flex-shrink-0"
                                                       type="checkbox"
                                                       name="roles[]"
                                                       value="{{ $rol->rol_id }}"
                                                       id="rol_switch_{{ $rol->rol_id }}"
                                                       data-role-name="{{ $rol->nombre }}"
                                                       data-role-active="{{ $rol->estado ? '1' : '0' }}">
                                            </div>
                                        </div>

                                        @if($esInactivo)
                                            <span class="rp-inactive-role-badge role-inactive-badge">
                                                <i class="bi bi-pause-circle-fill me-1"></i>
                                                Rol inactivo
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button"
                                class="btn rp-btn-secondary"
                                data-bs-dismiss="modal">
                            Cerrar
                        </button>

                        <button type="button"
                                id="btnSubmitUserRoles"
                                class="btn rp-btn-primary">
                            <i class="bi bi-check2-circle me-1"></i>
                            Aplicar Roles y Privilegios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection