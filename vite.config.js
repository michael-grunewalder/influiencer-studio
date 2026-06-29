import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

const isDdev = !!process.env.DDEV_PRIMARY_URL;
const ddevUrl = process.env.DDEV_PRIMARY_URL;
const port = 5173;

const herdDomain = 'vividpersona.bearny-codes.site';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            // Wenn Herd genutzt wird, überlassen wir dem Laravel-Plugin die Erkennung,
            // füttern es aber mit der exakten Domain.
            detectTls: isDdev ? undefined : herdDomain,
        }),
        tailwindcss(),
    ],
    resolve: {
        alias: {
            '@': '/resources/js',
        },
    },
    server: {
        // '0.0.0.0' sorgt dafür, dass Vite auf allen lokalen Interfaces lauscht, 
        // nicht nur streng auf 127.0.0.1
        host: '0.0.0.0', 
        port: port,
        strictPort: true,
        // Erlaubt dem Browser den Zugriff aus dem Herd-Netzwerk
        cors: true,
        allowedHosts: isDdev ? [] : [herdDomain],
        hmr: isDdev ? {
            host: ddevUrl?.replace('https://', ''),
        } : {
            host: herdDomain,
        },
    },
});