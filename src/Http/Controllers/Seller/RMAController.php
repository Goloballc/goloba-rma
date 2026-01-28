<?php

namespace Goloba\GolobaRMA\Http\Controllers\Seller;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Event;
use Illuminate\View\View;
use Webkul\Marketplace\Http\Controllers\Shop\Controller;
use Webkul\Sales\Repositories\{OrderRepository, RefundRepository};
use Webkul\Sales\Repositories\OrderItemRepository;
use Goloba\GolobaRMA\DataGrids\Seller\SellerRmaDataGrid;
use Webkul\RMA\Mail\CustomerRMAStatusEmail;
use Webkul\RMA\Repositories\{
    RMAAdditionalFieldRepository,
    RMAImagesRepository,
    RMAMessagesRepository,
    RMARepository
};
use Webkul\RMA\Repositories\RMAItemsRepository;
use Webkul\RMA\Repositories\RMAStatusRepository;
use Goloba\GolobaRMA\Models\RMA;
use Carbon\Carbon;

class RMAController extends Controller
{
    public const ACCEPT = 'Accept';
    public const DECLINED = 'Declined';
    public const PENDING = 'Pending';
    public const ACTIVE = 1;
    public const ITEMCANCELED = 'Item Canceled';
    public const RECEIVEDPACKAGE = 'Received Package';
    public const CANCELED = 'canceled';
    public const ORDERCANCELED = '1';

    public function __construct(
        protected OrderItemRepository $orderItemRepository,
        protected OrderRepository $orderRepository,
        protected RMAAdditionalFieldRepository $rmaAdditionalFieldRepository,
        protected RMAImagesRepository $rmaImagesRepository,
        protected RMAItemsRepository $rmaItemsRepository,
        protected RMAMessagesRepository $rmaMessagesRepository,
        protected RMARepository $rmaRepository,
        protected RMAStatusRepository $rmaStatusRepository,
        protected RefundRepository $refundRepository,
    ) {
    }

    public function index(): View|JsonResponse
    {
        if (request()->ajax()) {
            return datagrid(SellerRmaDataGrid::class)->process();
        }
        return view('goloba-rma::seller.rma.index');
    }

    public function view(int $rmaId): View|RedirectResponse
    {
        $sellerId = auth()->guard('seller')->user()->id;
        $rma = $this->rmaRepository->with(['orderItem', 'order'])->find($rmaId);
        
        if (!$rma) {
            session()->flash('error', 'RMA no encontrada');
            return redirect()->route('goloba.seller.rma.index');
        }

        $belongsToSeller = RMA::find($rmaId)->belongsToSeller($sellerId);
        if (!$belongsToSeller) {
            session()->flash('error', 'No tienes permiso para ver esta RMA');
            return redirect()->route('goloba.seller.rma.index');
        }

        $rmaActiveStatus = $this->rmaStatusRepository->where('status', 1)->pluck('title');
        $rmaAdditionalValues = $this->rmaAdditionalFieldRepository->findWhere(['rma_id' => $rmaId]);
        $viewData = $this->rmaRepository->sendDataToView($rmaId, $rma, $rma, $rmaActiveStatus, $rmaAdditionalValues);
        
        $rmaItemDetails = [];
        $rmaItems = \DB::table('rma_items')
            ->where('rma_items.rma_id', $rmaId)
            ->join('order_items', 'order_items.id', '=', 'rma_items.order_item_id')
            ->leftJoin('rma_reasons', 'rma_reasons.id', '=', 'rma_items.rma_reason_id')
            ->select('rma_items.quantity', 'rma_items.resolution', 'rma_reasons.title as reason', 'order_items.name as product_name', 'order_items.sku', 'order_items.product_id')
            ->get();
        
        foreach ($rmaItems as $item) {
            $productImage = null;
            if ($item->product_id) {
                $product = \DB::table('products')->where('id', $item->product_id)->first();
                if ($product) {
                    $image = \DB::table('product_images')->where('product_id', $product->id)->orderBy('position')->first();
                    if ($image) $productImage = $image->path;
                }
            }
            $rmaItemDetails[] = [
                'product_name' => $item->product_name,
                'sku' => $item->sku,
                'quantity' => $item->quantity,
                'product_image' => $productImage,
                'resolution' => $item->resolution ?? 'N/A',
                'reason' => $item->reason ?? 'Sin especificar',
            ];
        }
        $viewData['rmaItemDetails'] = $rmaItemDetails;
        return view('goloba-rma::seller.rma.view', $viewData);
    }

    public function getMessages(): JsonResponse
    {
        $sellerId = auth()->guard('seller')->user()->id;
        $rmaId = request()->get('rma_id');
        $rma = RMA::find($rmaId);
        if (!$rma || !$rma->belongsToSeller($sellerId)) {
            return response()->json(['error' => 'No autorizado'], 403);
        }
        $messages = $this->rmaMessagesRepository->where('rma_id', $rmaId)->orderBy('id', 'asc')->get();
        return response()->json(['messages' => $messages]);
    }

