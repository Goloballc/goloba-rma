<?php

namespace Goloba\GolobaRMA\Http\Controllers\Admin;

use Goloba\GolobaRMA\Mail\RmaMailHelper;
use Goloba\GolobaRMA\Mail\StatusUpdate;
use Webkul\RMA\Http\Controllers\Admin\RmaController as VendorRmaController;

/**
 * Extiende el RmaController del vendor para agregar notificación al seller
 * cuando el admin cambia el estado de un RMA.
 * El vendor ya notifica al cliente; nosotros sólo añadimos la notificación al seller.
 */
class RmaController extends VendorRmaController
{
    public function saveRmaStatus(): \Illuminate\Http\RedirectResponse
    {
        $status  = request()->input();
        $rma     = $this->rmaRepository->find($status['rma_id']);
        $orderId = $rma?->order_id;

        // Ejecutar la lógica completa del vendor (incluye correo al cliente)
        $response = parent::saveRmaStatus();

        // Añadir correo al seller
        if ($orderId) {
            $this->notifySellerStatusChange($status['rma_id'], $orderId, $status['rma_status']);
        }

        return $response;
    }

    // -------------------------------------------------------------------------

    private function notifySellerStatusChange(int $rmaId, int $orderId, string $rmaStatus): void
    {
        $sellerData = RmaMailHelper::getSellerData($orderId);
        if (! $sellerData) {
            return;
        }

        $order    = $this->orderRepository->find($orderId);
        $viewUrl  = url(config('app.admin_url') . '/rma/' . $rmaId);

        RmaMailHelper::queueMail(new StatusUpdate([
            'email'      => $sellerData['seller_email'],
            'name'       => $sellerData['seller_name'],
            'rma_id'     => $rmaId,
            'order_id'   => $orderId,
            'rma_status' => $rmaStatus,
            'body'       => trans('goloba-rma::app.mail.status-update.body-seller'),
            'subject_key'=> 'goloba-rma::app.mail.status-update.subject-seller',
            'view_url'   => $viewUrl,
        ]));
    }
}
