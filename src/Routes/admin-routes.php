<?php

use Illuminate\Support\Facades\Route;
use Goloba\GolobaRMA\Http\Controllers\Admin\HolidaysController;
use Goloba\GolobaRMA\Http\Controllers\Admin\DisputeController;
use Goloba\GolobaRMA\Http\Controllers\Admin\RmaController as GolobaAdminRmaController;

Route::group([
    'middleware' => ['web', 'admin'],
    'prefix'     => config('app.admin_url') . '/rma',
], function () {

    // ── Override: cambio de estado por admin — notifica también al seller ────
    // Registrado DESPUÉS que el vendor → este nombre gana la prioridad
    Route::post('save-rma-status', [GolobaAdminRmaController::class, 'saveRmaStatus'])
        ->name('admin.sales.rma.save.status');

    // ── Disputas ─────────────────────────────────────────────────────────────
    Route::controller(DisputeController::class)->prefix('disputes')->group(function () {
        Route::get('{rma_id}',         'view')   ->name('admin.rma.dispute.view');
        Route::post('{rma_id}/resolve','resolve')->name('admin.rma.dispute.resolve');
    });

    // ── Festivos ──────────────────────────────────────────────────────────────
    Route::controller(HolidaysController::class)->prefix('holidays')->group(function () {
        Route::get('',                  'index')      ->name('admin.rma.holidays.index');
        Route::post('import',           'import')     ->name('admin.rma.holidays.import');
        Route::get('{year}',            'show')       ->name('admin.rma.holidays.show');
        Route::delete('{year}',         'destroyYear')->name('admin.rma.holidays.destroyYear');
        Route::delete('{year}/{date}',  'destroy')    ->name('admin.rma.holidays.destroy');
    });

    // ── Alias en español (/festivos → mismas acciones) ───────────────────────
    Route::controller(HolidaysController::class)->prefix('festivos')->group(function () {
        Route::get('',                  'index')      ->name('admin.rma.festivos.index');
        Route::post('importar',         'import')     ->name('admin.rma.festivos.importar');
        Route::get('{year}',            'show')       ->name('admin.rma.festivos.show');
        Route::delete('{year}',         'destroyYear')->name('admin.rma.festivos.destroyYear');
        Route::delete('{year}/{date}',  'destroy')    ->name('admin.rma.festivos.destroy');
    });

});