    public function sendMessage(): JsonResponse
    {
        $sellerId = auth()->guard('seller')->user()->id;
        $data = request()->validate(['rma_id' => 'required|integer', 'message' => 'required|string', 'file' => 'nullable|file|max:10240']);
        $rma = RMA::find($data['rma_id']);
        if (!$rma || !$rma->belongsToSeller($sellerId)) {
            return response()->json(['error' => 'No autorizado'], 403);
        }
        
        $messageData = [
            'rma_id' => $data['rma_id'], 
            'message' => $data['message'], 
            'is_admin' => 0, 
            'is_seller' => 1
        ];
        
        // DEBUG: Log para verificar qué se está intentando guardar
        \Log::info('Intentando guardar mensaje RMA:', $messageData);
        
        $storedMessage = $this->rmaMessagesRepository->create($messageData);
        
        // DEBUG: Log para verificar qué se guardó realmente
        \Log::info('Mensaje guardado:', $storedMessage->toArray());
        
        if (request()->hasFile('file')) {
            $file = request()->file('file');
            $filename = $file->getClientOriginalName();
            $path = $file->storeAs('rma-conversation/' . $storedMessage->id, $filename);
            $this->rmaMessagesRepository->update(['attachment_path' => $path, 'attachment' => $filename], $storedMessage->id);
        }
        return response()->json(['success' => true, 'message' => $storedMessage]);
    }
    
