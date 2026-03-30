<?php

/**
 * Entradas adicionales al menú admin de Bagisto para el paquete GolobaRMA.
 *
 * Se fusionan con 'menu.admin' via mergeConfigFrom en el ServiceProvider.
 * La clave padre 'rma' ya está registrada por el vendor bagisto-rma,
 * por lo que solo agregamos el subítem.
 */
return [
    [
        'key'   => 'rma.holidays',
        'name'  => 'Festivos',
        'route' => 'admin.rma.holidays.index',
        'sort'  => 10,
        'icon'  => '',
    ],
];
