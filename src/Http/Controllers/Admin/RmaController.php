<?php

namespace Goloba\GolobaRMA\Http\Controllers\Admin;

use Goloba\GolobaRMA\Mail\RmaMailHelper;
use Goloba\GolobaRMA\Mail\StatusUpdate;
use Webkul\RMA\Http\Controllers\Admin\RmaController as VendorRmaController;

/**
 * Extiende el RmaController del vendor para:
 * - Suprimir el correo al cliente del vendor ('Status Updated') y reemplazarlo con el nuestro.
 * - Notificar al seller cuando el admin cambia el estado de un RMA.
 */
class RmaController extends VendorRmaController
{
    public function saveRmaStatus(): \Illuminate\Http\RedirectResponse
    {
        $status  = request()->input();
        $rma     = $this->rmaRepository->find($status['rma_id']);
        $orderId = $rma?->order_id;

        // Suprimir el correo al cliente del vendor ('Status Updated') — lo reemplazamos con el nuestro
        $listener = \Illuminate\Support\Facades\Event::listen(
            'Illuminate\Mail\Events\MessageSending',
            function ($event) {
                if (($event->message->getSubject() ?? '') === 'Status Updated') {
                    return false;
                }
            }
        );

        // Ejecutar la lógica completa del vendor
        $response = parent::saveRmaStatus();

        \Illuminate\Support\Facades\Event::forget('Illuminate\Mail\Events\MessageSending');

        // Notificar al seller
        if ($orderId) {
            $this->notifySellerStatusChange($status['rma_id'], $orderId, $status['rma_status']);
        }

        // Notificar al cliente con nuestro correo
        if ($orderId) {
            $order = $this->orderRepository->find($orderId);
            if ($order) {
                RmaMailHelper::queueMail(new StatusUpdate([
                    'email'       => $order->customer_email,
                    'name'        => $order->customer_first_name . ' ' . $order->customer_last_name,
                    'rma_id'      => $status['rma_id'],
                    'order_id'    => $orderId,
                    'rma_status'  => $status['rma_status'],
                    'body'        => trans('goloba-rma::app.mail.status-update.body-customer-by-admin', ['rma_id' => $status['rma_id']]),
                    'subject_key' => 'goloba-rma::app.mail.status-update.subject-customer',
                    'view_url'    => route('rma.customer.view', $status['rma_id']),
                ]));
            }
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
            'body'       => trans('goloba-rma::app.mail.status-update.body-seller', ['rma_id' => $rmaId]),
            'subject_key'=> 'goloba-rma::app.mail.status-update.subject-seller',
            'view_url'   => $viewUrl,
        ]));
    }
}
