<?php

namespace Goloba\GolobaRMA\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class GolobaRMAServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        // Cargar vistas del paquete GolobaRMA
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'goloba-rma');
        
        // IMPORTANTE: Sobrescribir vistas del paquete RMA original
        // El paquete RMA usa el namespace 'rma' que apunta a su carpeta Resources/views
        // Necesitamos anteponer nuestra carpeta para que Laravel la revise primero
        view()->prependNamespace('rma', __DIR__ . '/../Resources/views');

        $this->loadRoutesFrom(__DIR__ . '/../Routes/seller-routes.php');

        // Publicar vistas para personalización
        $this->publishes([
            __DIR__ . '/../Resources/views' => resource_path('themes/default/views/vendor/goloba-rma'),
        ], 'goloba-rma-views');

        // Publicar assets (PREPARADO - solo usar cuando se compilen estilos custom)
        // Para activar:
        // 1. cd packages/Goloba/GolobaRMA
        // 2. npm install
        // 3. npm run build
        // 4. php artisan vendor:publish --tag=goloba-rma-assets --force
        $this->publishes([
            __DIR__ . '/../../publishable' => public_path('themes/goloba-rma'),
        ], 'goloba-rma-assets');

        // Registrar el menú del vendedor
        $this->mergeConfigFrom(
            __DIR__ . '/../Config/seller-menu.php', 'menu.seller'
        );
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(EventServiceProvider::class);
        
        // Registrar nuestro modelo extendido de RMAMessages
        $this->app->bind(
            \Webkul\RMA\Contracts\RMAMessages::class,
            \Goloba\GolobaRMA\Models\RMAMessages::class
        );
        
        // Registrar nuestro repositorio extendido de RMAMessages
        $this->app->bind(
            \Webkul\RMA\Repositories\RMAMessagesRepository::class,
            \Goloba\GolobaRMA\Repositories\RMAMessagesRepository::class
        );
    }
}
