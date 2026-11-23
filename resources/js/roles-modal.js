
import * as bootstrap from 'bootstrap';

/**
 * Escapa HTML para evitar inyección de código al mostrar datos.
 */
function escapeHtml(value) {
    if (value === null || value === undefined) {
        return '';
    }

    const div = document.createElement('div');
    div.textContent = String(value);
    return div.innerHTML;
}

/**
 * Convierte un dataset JSON en un arreglo seguro.
 */
function parseJsonArray(value, label) {
    try {
        const parsed = JSON.parse(value || '[]');

        if (!Array.isArray(parsed)) {
            throw new Error('El valor no es un arreglo.');
        }

        return parsed;
    } catch (error) {
        console.error(`[NEXUM_RBAC] Error al interpretar ${label}:`, error);
        return [];
    }
}

/**
 * MODAL 1: Modificar estado de un rol.
 */
function initRoleStatusModal() {
    const modalElement = document.getElementById('modalEditRoleStatus');

    if (!modalElement) return;

    const form = document.getElementById('formUpdateRoleStatus');
    const roleName = document.getElementById('roleStatusModalName');
    const roleStatus = document.getElementById('roleStatusModalSelect');
    const roleIdInput = document.getElementById('roleStatusModalId');
    const impactWarning = document.getElementById('roleImpactWarning');
    const submitButton = document.getElementById('btnSubmitRoleStatus');

    if (!form || !roleName || !roleStatus || !roleIdInput) {
        console.error('[NEXUM_RBAC] Faltan elementos del modal de estado.');
        return;
    }

    const modal = bootstrap.Modal.getOrCreateInstance(modalElement);

    // Abrir modal.
    document.addEventListener('click', (event) => {
        const button = event.target.closest('.btn-edit-role-status');

        if (!button) return;

        const { id, name, status } = button.dataset;

        if (!id || !/^\d+$/.test(id)) {
            console.error('[NEXUM_RBAC] ID de rol inválido:', id);
            return;
        }

        roleName.textContent = name || '';
        roleStatus.value = status ?? '1';
        roleIdInput.value = id;

        form.action = `${window.location.origin}/superadmin/roles/status`;

        impactWarning?.classList.add('d-none');

        modal.show();
    });

    // Consultar impacto cuando se selecciona estado inactivo.
    roleStatus.addEventListener('change', async (event) => {
        const selectedStatus = event.target.value;
        const roleId = roleIdInput.value;

        if (selectedStatus !== '0') {
            impactWarning?.classList.add('d-none');
            return;
        }

        if (!roleId || !/^\d+$/.test(roleId)) {
            console.error('[NEXUM_RBAC] ID de rol inválido:', roleId);
            return;
        }

        try {
            const response = await fetch(
                `/superadmin/roles/${encodeURIComponent(roleId)}/impact`,
                {
                    method: 'GET',
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                }
            );

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const data = await response.json();

            document.getElementById('impactAffectedCount').textContent =
                data.affected_users ?? 0;

            const orphanBlock = document.getElementById('orphanUsersBlock');
            const orphanCount = Number(data.orphan_users ?? 0);
            const orphanList = document.getElementById('orphanUserListItems');

            if (orphanCount > 0) {
                document.getElementById('impactOrphanCount').textContent =
                    orphanCount;

                if (orphanList) {
                    orphanList.replaceChildren();

                    (Array.isArray(data.users) ? data.users : [])
                        .filter(user => user.orphan)
                        .forEach(user => {
                            const li = document.createElement('li');
                            const strong = document.createElement('strong');

                            strong.textContent = user.name ?? '';
                            li.appendChild(strong);
                            li.appendChild(
                                document.createTextNode(
                                    ` — ${user.email ?? ''}`
                                )
                            );

                            orphanList.appendChild(li);
                        });
                }

                orphanBlock?.classList.remove('d-none');
            } else {
                orphanBlock?.classList.add('d-none');
                orphanList?.replaceChildren();
            }

            impactWarning?.classList.remove('d-none');
        } catch (error) {
            console.error(
                '[NEXUM_RBAC] Error al consultar el impacto del rol:',
                error
            );

            impactWarning?.classList.add('d-none');
        }
    });

    // Enviar actualización del estado.
    submitButton?.addEventListener('click', () => {
        const roleId = roleIdInput.value;

        if (!roleId || !/^\d+$/.test(roleId)) {
            console.error('[NEXUM_RBAC] Falta un ID de rol válido.');
            return;
        }

        form.action = `${window.location.origin}/superadmin/roles/status`;

        console.log('[NEXUM_RBAC] Actualizando estado:', {
            action: form.action,
            roleId,
            method: 'PUT'
        });

        form.requestSubmit();
    });
}

