<?php

namespace Goloba\GolobaRMA\DataGrids\Shop;

use Illuminate\Support\Facades\DB;
use Webkul\Sales\Models\Order;
use Webkul\RMA\DataGrids\Shop\OrderRMADataGrid as BaseOrderRMADataGrid;

/**
 * Sobreescribe el DataGrid de órdenes para RMA del paquete base.
 *
 * Regla de negocio de Goloba: solo se pueden abrir RMAs sobre órdenes
 * con estado `completed` (entregadas según Bagisto) Y que además tengan
 * al menos un shipment confirmado como entregado por Servientrega
 * (shipments.status = 'delivered', actualizado en tiempo real por el
 * callback del microservicio servientrega-webhook — ver
 * DeliveryCallbackController en el paquete ServientregaTracking).
 *
 * orders.status == completed por si solo NO implica entrega real: Bagisto
 * lo marca así cuando el pedido fue enviado, no cuando el cliente lo
 * recibió. shipments.status es la fuente de verdad para la entrega.
 *
 * La validación final y autoritativa sigue ocurriendo en el servidor al
 * momento de crear el RMA (GolobaCustomerController::validarEntregaConfirmada),
 * este filtro solo evita mostrar en el listado órdenes que de todas formas
 * serían rechazadas al intentar crear la solicitud.
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

        // Exigir que exista al menos un shipment de la orden ya confirmado
        // como entregado por Servientrega. Se usa whereExists (no join) para
        // no duplicar filas antes del groupBy/SUM que ya hace el query base.
        $queryBuilder->whereExists(function ($query) {
            $query->select(DB::raw(1))
                ->from('shipments')
                ->whereColumn('shipments.order_id', 'orders.id')
                ->where('shipments.status', 'delivered');
        });

        return $queryBuilder;
    }
}
