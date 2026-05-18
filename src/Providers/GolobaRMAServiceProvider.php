<?php

namespace Goloba\GolobaRMA\Providers;

use Goloba\GolobaRMA\Services\RetractoService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;

class GolobaRMAServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Modelo y repositorio de mensajes extendidos (agrega campo is_seller)
        $this->app->bind(
            \Webkul\RMA\Contracts\RMAMessages::class,
            \Goloba\GolobaRMA\Models\RMAMessages::class
        );
        $this->app->bind(
            \Webkul\RMA\Repositories\RMAMessagesRepository::class,
            \Goloba\GolobaRMA\Repositories\RMAMessagesRepository::class
        );

        // RetractoService: singleton — cachea festivos en memoria durante el request
        $this->app->singleton(RetractoService::class);

        // DataGrid de órdenes para RMA en el shop: solo muestra órdenes completadas
        $this->app->bind(
            \Webkul\RMA\DataGrids\Shop\OrderRMADataGrid::class,
            \Goloba\GolobaRMA\DataGrids\Shop\OrderRMADataGrid::class
        );
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'goloba-rma');
        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'goloba-rma');

        $this->mergeConfigFrom(__DIR__ . '/../Config/retracto.php', 'retracto');
        $this->mergeConfigFrom(__DIR__ . '/../Config/admin-menu.php', 'menu.admin');
        $this->mergeConfigFrom(__DIR__ . '/../Config/seller-menu.php', 'menu.seller');

        $this->loadRoutesFrom(__DIR__ . '/../Routes/admin-routes.php');
        $this->loadRoutesFrom(__DIR__ . '/../Routes/seller-routes.php');

        // Las rutas del shop se registran en booted() para ejecutarse DESPUÉS del vendor,
        // garantizando que nuestros nombres de ruta sobreescriban los del vendor.
        $this->app->booted(function () {
            $this->loadRoutesFrom(__DIR__ . '/../Routes/shop-routes.php');

            // Al crear un producto, habilitar RMA con la regla Standard (id=1) por defecto
            Event::listen('catalog.product.create.after', function ($product) {
                app(\Webkul\Product\Repositories\ProductRepository::class)
                    ->where('id', $product->id)
                    ->update([
                        'allow_rma' => 'yes',
                        'rma_rules' => '1',
                    ]);
            });
        });

        $this->publishes([
            __DIR__ . '/../Resources/views' => resource_path('themes/default/views/vendor/goloba-rma'),
        ], 'goloba-rma-views');
    }
}
