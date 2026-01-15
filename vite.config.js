import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import path from 'path';

/**
 * Configuración de Vite para GolobaRMA
 * 
 * NOTA: Solo se usa cuando se activan estilos custom.
 * 
 * Para compilar assets:
 * 1. npm install
 * 2. npm run build
 * 3. php artisan vendor:publish --tag=goloba-rma-assets
 */
export default defineConfig({
    build: {
        outDir: 'publishable/default/build',
        emptyOutDir: true,
        manifest: true,
    },
    plugins: [
        laravel({
            input: [
                'src/Resources/assets/css/seller.css',
                'src/Resources/assets/css/customer.css',
                'src/Resources/assets/js/app.js',
            ],
            publicDirectory: 'publishable/default',
            buildDirectory: 'build',
        }),
    ],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'src/Resources/assets'),
        },
    },
});
