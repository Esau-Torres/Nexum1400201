//

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allow your team to quickly build robust real-time web applications.
 */
import * as bootstrap from 'bootstrap';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import Alpine from 'alpinejs';

import { registrarComponentesValidacion } from './validacion-documento';
import './toast';
import './datatable';
import './roles-modal';

import $ from 'jquery';
window.$ = window.jQuery = $;

// ─────────────────────────────────────────────────────────────
// 1. Exponer globales PRIMERO
// ─────────────────────────────────────────────────────────────
window.Alpine = Alpine;
window.Pusher = Pusher;
window.bootstrap = bootstrap;
window.HORARIOS_POR_CICLO = window.HORARIOS_POR_CICLO || {};

// ─────────────────────────────────────────────────────────────
// 2. Echo
// ─────────────────────────────────────────────────────────────
try {
    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: import.meta.env.VITE_REVERB_APP_KEY,
        wsHost: import.meta.env.VITE_REVERB_HOST,
        wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
        wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
        forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
        enabledTransports: ['ws', 'wss'],
    });
} catch (e) {
    console.warn('[Echo] No se pudo inicializar Reverb:', e.message);
    window.Echo = null;
}


try {
    registrarComponentesValidacion(Alpine);
} catch (e) {
    console.error('[Alpine] Error registrando componentes de validación:', e);
}

try {
    // ─────────────────────────────────────────────────────────────
    // 3. Registrar TODOS los componentes Alpine (ANTES de start)
    // ─────────────────────────────────────────────────────────────
    Alpine.data('grupoForm', () => ({
        cicloId: '',
        horariosFiltrados: [],
        filtrarHorarios() {
            this.horariosFiltrados = window.HORARIOS_POR_CICLO[this.cicloId] || [];
        }
    }));

    Alpine.data('grupoFormEditar', (cicloInicial, seleccionados) => ({
        cicloId: String(cicloInicial),
        seleccionados: (seleccionados || []).map(Number),
        horariosFiltrados: [],
        init() { this.filtrarHorarios(); },
        filtrarHorarios() {
            this.horariosFiltrados = window.HORARIOS_POR_CICLO[this.cicloId] || [];
        },
        toggle(id) {
            id = Number(id);
            const i = this.seleccionados.indexOf(id);
            if (i > -1) this.seleccionados.splice(i, 1);
            else this.seleccionados.push(id);
        }
    }));
} catch (e) {
    console.error('[Alpine] Error registrando grupoForm:', e);
}

// ─────────────────────────────────────────────────────────────
// 4. Arrancar Alpine AL FINAL, después de todos los Alpine.data()
// ─────────────────────────────────────────────────────────────
try {
    Alpine.start();
} catch (e) {
    console.error('[Alpine] Error en start:', e);
}