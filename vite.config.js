import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/pensum.css',
                'resources/css/cambiocarrera.css',
                'resources/css/evaluaciones-docentes.css',
                'resources/css/reglas-academicas.css',
                'resources/css/notas-estudiante.css',
                'resources/css/roles-permisos.css',
                'resources/css/profile.css',
                'resources/css/toast.css',
                'resources/js/app.js'
            ],
            refresh: true,
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                }),
            ],
        }),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});