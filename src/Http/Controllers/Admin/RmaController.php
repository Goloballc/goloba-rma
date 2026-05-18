<?php

namespace Goloba\GolobaRMA\Http\Controllers\Admin;

use Goloba\GolobaRMA\Mail\RmaMailHelper;
use Goloba\GolobaRMA\Mail\StatusUpdate;
use Webkul\RMA\Http\Controllers\Admin\RmaController as VendorRmaController;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Sales\Repositories\OrderItemRepository;
use Webkul\Sales\Repositories\RefundRepository;
use Webkul\RMA\Repositories\RMAAdditionalFieldRepository;
use Webkul\RMA\Repositories\RMAMessagesRepository;
use Webkul\RMA\Repositories\RMARepository;
use Webkul\RMA\Repositories\RMAItemsRepository;
use Webkul\RMA\Repositories\RMAStatusRepository;

/**
 * Extiende el RmaController del vendor para:
 * - Suprimir el correo al cliente del vendor ('Status Updated') y reemplazarlo con el nuestro.
 * - Notificar al seller cuando el admin cambia el estado de un RMA.
 * - Crear automáticamente un Refund cuando el RMA pasa a estado 'Paid' con resolución 'return'.
 */
class RmaController extends VendorRmaController
{
    public function __construct(
        OrderItemRepository          $orderItemRepository,
        OrderRepository              $orderRepository,
        RMAAdditionalFieldRepository $rmaAdditionalFieldRepository,
        RMAItemsRepository           $rmaItemsRepository,
        RMAMessagesRepository        $rmaMessagesRepository,
        RMARepository                $rmaRepository,
        RMAStatusRepository          $rmaStatusRepository,
        RefundRepository             $refundRepository,
    ) {
        parent::__construct(
            $orderItemRepository,
            $orderRepository,
            $rmaAdditionalFieldRepository,
            $rmaItemsRepository,
            $rmaMessagesRepository,
            $rmaRepository,
            $rmaStatusRepository,
            $refundRepository,
        );
    }

    public function saveRmaStatus(): \Illuminate\Http\RedirectResponse
    {
        $status  = request()->input();
        $rma     = $this->rmaRepository->find($status['rma_id']);
        $orderId = $rma?->order_id;

        // Si el nuevo estado es 'Paid', intentar crear el refund automáticamente.
        // El servicio verifica internamente la resolución — si no es 'return', retorna false sin error.
        if (($status['rma_status'] ?? '') === 'Paid') {
            $autoRefundService = app(\Goloba\GolobaRMA\Services\AutoRefundService::class);
            $refundCreated = $autoRefundService->handle($rma, 'Paid');

            if (! $refundCreated) {
                return redirect()->back();
            }

            // Guardamos el estado directamente — no llamamos al vendor porque
            // su saveRmaStatus() detecta qty_invoiced y redirige a refunds en lugar de guardar.
            $rma->update(['rma_status' => 'Paid']);

            $this->rmaMessagesRepository->create([
                'message'    => 'Estado actualizado a Paid por el administrador.',
                'rma_id'     => $status['rma_id'],
                'is_admin'   => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($orderId) {
                $this->notifySellerStatusChange($status['rma_id'], $orderId, 'Paid');
                $order = $this->orderRepository->find($orderId);
                if ($order) {
                    RmaMailHelper::queueMail(new StatusUpdate([
                        'email'       => $order->customer_email,
                        'name'        => $order->customer_first_name . ' ' . $order->customer_last_name,
                        'rma_id'      => $status['rma_id'],
                        'order_id'    => $orderId,
                        'rma_status'  => 'Paid',
                        'body'        => trans('goloba-rma::app.mail.status-update.body-customer-by-admin', ['rma_id' => $status['rma_id']]),
                        'subject_key' => 'goloba-rma::app.mail.status-update.subject-customer',
                        'view_url'    => route('rma.customer.view', $status['rma_id']),
                    ]));
                }
            }

            session()->flash('success', 'Estado actualizado a Paid y reembolso creado correctamente.');
            return redirect()->route('admin.sales.rma.view', ['id' => $status['rma_id']]);
        }

        // Suprimir el correo al cliente del vendor ('Status Updated') — lo reemplazamos con el nuestro
        \Illuminate\Support\Facades\Event::listen(
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

        $viewUrl = url(config('app.admin_url') . '/rma/' . $rmaId);

        RmaMailHelper::queueMail(new StatusUpdate([
            'email'       => $sellerData['seller_email'],
            'name'        => $sellerData['seller_name'],
            'rma_id'      => $rmaId,
            'order_id'    => $orderId,
            'rma_status'  => $rmaStatus,
            'body'        => trans('goloba-rma::app.mail.status-update.body-seller', ['rma_id' => $rmaId]),
            'subject_key' => 'goloba-rma::app.mail.status-update.subject-seller',
            'view_url'    => $viewUrl,
        ]));
    }
}
