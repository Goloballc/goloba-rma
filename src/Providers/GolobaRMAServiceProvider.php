<?php

namespace Goloba\GolobaRMA\Providers;

use Goloba\GolobaRMA\Services\RetractoService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
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

        // Rutas admin del paquete Goloba RMA
        $this->loadRoutesFrom(__DIR__ . '/../Routes/admin-routes.php');

        // Namespace de vistas admin (goloba-rma::admin.*)
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'goloba-rma');

        // Traducciones del paquete bajo el namespace 'goloba-rma'
        // Uso: trans('goloba-rma::app.mail.new-request.greeting', ['name' => $name])
        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'goloba-rma');

        // Registrar config de categorías de retracto bajo la clave 'retracto'
        $this->mergeConfigFrom(__DIR__ . '/../Config/retracto.php', 'retracto');

        // Entrada de festivos en el menú admin de RMA
        $this->mergeConfigFrom(__DIR__ . '/../Config/admin-menu.php', 'menu.admin');

        // Nota: loadViewsFrom para 'goloba-rma' ya está registrado arriba junto a las migraciones
        
        // IMPORTANTE: Sobrescribir vistas del paquete RMA original
        // El paquete RMA usa el namespace 'rma' que apunta a su carpeta Resources/views
        // Necesitamos anteponer nuestra carpeta para que Laravel la revise primero
        view()->prependNamespace('rma', __DIR__ . '/../Resources/views');

        $this->loadRoutesFrom(__DIR__ . '/../Routes/seller-routes.php');

        // Rutas del shop: se registran en `booted` para ejecutarse DESPUÉS que
        // el vendor (bagisto-rma vía Concord). En Laravel la última ruta con el
        // mismo nombre gana, por lo que registrar al final nos da prioridad.
        $this->app->booted(function () {
            $this->loadRoutesFrom(__DIR__ . '/../Routes/shop-routes.php');

            // Cambio 6: al crear un producto (admin o seller), habilitar RMA con
            // la regla Standard (id=1) por defecto, sin requerir acción manual.
            Event::listen('catalog.product.create.after', function ($product) {
                app(\Webkul\Product\Repositories\ProductRepository::class)
                    ->where('id', $product->id)
                    ->update([
                        'allow_rma' => 'yes',
                        'rma_rules' => '1',
                    ]);
            });
        });

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

        // Registrar RetractoService como singleton
        $this->app->singleton(RetractoService::class);

        // Sobreescribir el DataGrid de órdenes para RMA del shop:
        // solo muestra órdenes completadas (entregadas).
        $this->app->bind(
            \Webkul\RMA\DataGrids\Shop\OrderRMADataGrid::class,
            \Goloba\GolobaRMA\DataGrids\Shop\OrderRMADataGrid::class
        );
    }
}
