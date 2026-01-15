<?php

namespace Goloba\GolobaRMA\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        // Aquí se registrarán los eventos relacionados con RMA
    ];
}
