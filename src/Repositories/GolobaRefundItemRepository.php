<?php

namespace Goloba\GolobaRMA\Repositories;

use Webkul\Sales\Repositories\RefundItemRepository;

/**
 * Proxy de RefundItemRepository que suprime la devolución automática de stock
 * cuando el refund es creado desde un RMA de tipo 'return'.
 *
 * Usa delegación en lugar de herencia para evitar que BaseRepository intente
 * instanciar contratos de Bagisto en el momento del binding.
 *
 * El producto devuelto puede llegar dañado o en condiciones inaceptables,
 * por lo que la reposición de inventario debe ser responsabilidad del vendedor.
 *
 * Uso desde AutoRefundService:
 *   GolobaRefundItemRepository::$skipStockReturn = true;
 *   $refundRepository->create($data);
 *   GolobaRefundItemRepository::$skipStockReturn = false;
 */
class GolobaRefundItemRepository extends RefundItemRepository
{
    /**
     * Cuando es true, returnQtyToProductInventory no hace nada.
     * Activar antes de crear un refund de RMA, desactivar inmediatamente después.
     */
    public static bool $skipStockReturn = false;

    /**
     * {@inheritdoc}
     */
    public function returnQtyToProductInventory($orderItem, $quantity): void
    {
        if (static::$skipStockReturn) {
            return;
        }

        parent::returnQtyToProductInventory($orderItem, $quantity);
    }
}
