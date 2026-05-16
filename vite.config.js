import { fileURLToPath, URL } from 'node:url';
import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');
    const herdHost = env.APP_URL ? new URL(env.APP_URL).hostname : null;
    const useHerdTls = herdHost && herdHost.endsWith('.test') ? herdHost : false;

    return {
        plugins: [
            laravel({
                input: 'resources/js/app.js',
                refresh: true,
                detectTls: useHerdTls,
            }),
            vue({
                template: {
                    transformAssetUrls: {
                        base: null,
                        includeAbsolute: false,
                    },
                },
            }),
        ],
        resolve: {
            alias: {
                '@': fileURLToPath(new URL('./resources/js', import.meta.url)),
            },
        },
    };
});
