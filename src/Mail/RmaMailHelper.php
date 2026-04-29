<?php

namespace Goloba\GolobaRMA\Mail;

use Illuminate\Support\Facades\DB;

/**
 * Utilidades de correo para GolobaRMA.
 * Encapsula la obtención del seller a partir de un order_id,
 * siguiendo la relación: rma.order_id → marketplace_orders → marketplace_sellers
 */
class RmaMailHelper
{
    /**
     * Obtiene email y nombre del seller dueño de una orden.
     * Devuelve null si la orden no tiene seller asociado (ej: orden directa).
     *
     * @return array{seller_email: string, seller_name: string, shop_title: string}|null
     */
    public static function getSellerData(int $orderId): ?array
    {
        $result = DB::table('marketplace_orders as mo')
            ->join('marketplace_sellers as ms', 'mo.marketplace_seller_id', '=', 'ms.id')
            ->where('mo.order_id', $orderId)
            ->select('ms.email as seller_email', 'ms.name as seller_name', 'ms.shop_title')
            ->first();

        if (! $result) {
            return null;
        }

        return [
            'seller_email' => $result->seller_email,
            'seller_name'  => $result->seller_name  ?: ($result->shop_title ?: 'Vendedor'),
            'shop_title'   => $result->shop_title   ?: $result->seller_name,
        ];
    }

    /**
     * Envía un mailable usando queue (igual que el vendor).
     * Silencia excepciones y las registra en el log, igual que el patrón del vendor.
     */
    public static function queueMail(object $mailable): void
    {
        try {
            \Illuminate\Support\Facades\Mail::queue($mailable);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('[GolobaRMA] Error enviando correo: ' . $e->getMessage());
        }
    }
}
