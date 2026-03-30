<?php

use Illuminate\Support\Facades\Route;
use Goloba\GolobaRMA\Http\Controllers\Shop\GolobaCustomerController;

/**
 * Sobreescribe las rutas de creación de RMA del paquete base de Bagisto
 * para pasar por GolobaCustomerController, que agrega la validación de
 * entrega confirmada por Servientrega.
 *
 * IMPORTANTE: Este archivo se registra ANTES que las rutas del vendor
 * en GolobaRMAServiceProvider, por lo que estas rutas tienen prioridad.
 */
Route::group(['middleware' => ['web', 'theme', 'locale', 'currency', 'rma']], function () {

    // Verifica si una orden aplica retracto (respuesta JSON para AJAX)
    Route::get(
        'customer/account/rma/check-retracto',
        [GolobaCustomerController::class, 'checkRetracto']
    )->name('rma.customers.check-retracto')->middleware(['customer']);

    // Cliente registrado
    Route::post(
        'customer/account/rma/store',
        [GolobaCustomerController::class, 'store']
    )->name('rma.customers.store')->middleware(['customer']);

    // Cliente guest
    Route::post(
        'guest/store',
        [GolobaCustomerController::class, 'storeGuest']
    )->name('rma.guest.store')->middleware(['guest-rma']);

});