    public function changeStatus(): RedirectResponse
    {
        $data = request()->only(['rma_id', 'rma_status', 'message']);
        $sellerId = auth()->guard('seller')->user()->id;
        $rma = $this->rmaRepository->find($data['rma_id']);
        if (!$rma) {
            session()->flash('error', 'RMA no encontrada');
            return redirect()->route('goloba.seller.rma.index');
        }
        $belongsToSeller = RMA::find($data['rma_id'])->belongsToSeller($sellerId);
        if (!$belongsToSeller) {
            session()->flash('error', 'No tienes permiso para modificar esta RMA');
            return redirect()->route('goloba.seller.rma.index');
        }
        $this->rmaRepository->update(['rma_status' => $data['rma_status']], $data['rma_id']);
        if (!empty($data['message'])) {
            $this->rmaMessagesRepository->create(['message' => $data['message'], 'rma_id' => $data['rma_id'], 'is_admin' => 0, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        }
        $order = $this->orderRepository->find($rma->order_id);
        $mailDetails = ['name' => $order->customer_first_name . ' ' . $order->customer_last_name, 'email' => $order->customer_email, 'rma_id' => $data['rma_id'], 'rma_status' => $data['rma_status']];
        try {
            Mail::queue(new CustomerRMAStatusEmail($mailDetails));
        } catch (\Exception $e) {
        }
        $statusText = $data['rma_status'] == self::ACCEPT ? 'aceptada' : 'rechazada';
        session()->flash('success', "RMA {$statusText} exitosamente");
        return redirect()->route('goloba.seller.rma.view', $data['rma_id']);
    }

    /**
     * Guardar estado del RMA (Awaiting, Dispatched Package, Received Package, etc.)
     * Método copiado del AdminController para que el vendedor también pueda cambiar estados
     */
    public function saveRmaStatus(): RedirectResponse
    {
        $sellerId = auth()->guard('seller')->user()->id;
        $status = request()->input();

        $rma = $this->rmaRepository->find($status['rma_id']);
        
        // Validar que el RMA pertenece al vendedor
        if (!$rma || !$rma->belongsToSeller($sellerId)) {
            session()->flash('error', 'No tienes permiso para modificar esta RMA');
            return redirect()->route('goloba.seller.rma.index');
        }
        
        $orderId = $rma->order_id;
        $order = $this->orderRepository->find($orderId);
        
        $mailDetails = [
            'name'       => $order->customer_first_name . ' ' . $order->customer_last_name,
            'email'      => $order->customer_email,
            'rma_id'     => $status['rma_id'],
            'rma_status' => $status['rma_status'],
        ];

        $ordersRma = $this->rmaRepository->findWhere(['order_id' => $orderId]);
        $totalCount = (int)$this->rmaItemsRepository->whereIn('rma_id', $ordersRma->pluck('id'))->sum('quantity');

        if ($totalCount > 0) {
            $qtyCanceled = ($status['rma_status'] == self::ITEMCANCELED) ? 1 : 0;

            foreach ($ordersRma as $orderRma) {
                $rmaItems = $this->rmaItemsRepository->findWhere(['rma_id' => $orderRma->id]);
                
                foreach ($rmaItems as $key => $rmaItem) {
                    $item1 = $this->orderItemRepository->find($rmaItem->order_item_id);

                    if ($item1->parent_id != null) {
                        $parentItem = $this->orderItemRepository->find($item1->parent_id);
                        $parentItem->update([
                            'qty_canceled' => $parentItem->qty_canceled + ($qtyCanceled == 1 ? $rmaItem->quantity : 0),
                        ]);
                    } else {
                        $item1->update([
                            'qty_canceled' => $item1->qty_canceled + ($qtyCanceled == 1 ? $rmaItem->quantity : 0),
                        ]);
                    }
                }
            }

            if ($qtyCanceled == 1) {
                $this->updateOrderStatus($order);
                Event::dispatch('sales.order.cancel.after', $order);
            }

            // Cuando el paquete es recibido, crear el reembolso automáticamente
            if ($status['rma_status'] == self::RECEIVEDPACKAGE) {
                $items = collect($orderRma->orderItem)->pluck('order_item_id', 'quantity')->mapWithKeys(function ($item, $quantity) {
                    return [$item => $quantity];
                });

                $refundArray = [
                    'refund' => [
                        'shipping'          => 0,
                        'adjustment_refund' => 0,
                        'adjustment_fee'    => 0,
                    ],
                ];

                foreach ($items as $key => $value) {
                    $refundArray['refund']['items'][$key] = $value;
                }

                $order = $this->orderRepository->findOrFail($orderId);

                if (! $order->canRefund()) {
                    session()->flash('error', trans('rma::app.response.creation-error'));
                    return redirect()->back();
                }

                $totals = $this->refundRepository->getOrderItemsRefundSummary($refundArray['refund'], $orderId);

                if (! $totals) {
                    session()->flash('error', trans('admin::app.sales.refunds.create.invalid-qty'));
                    return redirect()->back();
                }

                $maxRefundAmount = $totals['grand_total']['price'] - $order->refunds()->sum('base_adjustment_refund');
                $refundAmount = $totals['grand_total']['price'] - $totals['shipping']['price'] + $refundArray['refund']['shipping'] + $refundArray['refund']['adjustment_refund'] - $refundArray['refund']['adjustment_fee'];

                if (! $refundAmount) {
                    session()->flash('error', trans('admin::app.sales.refunds.create.invalid-refund-amount-error'));
                    return redirect()->back();
                }

                if ($refundAmount > $maxRefundAmount) {
                    session()->flash('error', trans('admin::app.sales.refunds.create.refund-limit-error', ['amount' => core()->formatBasePrice($maxRefundAmount)]));
                    return redirect()->back();
                }

                // Crear el reembolso
                $this->refundRepository->create(array_merge($refundArray, ['order_id' => $orderId]));
                $updateStatus = $rma->update($status);

                session()->flash('success', trans('admin::app.sales.refunds.create.create-success'));
                return redirect()->route('goloba.seller.rma.view', $status['rma_id']);
            }

            if ($order->total_qty_ordered == $totalCount) {
                if ($status['rma_status'] == self::ITEMCANCELED) {
                    $status['order_status'] = self::ORDERCANCELED;
                    $order->update(['status' => self::CANCELED]);
                } elseif ($status['rma_status'] == self::ACCEPT) {
                    $this->rmaRepository->find($status['rma_id'])->update(['status' => 0]);
                }
            }
        }

        $updateStatus = $rma->update($status);

        // Crear mensaje automático en el chat del RMA
        $requestData = [
            'message'    => trans('rma::app.mail.status.your-rma-id') .' '. trans('rma::app.mail.status.status-change', ['id' => $status['rma_id']]) .'. '. trans('rma::app.mail.status.status') . ' : ' . $rma['rma_status'],
            'rma_id'     => $status['rma_id'],
            'is_admin'   => 0,
            'is_seller'  => 1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];

        $this->rmaMessagesRepository->create($requestData);

        if ($updateStatus) {
            try {
                Mail::queue(new CustomerRMAStatusEmail($mailDetails));
                session()->flash('success', trans('rma::app.admin.sales.rma.all-rma.view.update-success'));
            } catch (\Exception $e) {
                session()->flash('success', trans('rma::app.admin.sales.rma.all-rma.view.update-success'));
            }

            return redirect()->back();
        }

        session()->flash('error', trans('shop::app.customer.signup-form.failed'));
        return redirect()->back();
    }

    /**
     * Actualizar estado de la orden
     * Método copiado del AdminController
     */
    protected function updateOrderStatus(\Webkul\Sales\Contracts\Order $order, string $orderState = null): void
    {
        Event::dispatch('sales.order.update-status.before', $order);

        if (! empty($orderState)) {
            $status = $orderState;
        } else {
            if ($this->orderRepository->isInCompletedState($order)) {
                $status = 'completed';
            }

            if ($this->orderRepository->isInCanceledState($order)) {
                $status = 'canceled';
            } elseif ($this->orderRepository->isInClosedState($order)) {
                $status = 'closed';
            }
        }

        if (! empty($status)) {
            $order->status = $status;
        }

        $order->save();

        Event::dispatch('sales.order.update-status.after', $order);
    }
}
