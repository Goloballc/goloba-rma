<?php

namespace Goloba\GolobaRMA\Http\Controllers\Admin;

use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\RMA\Repositories\RMARepository;
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

        $msg = $data['resolution'] === 'approved'
            ? 'Disputa aprobada. La RMA fue marcada como Rechazada.'
            : 'Disputa rechazada. La RMA continúa como Aceptada.';

        session()->flash('success', $msg);
        return redirect()->route('admin.sales.rma.view', $rmaId);
    }
}
