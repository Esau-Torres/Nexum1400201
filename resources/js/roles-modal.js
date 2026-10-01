
import * as bootstrap from 'bootstrap';

/**
 * Escapa HTML para prevenir XSS al inyectar strings del dataset.
 */
function escapeHtml(str) {
    if (!str) return '';
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

/**
 * Inicializa el Modal 1: Edición de Estado del Rol.
 */
function initRoleStatusModal() {
    const modalRoleStatusEl = document.getElementById('modalEditRoleStatus');
    const formRoleStatus    = document.getElementById('formUpdateRoleStatus');
    const inputRoleName     = document.getElementById('roleStatusModalName');
    const selectRoleStatus  = document.getElementById('roleStatusModalSelect');
    const hiddenRoleId      = document.getElementById('roleStatusModalId');
    const roleImpactWarning = document.getElementById('roleImpactWarning');

    if (!modalRoleStatusEl) return;

    const bsRoleStatusModal = new bootstrap.Modal(modalRoleStatusEl);

    // Delegación de eventos: abrir modal desde cualquier botón .btn-edit-role-status
    document.addEventListener('click', (event) => {
        const btn = event.target.closest('.btn-edit-role-status');
        if (!btn) return;

        const { id, name, status } = btn.dataset;
        formRoleStatus.action = formRoleStatus.dataset.route;
        inputRoleName.textContent = name;
        selectRoleStatus.value    = status;
        hiddenRoleId.value        = id;

        // Resetear advertencia al abrir
        roleImpactWarning.classList.add('d-none');

        bsRoleStatusModal.show();
    });

    // Escuchar cambios del select para previsualizar impacto
    selectRoleStatus.addEventListener('change', async (event) => {
        const selectedStatus = event.target.value;
        const roleId = hiddenRoleId.value;

        // Si eligen "Activo", ocultar advertencia
        if (selectedStatus !== '0') {
            roleImpactWarning.classList.add('d-none');
            return;
        }

        // Si eligen "Inactivo", consultar impacto al backend
        try {
            const res = await fetch(`/superadmin/roles/${roleId}/impact`, {
                headers: { 'Accept': 'application/json' },
            });

            if (!res.ok) throw new Error(`HTTP ${res.status}`);

            const data = await res.json();

            document.getElementById('impactAffectedCount').textContent = data.affected_users;

            const orphanBlock = document.getElementById('orphanUsersBlock');
            if (data.orphan_users > 0) {
                document.getElementById('impactOrphanCount').textContent = data.orphan_users;
                document.getElementById('orphanUserListItems').innerHTML = data.users
                    .filter(u => u.orphan)
                    .map(u => `<li><strong>${escapeHtml(u.name)}</strong> — ${escapeHtml(u.email)}</li>`)
                    .join('');
                orphanBlock.classList.remove('d-none');
            } else {
                orphanBlock.classList.add('d-none');
            }

            roleImpactWarning.classList.remove('d-none');
        } catch (err) {
            console.error('[NEXUM_RBAC] Error al obtener impacto del rol:', err);
            roleImpactWarning.classList.add('d-none');
        }
    });

    // Envío real del formulario al guardar
    document.getElementById('btnSubmitRoleStatus')?.addEventListener('click', () => {
        formRoleStatus.submit();
    });
}

/**
 * Inicializa el Modal 2: Asignación de Roles por Usuario.
 */
function initUserRolesModal() {
    const modalUserRolesEl = document.getElementById('modalManageUserRoles');
    const formUserRoles    = document.getElementById('formUpdateUserRoles');
    const subtitleEl       = document.getElementById('modalUserRoleSubtitle');
    const selfRoleWarning  = document.getElementById('selfRoleWarning');
    const hiddenUserId     = document.getElementById('userIdHiddenInput');
    const roleCheckboxes   = document.querySelectorAll('.user-role-checkbox');

    if (!modalUserRolesEl) return;

    const bsUserRolesModal = new bootstrap.Modal(modalUserRolesEl);

        function lockSuperAdminCheckbox(cb) {
        cb.checked = true;
        cb.setAttribute('data-locked', 'true');
        cb.setAttribute('aria-disabled', 'true');
        cb.style.pointerEvents = 'none';
        cb.style.opacity = '0.6';
        cb.style.cursor = 'not-allowed';

        // También bloqueamos visualmente el contenedor padre
        const wrapper = cb.closest('.form-check');
        if (wrapper) {
            wrapper.classList.add('border-warning-subtle');
            wrapper.style.backgroundColor = '#fffbeb';
        }
    }

    /**
     * Restaura el checkbox de SUPER_ADMIN a su estado normal.
     */
    function unlockSuperAdminCheckbox(cb) {
        cb.removeAttribute('data-locked');
        cb.removeAttribute('aria-disabled');
        cb.style.pointerEvents = '';
        cb.style.opacity = '';
        cb.style.cursor = '';

        const wrapper = cb.closest('.form-check');
        if (wrapper) {
            wrapper.classList.remove('border-warning-subtle');
            wrapper.style.backgroundColor = '';
        }
    }
    
    document.addEventListener('click', (event) => {
        const btn = event.target.closest('.btn-manage-user-roles');
 
        if (!btn) return;

        const { id, name, email, roles, inactiveRoles, isCurrent } = btn.dataset;
        formUserRoles.action = formUserRoles.dataset.route.replace('__USER_ID__', id);

        const userRoles = JSON.parse(roles || '[]');
        const userInactiveRoles = JSON.parse(inactiveRoles || '[]');

        // Subtítulo del modal
        subtitleEl.innerHTML = `Usuario: <strong>${escapeHtml(name)}</strong> (${escapeHtml(email)}) | ID: UMA-${String(id).padStart(5, '0')}`;

        // Guardar ID de usuario en el hidden input del form
        if (hiddenUserId) hiddenUserId.value = id;

        //Determinar visibilidad de cada rol
        document.querySelectorAll('.role-item').forEach(item => {
            const roleId     = parseInt(item.dataset.roleId, 10);
            const isActive   = item.dataset.roleActive === '1';

            // R1: rol activo → siempre visible
            // R3: rol reactivado (isActive=1) → vuelve a ser visible para todos
            if (isActive) {
                item.classList.remove('d-none');
                return;
            }

            // R2: rol inactivo → visible SOLO si el usuario lo tiene asignado
            if (userInactiveRoles.includes(roleId)) {
                item.classList.remove('d-none');
            } else {
                item.classList.add('d-none');
            }
        });

        // Resetear todos los checkboxes
        roleCheckboxes.forEach(cb => {
            cb.checked  = false;
            unlockSuperAdminCheckbox(cb);
        });

        // Marcar los roles actuales del usuario
        userRoles.forEach(roleId => {
            const cb = document.getElementById(`rol_switch_${roleId}`);
            if (cb) cb.checked = true;
        });

        // marca los roles inactivos actuales 
        userInactiveRoles.forEach(roleId => {
            const cb = document.getElementById(`rol_switch_${roleId}`);
            if (cb) {
                cb.checked = true;
                // Marcar visualmente que está inactivo pero asignado
                const wrapper = cb.closest('.role-wrapper');
                if (wrapper) wrapper.classList.add('border-warning');
            }
        });


        // Si es el usuario en sesión, proteger SUPER_ADMIN
        if (isCurrent === 'true') {
            selfRoleWarning.classList.remove('d-none');

            roleCheckboxes.forEach(cb => {
                if (cb.dataset.roleName === 'SUPER_ADMIN') {
                    lockSuperAdminCheckbox(cb);
                }
            });
        } else {
            selfRoleWarning.classList.add('d-none');
        }

        bsUserRolesModal.show();
    });

    document.addEventListener('change', (event) => {
        const cb = event.target.closest('.user-role-checkbox[data-locked="true"]');
        if (cb && !cb.checked) {
            cb.checked = true;
        }
    });

    // Envío real del formulario
    document.getElementById('btnSubmitUserRoles')?.addEventListener('click', () => {
        document.querySelectorAll('.user-role-checkbox[data-locked="true"]').forEach(cb => {
            cb.checked = true;
        });
        formUserRoles.submit();
    });
}

/**
 * Punto de entrada: inicializa ambos modales cuando el DOM está listo.
 */
document.addEventListener('DOMContentLoaded', () => {
    initRoleStatusModal();
    initUserRolesModal();
});