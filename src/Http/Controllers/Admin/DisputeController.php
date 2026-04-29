<?php

namespace Goloba\GolobaRMA\Http\Controllers\Admin;

use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\RMA\Repositories\RMARepository;
use Goloba\GolobaRMA\Mail\DisputeResolved;
use Goloba\GolobaRMA\Mail\RmaMailHelper;
use Goloba\GolobaRMA\Models\RmaDispute;

class DisputeController extends Controller
{
    public function __construct(
        protected RMARepository $rmaRepository,
    ) {}

    /**
     * Muestra el detalle de la disputa de un RMA.
     * GET admin/rma/disputes/{rma_id}
     */
    public function view(int $rmaId): View|RedirectResponse
    {
        $rma = $this->rmaRepository->find($rmaId);

        if (! $rma) {
            session()->flash('error', 'RMA no encontrada.');
            return redirect()->route('admin.sales.rma.index');
        }

        $dispute = RmaDispute::with('images')->where('rma_id', $rmaId)->latest()->first();

        if (! $dispute) {
            session()->flash('error', 'Esta RMA no tiene una disputa activa.');
            return redirect()->route('admin.sales.rma.view', $rmaId);
        }

        return view('goloba-rma::admin.rma.dispute', compact('rma', 'dispute'));
    }

    /**
     * Resuelve la disputa: aprobar (→ Declined) o rechazar (→ Accept).
     * POST admin/rma/disputes/{rma_id}/resolve
     */
    public function resolve(int $rmaId): RedirectResponse
    {
        $data = request()->validate([
            'resolution' => 'required|in:approved,rejected',
            'admin_notes' => 'nullable|string|max:2000',
        ]);

        $rma = $this->rmaRepository->find($rmaId);

        if (! $rma) {
            session()->flash('error', 'RMA no encontrada.');
            return redirect()->route('admin.sales.rma.index');
        }

        $dispute = RmaDispute::where('rma_id', $rmaId)
            ->whereNull('admin_resolution')
            ->latest()
            ->first();

        if (! $dispute) {
            session()->flash('error', 'No hay disputa pendiente para esta RMA.');
            return redirect()->route('admin.sales.rma.view', $rmaId);
        }

        // Actualizar disputa
        $dispute->update([
            'admin_resolution' => $data['resolution'],
            'admin_notes'      => $data['admin_notes'] ?? null,
            'resolved_at'      => Carbon::now(),
        ]);

        // Cambiar estado del RMA según resolución
        // approved = admin da la razón al vendedor → Declined
        // rejected = admin da la razón al cliente → Accept (sigue la devolución)
        $newStatus = $data['resolution'] === 'approved' ? 'Declined' : 'Accept';
        $this->rmaRepository->update(['rma_status' => $newStatus], $rmaId);

        // Notificar a seller y cliente
        $this->sendDisputeResolvedEmails($rmaId, $rma->order_id, $data['resolution'], $data['admin_notes'] ?? null, $newStatus);

        $msg = $data['resolution'] === 'approved'
            ? 'Disputa aprobada. La RMA fue marcada como Rechazada.'
            : 'Disputa rechazada. La RMA continúa como Aceptada.';

        session()->flash('success', $msg);
        return redirect()->route('admin.sales.rma.view', $rmaId);
    }

    // -------------------------------------------------------------------------

    private function sendDisputeResolvedEmails(int $rmaId, int $orderId, string $resolution, ?string $adminNotes, string $newStatus): void
    {
        $order = DB::table('orders')->where('id', $orderId)->first();

        // Claves de traducción según resolución
        $sellerSubjectKey   = $resolution === 'approved'
            ? 'goloba-rma::app.mail.dispute-resolved.subject-seller-approved'
            : 'goloba-rma::app.mail.dispute-resolved.subject-seller-rejected';
        $sellerBodyKey      = $resolution === 'approved'
            ? 'goloba-rma::app.mail.dispute-resolved.body-seller-approved'
            : 'goloba-rma::app.mail.dispute-resolved.body-seller-rejected';
        $customerSubjectKey = $resolution === 'approved'
            ? 'goloba-rma::app.mail.dispute-resolved.subject-customer-approved'
            : 'goloba-rma::app.mail.dispute-resolved.subject-customer-rejected';
        $customerBodyKey    = $resolution === 'approved'
            ? 'goloba-rma::app.mail.dispute-resolved.body-customer-approved'
            : 'goloba-rma::app.mail.dispute-resolved.body-customer-rejected';

        $baseData = [
            'rma_id'      => $rmaId,
            'order_id'    => $orderId,
            'rma_status'  => $newStatus,
            'admin_notes' => $adminNotes,
            'view_url'    => '',
        ];

        // Correo al seller
        $sellerData = RmaMailHelper::getSellerData($orderId);
        if ($sellerData) {
            try {
                RmaMailHelper::queueMail(new DisputeResolved(array_merge($baseData, [
                    'email'       => $sellerData['seller_email'],
                    'name'        => $sellerData['seller_name'],
                    'subject_key' => $sellerSubjectKey,
                    'body'        => trans($sellerBodyKey),
                    'view_url'    => url('/seller/rma/' . $rmaId),
                ])));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('[GolobaRMA] Error enviando correo disputa resuelta al seller: ' . $e->getMessage());
            }
        }

        // Correo al cliente
        if ($order) {
            try {
                $customerName = trim(($order->customer_first_name ?? '') . ' ' . ($order->customer_last_name ?? '')) ?: 'Cliente';
                RmaMailHelper::queueMail(new DisputeResolved(array_merge($baseData, [
                    'email'       => $order->customer_email,
                    'name'        => $customerName,
                    'subject_key' => $customerSubjectKey,
                    'body'        => trans($customerBodyKey),
                    'view_url'    => route('rma.customer.view', $rmaId),
                ])));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('[GolobaRMA] Error enviando correo disputa resuelta al cliente: ' . $e->getMessage());
            }
        }
    }
}
