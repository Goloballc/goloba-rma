<?php

namespace Goloba\GolobaRMA\Http\Controllers\Seller;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
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
}
