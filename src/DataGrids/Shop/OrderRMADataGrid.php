<?php

namespace Goloba\GolobaRMA\DataGrids\Shop;

use Illuminate\Support\Facades\DB;
use Webkul\Sales\Models\Order;
use Webkul\RMA\DataGrids\Shop\OrderRMADataGrid as BaseOrderRMADataGrid;

/**
 * Sobreescribe el DataGrid de órdenes para RMA del paquete base.
 *
 * Regla de negocio de Goloba: solo se pueden abrir RMAs sobre órdenes
 * con estado `completed` (entregadas según Bagisto). La confirmación
 * real de entrega la valida el servidor vía Servientrega al momento
 * de crear el RMA — este filtro evita mostrar órdenes irrelevantes.
 *
 * Órdenes pending/processing no deben aparecer aquí: si el cliente
 * quiere cancelar antes del envío, debe usar el flujo de cancelación
 * de orden, no el de RMA/devolución.
 */
class OrderRMADataGrid extends BaseOrderRMADataGrid
{
    public function prepareQueryBuilder(): \Illuminate\Database\Query\Builder
    {
        $queryBuilder = parent::prepareQueryBuilder();

        // Restringir a órdenes completadas (entregadas según Bagisto).
        // Reemplaza el filtro condicional del paquete base que dependía
        // de una configuración del admin que no usamos.
        $queryBuilder->where('orders.status', Order::STATUS_COMPLETED);

        return $queryBuilder;
    }
}
