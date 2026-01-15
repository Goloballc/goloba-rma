<?php

use Illuminate\Support\Facades\Route;
use Goloba\GolobaRMA\Http\Controllers\Seller\RMAController;

/**
 * Rutas para el módulo RMA de vendedores
 */
Route::group([
    'middleware' => ['locale', 'theme', 'currency', 'marketplace', 'seller'],
    'prefix'     => 'vendedor/cuenta',
], function () {
    Route::controller(RMAController::class)->prefix('rma')->group(function () {
        // === RUTAS DE DEBUG (TEMPORALES - COMENTAR EN PRODUCCIÓN) ===
        // Route::get('debug', function() {
        //     return view('goloba-rma::seller.rma.debug');
        // })->name('goloba.seller.rma.debug');
        
        // Route::get('help', function() {
        //     return view('goloba-rma::seller.rma.help');
        // })->name('goloba.seller.rma.help');
        
        // Route::post('create-test-rma', function() {
        //     try {
        //         $orderId = request()->input('order_id', 27);
        //         
        //         $orderItem = DB::table('order_items')->where('order_id', $orderId)->first();
        //         
        //         if (!$orderItem) {
        //             return response()->json(['success' => false, 'message' => 'No se encontró order item']);
        //         }
        //         
        //         $rmaId = DB::table('rma')->insertGetId([
        //             'order_id' => $orderId,
        //             'rma_status' => 'Pending',
        //             'rma_type' => 'standard',
        //             'order_status' => '1',
        //             'package_condition' => 'packed',
        //             'information' => 'RMA de prueba creada automáticamente',
        //             'created_at' => now(),
        //             'updated_at' => now(),
        //         ]);
        //         
        //         DB::table('rma_items')->insert([
        //             'rma_id' => $rmaId,
        //             'order_item_id' => $orderItem->id,
        //             'quantity' => 1,
        //             'resolution' => 'return',
        //             'reason' => 'Prueba del sistema RMA',
        //             'created_at' => now(),
        //             'updated_at' => now(),
        //         ]);
        //         
        //         return response()->json([
        //             'success' => true, 
        //             'rma_id' => $rmaId,
        //             'message' => 'RMA creada exitosamente'
        //         ]);
        //         
        //     } catch (\Exception $e) {
        //         return response()->json([
        //             'success' => false, 
        //             'message' => $e->getMessage()
        //         ]);
        //     }
        // })->name('goloba.seller.rma.create-test');
        // === FIN RUTAS DE DEBUG ===
        
        // Listar RMAs
        Route::get('', 'index')->name('goloba.seller.rma.index');
        
        // Ver detalle de RMA
        Route::get('view/{id}', 'view')->name('goloba.seller.rma.view');
        
        // Cambiar estado (aceptar/rechazar)
        Route::post('change-status', 'changeStatus')->name('goloba.seller.rma.change_status');
        
        // Chat - Obtener mensajes
        Route::get('messages', 'getMessages')->name('goloba.seller.rma.messages');
        
        // Chat - Enviar mensaje
        Route::post('send-message', 'sendMessage')->name('goloba.seller.rma.send_message');
        
        // Obtener mensajes (legacy - mantener por compatibilidad)
        Route::get('get-messages', 'getMessages')->name('goloba.seller.rma.get_messages');
        
        // Enviar mensaje
        Route::post('send-message', 'sendMessage')->name('goloba.seller.rma.send_message');
    });
});