/**
 * MODAL 2: Asignación de roles por usuario.
 */
function initUserRolesModal() {
    const modalElement = document.getElementById('modalManageUserRoles');

    if (!modalElement) return;

    const form = document.getElementById('formUpdateUserRoles');
    const subtitle = document.getElementById('modalUserRoleSubtitle');
    const selfRoleWarning = document.getElementById('selfRoleWarning');
    const userIdInput = document.getElementById('userIdHiddenInput');
    const submitButton = document.getElementById('btnSubmitUserRoles');

    if (!form || !userIdInput) {
        console.error('[NEXUM_RBAC] Faltan elementos del modal de usuarios.');
        return;
    }

    const modal = bootstrap.Modal.getOrCreateInstance(modalElement);

    /**
     * Obtiene el prefijo desde Blade.
     * No utiliza marcadores como __USER_ID__.
     */
    function getRoutePrefix() {
        const prefix = form.dataset.routePrefix;

        if (!prefix) {
            throw new Error('No existe data-route-prefix en el formulario.');
        }

        return prefix.replace(/\/+$/, '');
    }

    /**
     * Construye la URL utilizando un ID numérico real.
     */
    function setUserRolesAction(userId) {
        if (!userId || !/^\d+$/.test(String(userId))) {
            throw new Error(`ID de usuario inválido: ${userId}`);
        }

        const prefix = getRoutePrefix();

        form.action =
            `${prefix}/${encodeURIComponent(String(userId))}/roles`;
    }

    /**
     * Bloquea visualmente el rol SUPER_ADMIN
     * cuando se está editando la propia cuenta.
     */
    function lockSuperAdminCheckbox(checkbox) {
        checkbox.checked = true;
        checkbox.dataset.locked = 'true';
        checkbox.setAttribute('aria-disabled', 'true');
        checkbox.style.pointerEvents = 'none';
        checkbox.style.opacity = '0.6';
        checkbox.style.cursor = 'not-allowed';

        const wrapper = checkbox.closest('.form-check');

        if (wrapper) {
            wrapper.classList.add('border-warning-subtle');
            wrapper.style.backgroundColor = '#fffbeb';
        }
    }

    /**
     * Restaura el checkbox a su estado normal.
     */
    function unlockSuperAdminCheckbox(checkbox) {
        delete checkbox.dataset.locked;
        checkbox.removeAttribute('aria-disabled');
        checkbox.style.pointerEvents = '';
        checkbox.style.opacity = '';
        checkbox.style.cursor = '';

        const wrapper = checkbox.closest('.form-check');

        if (wrapper) {
            wrapper.classList.remove('border-warning-subtle');
            wrapper.style.backgroundColor = '';
        }
    }

    /**
     * Abre y prepara el modal para el usuario seleccionado.
     */
    document.addEventListener('click', (event) => {
        const button = event.target.closest('.btn-manage-user-roles');

        if (!button) return;

        const {
            id,
            name,
            email,
            roles,
            inactiveRoles,
            isCurrent
        } = button.dataset;

        if (!id || !/^\d+$/.test(id)) {
            console.error('[NEXUM_RBAC] ID de usuario inválido:', id);
            return;
        }

        try {
            setUserRolesAction(id);
        } catch (error) {
            console.error('[NEXUM_RBAC] No se pudo construir la ruta:', error);
            return;
        }

        const userRoles = parseJsonArray(roles, 'roles del usuario');
        const userInactiveRoles = parseJsonArray(
            inactiveRoles,
            'roles inactivos del usuario'
        );

        // Guardar el ID real en el formulario.
        userIdInput.value = id;

        if (subtitle) {
            subtitle.textContent = '';

            const label = document.createElement('span');
            label.textContent =
                `Usuario: ${name || ''} (${email || ''}) | ID: UMA-${String(id).padStart(5, '0')}`;

            subtitle.appendChild(label);
        }

        // Mostrar roles activos y roles inactivos ya asignados.
        document.querySelectorAll('.role-item').forEach(item => {
            const roleId = Number(item.dataset.roleId);
            const isActive = item.dataset.roleActive === '1';
            const isAssignedInactive = userInactiveRoles.some(
                value => Number(value) === roleId
            );

            item.classList.toggle(
                'd-none',
                !isActive && !isAssignedInactive
            );

            const checkbox = item.querySelector('.user-role-checkbox');

            if (checkbox) {
                const wrapper = checkbox.closest('.role-wrapper');
                wrapper?.classList.remove('border-warning');
            }
        });

        // Reiniciar selección y desbloquear controles.
        form.querySelectorAll('.user-role-checkbox').forEach(checkbox => {
            checkbox.checked = false;
            unlockSuperAdminCheckbox(checkbox);
        });

        // Marcar roles que el usuario ya tiene asignados.
        userRoles.forEach(value => {
            const checkbox = document.getElementById(
                `rol_switch_${Number(value)}`
            );

            if (checkbox) {
                checkbox.checked = true;
            }
        });

        // Mantener marcados los roles inactivos asignados.
        userInactiveRoles.forEach(value => {
            const checkbox = document.getElementById(
                `rol_switch_${Number(value)}`
            );

            if (checkbox) {
                checkbox.checked = true;

                checkbox.closest('.role-wrapper')
                    ?.classList.add('border-warning');
            }
        });

        // Proteger SUPER_ADMIN si es la cuenta actual.
        if (isCurrent === 'true') {
            selfRoleWarning?.classList.remove('d-none');

            form.querySelectorAll('.user-role-checkbox').forEach(checkbox => {
                if (checkbox.dataset.roleName === 'SUPER_ADMIN') {
                    lockSuperAdminCheckbox(checkbox);
                }
            });
        } else {
            selfRoleWarning?.classList.add('d-none');
        }

        console.log('[NEXUM_RBAC] Modal de usuario preparado:', {
            userId: id,
            action: form.action
        });

        modal.show();
    });

    // Evitar que se desmarque el rol protegido.
    form.addEventListener('change', (event) => {
        const checkbox = event.target.closest(
            '.user-role-checkbox[data-locked="true"]'
        );

        if (checkbox) {
            checkbox.checked = true;
        }
    });

    /**
     * Envío del formulario de asignación.
     */
    submitButton?.addEventListener('click', () => {
        const userId = userIdInput.value.trim();

        if (!userId || !/^\d+$/.test(userId)) {
            console.error('[NEXUM_RBAC] ID de usuario inválido:', userId);
            return;
        }

        try {
            // Reconstruir la ruta justo antes del envío.
            setUserRolesAction(userId);
        } catch (error) {
            console.error('[NEXUM_RBAC] Error de ruta:', error);
            return;
        }

        // Los checkboxes deshabilitados no se envían.
        // Por eso se mantienen marcados, sin usar disabled.
        form.querySelectorAll(
            '.user-role-checkbox[data-locked="true"]'
        ).forEach(checkbox => {
            checkbox.checked = true;
        });

        console.log('[NEXUM_RBAC] Enviando asignación de roles:', {
            action: form.action,
            userId,
            method: 'PUT',
            roles: Array.from(
                form.querySelectorAll('.user-role-checkbox:checked')
            ).map(checkbox => checkbox.value)
        });

        form.requestSubmit();
    });
}

/**
 * Inicialización de los modales.
 */
document.addEventListener('DOMContentLoaded', () => {
    initRoleStatusModal();
    initUserRolesModal();
});
