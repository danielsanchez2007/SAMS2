import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');
    const viteHost = env.VITE_HOST || 'sams';
    const vitePort = Number(env.VITE_PORT || 5173);
    const viteBindHost = env.VITE_BIND_HOST || '0.0.0.0';

    return {
        plugins: [
            laravel({
                input: ['resources/css/app.css', 'resources/js/app.js'],
                refresh: true,
            }),
            tailwindcss(),
        ],
        server: {
            host: viteBindHost,
            port: vitePort,
            strictPort: true,
            hmr: {
                host: viteHost,
                port: vitePort,
            },
            watch: {
                ignored: ['**/storage/framework/views/**'],
            },
        },
    };
});
