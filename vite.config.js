import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

const port = 5173;
const origin = process.env.DDEV_PRIMARY_URL;
export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        // The following line is require until the release of https://github.com/vitejs/vite/pull/19241
        cors: { origin },
        // ----------------
        host: '0.0.0.0',
        port: port,
        origin: `${origin}:${port}`,
        strictPort: true
    },
});
