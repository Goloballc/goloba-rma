<?php

use Illuminate\Support\Facades\Route;
use Goloba\GolobaRMA\Http\Controllers\Seller\RMAController;

Route::group([
    'middleware' => ['locale', 'theme', 'currency', 'marketplace', 'seller'],
    'prefix'     => 'vendedor/cuenta',
], function () {
    Route::controller(RMAController::class)->prefix('rma')->group(function () {
        Route::get('',                  'index')        ->name('goloba.seller.rma.index');
        Route::get('view/{id}',         'view')         ->name('goloba.seller.rma.view');
        Route::post('change-status',    'changeStatus') ->name('goloba.seller.rma.change_status');
        Route::post('dispute',          'submitDispute')->name('goloba.seller.rma.dispute');
        Route::post('save-rma-status',  'saveRmaStatus')->name('goloba.seller.rma.save.status');
        Route::get('messages',          'getMessages')  ->name('goloba.seller.rma.messages');
        Route::post('send-message',     'sendMessage')  ->name('goloba.seller.rma.send_message');
    });
});
