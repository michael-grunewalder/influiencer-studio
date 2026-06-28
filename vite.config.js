import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

const origin = process.env.DDEV_PRIMARY_URL;
const port = 5173;

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            detectTls: origin ? undefined : 'vividpersona.bearny-codes.site',
        }),
        tailwindcss(),
    ],
    ...(origin ? {
        server: {
            // The following line is required until the release of https://github.com/vitejs/vite/pull/19241
            cors: { origin },
            // ----------------
            host: '0.0.0.0',
            port: port,
            origin: `${origin}:${port}`,
            strictPort: true
        }
    } : {}),
});
