import { defineConfig, loadEnv } from 'vite';
import vue from '@vitejs/plugin-vue';
import vuetify from 'vite-plugin-vuetify';
import path from 'path';

// https://vite.dev/config/
export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');
    const apiBaseUrl = env.VITE_API_BASE_URL?.replace('/api', '');
    
    if (!apiBaseUrl) {
        throw new Error('VITE_API_BASE_URL no está configurada en el archivo .env');
    }

    return {
        plugins: [
            vue(),
            vuetify({ autoImport: true }),
        ],
        resolve: {
            alias: {
                '@': path.resolve(__dirname, './src'),
            },
        },
        server: {
            port: 5173,
            proxy: {
                '/api': {
                    target: apiBaseUrl,
                    changeOrigin: true,
                    secure: false,
                    rewrite: (path) => path, // No reescribir la ruta
                    configure: (proxy, _options) => {
                        proxy.on('proxyReq', (proxyReq, req, _res) => {
                            // Asegurar que las cookies se envíen correctamente
                            if (req.headers.cookie) {
                                proxyReq.setHeader('Cookie', req.headers.cookie);
                            }
                        });
                    },
                },
                '/storage': {
                    target: apiBaseUrl,
                    changeOrigin: true,
                    secure: false,
                },
                '/dist': {
                    target: apiBaseUrl,
                    changeOrigin: true,
                    secure: false,
                },
            },
        },
    };
});
