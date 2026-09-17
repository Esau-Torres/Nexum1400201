import { Toast } from 'bootstrap';

/**
 * NEXUM Toast Manager
 * API global para emitir notificaciones con Bootstrap 5
 */
const NexumToast = (() => {
    const containerId = 'nexum-toast-container';

    const typeConfig = {
        success: {
            title: 'Éxito',
            icon: 'bi-check-circle-fill',
            class: 'nexum-toast-success'
        },
        error: {
            title: 'Error del Sistema',
            icon: 'bi-x-circle-fill',
            class: 'nexum-toast-error'
        },
        warning: {
            title: 'Advertencia',
            icon: 'bi-exclamation-triangle-fill',
            class: 'nexum-toast-warning'
        },
        info: {
            title: 'Información',
            icon: 'bi-info-circle-fill',
            class: 'nexum-toast-info'
        }
    };

    const sanitize = (str) => {
        const temp = document.createElement('div');
        temp.textContent = str ?? '';
        return temp.innerHTML;
    };

    const show = (type = 'info', message = '', title = null, delay = 5000) => {
        const container = document.getElementById(containerId);
        if (!container) {
            console.error(`[NEXUM] Contenedor #${containerId} no encontrado en el DOM.`);
            return;
        }

        const config = typeConfig[type] || typeConfig.info;
        const toastTitle = title ? sanitize(title) : config.title;
        const toastMessage = sanitize(message);

        const toastEl = document.createElement('div');
        toastEl.className = `toast align-items-center border-0 shadow-sm ${config.class}`;
        toastEl.setAttribute('role', 'alert');
        toastEl.setAttribute('aria-live', 'assertive');
        toastEl.setAttribute('aria-atomic', 'true');

        toastEl.innerHTML = `
            <div class="d-flex">
                <div class="toast-body d-flex align-items-start gap-2">
                    <i class="bi ${config.icon} fs-5 flex-shrink-0"></i>
                    <div>
                        <strong class="d-block mb-1 text-dark">${toastTitle}</strong>
                        <span class="text-secondary small">${toastMessage}</span>
                    </div>
                </div>
                <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
            </div>
        `;

        container.appendChild(toastEl);

        const bsToast = new Toast(toastEl, {
            autohide: true,
            delay: delay
        });

        toastEl.addEventListener('hidden.bs.toast', () => {
            toastEl.remove();
        });

        bsToast.show();
    };

    return {
        show,
        success: (msg, title, delay) => show('success', msg, title, delay),
        error: (msg, title, delay) => show('error', msg, title, delay),
        warning: (msg, title, delay) => show('warning', msg, title, delay),
        info: (msg, title, delay) => show('info', msg, title, delay)
    };
})();

// Registro en el objeto global del navegador
window.NexumToast = NexumToast;

// Inicialización de Toasts renderizados desde el backend por Blade
document.addEventListener('DOMContentLoaded', () => {
    const existingToasts = document.querySelectorAll('#nexum-toast-container .toast');
    existingToasts.forEach(toastNode => {
        const bsToast = new Toast(toastNode);
        toastNode.addEventListener('hidden.bs.toast', () => toastNode.remove());
        bsToast.show();
    });
});

export default NexumToast;