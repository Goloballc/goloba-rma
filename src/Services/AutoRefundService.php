<?php

namespace Goloba\GolobaRMA\Services;

use Goloba\GolobaRMA\Repositories\GolobaRefundItemRepository;
use Illuminate\Support\Facades\Log;
use Webkul\RMA\Repositories\RMAItemsRepository;
use Webkul\Sales\Repositories\RefundRepository;

/**
 * Crea automáticamente un Refund de Bagisto cuando un RMA de tipo 'return'
 * pasa al estado 'Paid', registrando la devolución en el balance del vendedor.
 *
 * No devuelve unidades al stock: el producto puede llegar dañado; la reposición
 * de inventario es responsabilidad del vendedor.
 */
class AutoRefundService
{
    public function __construct(
        protected RefundRepository   $refundRepository,
        protected RMAItemsRepository $rmaItemsRepository,
    ) {}

    /**
     * Intenta crear el refund para el RMA indicado.
     *
     * Devuelve true si el refund se creó con éxito.
     * Devuelve false si no aplica o si la orden no puede ser reembolsada
     * (en ese caso también añade un mensaje de error a la sesión).
     *
     * @param  \Webkul\RMA\Contracts\RMA  $rma
     * @param  string                     $newStatus  Valor recibido en el request (título del status)
     */
    public function handle($rma, string $newStatus): bool
    {
        // Solo actuar cuando el nuevo estado sea 'Paid' y la resolución sea 'return'.
        // Nota: rma.resolution es siempre NULL en Bagisto — la resolución real vive en rma_items.
        $rmaResolution = $this->rmaItemsRepository->findOneByField('rma_id', $rma->id)?->resolution;
        if ($newStatus !== 'Paid' || $rmaResolution !== 'return') {
            return false;
        }

        // Cargamos la orden a través de la relación del modelo (no inyectamos OrderRepository)
        $order = $rma->order;

        if (! $order) {
            Log::error("[AutoRefundService] Orden no encontrada para RMA #{$rma->id} (order_id={$rma->order_id})");
            session()->flash('error', 'No se encontró la orden asociada al RMA. El reembolso no fue procesado.');
            return false;
        }

        if (! $order->canRefund()) {
            Log::warning("[AutoRefundService] RMA #{$rma->id}: la orden #{$order->id} no puede ser reembolsada (canRefund=false).");
            session()->flash('error', 'La orden asociada no puede ser reembolsada en este momento. Verifica que exista una factura y que no esté completamente reembolsada.');
            return false;
        }

        // Construir el array de ítems: [order_item_id => qty] filtrando
        // solo los que aún tienen cantidad disponible para reembolso.
        $rmaItems = $this->rmaItemsRepository->findWhere(['rma_id' => $rma->id]);
        $items = [];

        foreach ($rmaItems as $rmaItem) {
            $orderItem = $rmaItem->getOrderItem()->first();

            if (! $orderItem) {
                continue;
            }

            $qtyToRefund = $orderItem->qty_to_refund ?? 0;

            if ($qtyToRefund <= 0) {
                continue;
            }

            $qty = min((int) $rmaItem->quantity, (int) $qtyToRefund);
            $items[$orderItem->id] = $qty;
        }

        if (empty($items)) {
            Log::warning("[AutoRefundService] RMA #{$rma->id}: no hay ítems con qty_to_refund > 0.");
            session()->flash('error', 'Todos los ítems de esta RMA ya fueron reembolsados anteriormente.');
            return false;
        }

        $refundData = [
            'order_id' => $order->id,
            'refund'   => [
                'items'             => $items,
                'shipping'          => 0,
                'adjustment_refund' => 0,
                'adjustment_fee'    => 0,
            ],
        ];

        // Validar que el monto calculado sea mayor que cero
        try {
            $totals = $this->refundRepository->getOrderItemsRefundSummary($refundData['refund'], $order->id);
        } catch (\Exception $e) {
            Log::error("[AutoRefundService] RMA #{$rma->id}: error calculando totales: " . $e->getMessage());
            session()->flash('error', 'Error calculando el monto del reembolso: ' . $e->getMessage());
            return false;
        }

        if (! $totals || ! $totals['grand_total']['price']) {
            session()->flash('error', 'El monto calculado del reembolso es cero. El reembolso no fue procesado.');
            return false;
        }

        // Crear el refund suprimiendo la devolución de stock
        try {
            GolobaRefundItemRepository::$skipStockReturn = true;
            $this->refundRepository->create($refundData);
        } catch (\Exception $e) {
            Log::error("[AutoRefundService] RMA #{$rma->id}: error creando refund: " . $e->getMessage());
            session()->flash('error', 'Error al registrar el reembolso: ' . $e->getMessage());
            return false;
        } finally {
            GolobaRefundItemRepository::$skipStockReturn = false;
        }

        Log::info("[AutoRefundService] RMA #{$rma->id}: refund creado exitosamente para orden #{$order->id}.");

        return true;
    }
}
