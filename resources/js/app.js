//

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allow your team to quickly build robust real-time web applications.
 */
import * as bootstrap from 'bootstrap';
import Alpine from 'alpinejs';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import { registrarComponentesValidacion } from './validacion-documento';
import './toast';

import $ from 'jquery';
window.$ = window.jQuery = $;
import './datatable';

import './roles-modal'; 


window.Pusher = Pusher;
window.Alpine = Alpine;
window.bootstrap = bootstrap;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
});

// Registrar los componentes en Alpine antes del inicio
registrarComponentesValidacion(Alpine);

Alpine.start();