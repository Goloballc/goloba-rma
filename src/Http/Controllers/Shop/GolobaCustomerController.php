<?php

namespace Goloba\GolobaRMA\Http\Controllers\Shop;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Event;
use Goloba\GolobaRMA\Mail\NewRequestCustomer;
use Goloba\GolobaRMA\Mail\NewRequestSeller;
use Goloba\GolobaRMA\Mail\RmaMailHelper;
use Goloba\GolobaRMA\Services\RetractoService;
use Goloba\ServientregaTracking\Services\ServientregaTrackingService;
use Webkul\RMA\Http\Controllers\Customer\CustomerController;
use Webkul\RMA\Mail\CustomerRmaCreationEmail;

/**
 * Extiende CustomerController del paquete RMA de Bagisto para:
 *
 *  1. Validar que el pedido esté entregado antes de crear cualquier RMA.
 *  2. Determinar si aplica Derecho de Retracto y redirigir al formulario correcto.
 *  3. Guardar el tipo de RMA y la fecha límite de retracto al crear.
 */
class GolobaCustomerController extends CustomerController
{
    public function __construct(
        protected ServientregaTrackingService $trackingService,
        protected RetractoService             $retractoService,
        \Webkul\RMA\Repositories\CreateRmaRepository $createRmaRepository,
        \Webkul\Sales\Repositories\InvoiceItemRepository $invoiceItemRepository,
        \Webkul\Sales\Repositories\OrderItemRepository $orderItemRepository,
        \Webkul\Sales\Repositories\OrderRepository $orderRepository,
        \Webkul\Product\Repositories\ProductImageRepository $productImageRepository,
        \Webkul\Product\Repositories\ProductRepository $productRepository,
        \Webkul\RMA\Repositories\ReasonResolutionsRepository $reasonResolutionsRepository,
        \Webkul\RMA\Repositories\RMAAdditionalFieldRepository $rmaAdditionalFieldRepository,
        \Webkul\RMA\Repositories\RmaCustomFieldRepository $rmaCustomFieldRepository,
        \Webkul\RMA\Helpers\Helper $rmaHelper,
        \Webkul\RMA\Repositories\RMAImagesRepository $rmaImagesRepository,
        \Webkul\RMA\Repositories\RMAItemsRepository $rmaItemsRepository,
        \Webkul\RMA\Repositories\RMAMessagesRepository $rmaMessagesRepository,
        \Webkul\RMA\Repositories\RMAReasonsRepository $rmaReasonRepository,
        \Webkul\RMA\Repositories\RMARepository $rmaRepository,
        \Webkul\Sales\Repositories\ShipmentItemRepository $shipmentItemRepository,
    ) {
        parent::__construct(
            $createRmaRepository, $invoiceItemRepository, $orderItemRepository,
            $orderRepository, $productImageRepository, $productRepository,
            $reasonResolutionsRepository, $rmaAdditionalFieldRepository,
            $rmaCustomFieldRepository, $rmaHelper, $rmaImagesRepository,
            $rmaItemsRepository, $rmaMessagesRepository, $rmaReasonRepository,
            $rmaRepository, $shipmentItemRepository,
        );
    }

    // =========================================================================
    // RESOLUCIÓN DE FORMULARIO
    // =========================================================================

    /**
     * Verifica si una orden aplica para Derecho de Retracto.
     * Llamado por AJAX desde el modal de creación de RMA.
     *
     * GET /customer/account/rma/check-retracto?order_id={id}
     *
     * Respuesta JSON:
     *   { applies: false }
     *   { applies: true, remainingDays: 3, expiresAt: "viernes 28 de marzo de 2026", hasConditional: false }
     */
    public function checkRetracto(): JsonResponse
    {
        $orderId = (int) request()->query('order_id');

        if (!$orderId) {
            return new JsonResponse(['applies' => false]);
        }

        $deliveryDate = $this->getDeliveryDate($orderId);

        $categoryIds = $this->getOrderCategoryIds($orderId);
        $eligibility = $this->retractoService->checkCategories($categoryIds);

        if ($deliveryDate === null || !$this->retractoService->isWithinWindow($deliveryDate)) {
            return new JsonResponse(['applies' => false, 'hasConditional' => $eligibility['has_conditional']]);
        }

        if (!$eligibility['eligible']) {
            return new JsonResponse(['applies' => false, 'hasConditional' => $eligibility['has_conditional']]);
        }

        $remaining = $this->retractoService->remainingBusinessDays($deliveryDate);
        $expiresAt = $this->retractoService->calculateExpiresAt($deliveryDate);

        return new JsonResponse([
            'applies'        => true,
            'remainingDays'  => $remaining,
            'expiresAt'      => $expiresAt->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY'),
            'hasConditional' => $eligibility['has_conditional'],
        ]);
    }

    // =========================================================================
    // VALIDACIÓN DE ENTREGA
    // =========================================================================

    /**
     * Bloquea la creación de RMA si el pedido no está entregado.
     * Retorna null si la validación pasa, o un JsonResponse de error si falla.
     */
    private function validarEntregaConfirmada(int $orderId): ?JsonResponse
    {
        $deliveryDate = $this->getDeliveryDate($orderId);

        if ($deliveryDate === null) {
            $shipment = $this->getShipment($orderId);

            if (!$shipment) {
                return new JsonResponse([
                    'messages' => 'No es posible abrir una solicitud de devolución porque el envío de tu pedido aún no ha sido registrado. Por favor intenta más tarde.',
                ], 422);
            }

            $estado = $this->trackingService->estadoGuia($shipment->track_number);

            if (!$estado) {
                return new JsonResponse([
                    'messages' => 'No es posible abrir una solicitud de devolución porque aún no tenemos confirmación de entrega para tu pedido. Por favor intenta más tarde.',
                ], 422);
            }

            return new JsonResponse([
                'messages' => 'No es posible abrir una solicitud de devolución porque tu pedido aún no ha sido entregado. Estado actual: ' . $estado->estadoEnvio . '.',
            ], 422);
        }

        return null;
    }

    // =========================================================================
    // OVERRIDES
    // =========================================================================

    /**
     * {@inheritdoc}
     *
     * Agrega validación de entrega y determina el tipo de RMA antes de persistir.
     */
    public function store(): JsonResponse|RedirectResponse
    {
        $orderId = (int) request()->input('order_id');

        $error = $this->validarEntregaConfirmada($orderId);
        if ($error !== null) {
            return $error;
        }

        // Cambio 1: imagen obligatoria
        if (! request()->hasFile('images') || empty(array_filter(request()->file('images') ?? []))) {
            return new JsonResponse(['messages' => 'Debes adjuntar al menos una imagen de los productos para continuar.'], 422);
        }

        // Cambio 2: aceptación de términos y condiciones obligatoria
        if (! request()->input('agreement')) {
            return new JsonResponse(['messages' => 'Debes aceptar los Términos y Condiciones para continuar.'], 422);
        }

        // Calcular datos de retracto ANTES de llamar al padre
        $retractoData = $this->buildRetractoData($orderId);

        $this->normalizeResolutionType();

        if (! request()->has('order_status')) {
            request()->merge(['order_status' => '1']);
        }

        $response = $this->callParentStoreSuppressingVendorMail('store');

        // Solo actualizar si el padre persistió el RMA exitosamente
        if ($response instanceof JsonResponse && $response->getStatusCode() === 200) {
            $this->persistRetractoData($orderId, $retractoData);
            $this->sendNewRequestEmails($orderId, $retractoData['rma_type']);
        }

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function storeGuest(): JsonResponse|RedirectResponse
    {
        $orderId = (int) request()->input('order_id');

        $error = $this->validarEntregaConfirmada($orderId);
        if ($error !== null) {
            return $error;
        }

        // Cambio 1: imagen obligatoria
        if (! request()->hasFile('images') || empty(array_filter(request()->file('images') ?? []))) {
            return new JsonResponse(['messages' => 'Debes adjuntar al menos una imagen de los productos para continuar.'], 422);
        }

        // Cambio 2: aceptación de términos y condiciones obligatoria
        if (! request()->input('agreement')) {
            return new JsonResponse(['messages' => 'Debes aceptar los Términos y Condiciones para continuar.'], 422);
        }

        $retractoData = $this->buildRetractoData($orderId);

        $this->normalizeResolutionType();

        if (! request()->has('order_status')) {
            request()->merge(['order_status' => '1']);
        }

        $response = $this->callParentStoreSuppressingVendorMail('storeGuest');

        if ($response instanceof JsonResponse && $response->getStatusCode() === 200) {
            $this->persistRetractoData($orderId, $retractoData);
            $this->sendNewRequestEmails($orderId, $retractoData['rma_type']);
        }

        return $response;
    }

    // =========================================================================
    // VISTA DE DETALLE
    // =========================================================================

    /**
     * {@inheritdoc}
     *
     * Extiende la vista de detalle del vendor para pasar $dispute a la vista,
     * evitando queries en el Blade template.
     */
    public function view(int $id): \Illuminate\View\View|\Illuminate\Http\RedirectResponse
    {
        $customer = auth()->guard('customer')->user();
        $isGuest  = empty($customer) ? 1 : 0;

        $rmaData = $this->rmaRepository->with(['orderItem', 'order'])->findOneWhere(['id' => $id]);

        if (! $rmaData) {
            return redirect()->route('shop.customer.session.index');
        }

        $order = $this->orderRepository->where([
            'id'       => $rmaData['order_id'],
            'is_guest' => $isGuest,
            $customer ? 'customer_id' : 'customer_email' => $customer?->id ?? session()->get('guestEmail'),
        ])->first();

        if (empty($order)) {
            return redirect()->route(empty($customer) ? 'shop.customer.session.index' : 'rma.customers.all-rma');
        }

        $rmaImages            = $this->rmaImagesRepository->findWhere(['rma_id' => $id]);
        $rmaAdditionalValues  = $this->rmaAdditionalFieldRepository->findWhere(['rma_id' => $id]);
        $rmaAdditionalFieldValues = [];

        foreach ($rmaAdditionalValues as $value) {
            $rmaCustomField = $this->rmaCustomFieldRepository->findOneWhere(['code' => $value->field_name]);
            if ($rmaCustomField) {
                $rmaAdditionalFieldValues[$value->field_value] = $rmaCustomField['label'];
            }
        }

        $reasons        = $this->rmaItemsRepository->with('getReasons')->findWhere(['rma_id' => $id]);
        $productDetails = $this->rmaItemsRepository->findWhere(['rma_id' => $id]);
        $rmaItems       = $this->rmaItemsRepository->findWhere(['rma_id' => $rmaData['id']]);

        $skus = [];
        foreach ($order->items as $item) {
            if ($item['type'] === 'configurable') {
                $skus[] = $item['child'];
            }
            $skus[] = $item['sku'];
        }

        $customerFirstName = $order->customer_first_name;
        $customerLastName  = $order->customer_last_name;

        // Disputa — se pasa null si no existe, la vista solo hace @if ($dispute)
        $dispute = \Goloba\GolobaRMA\Models\RmaDispute::with('images')
            ->where('rma_id', $id)
            ->first();

        return view(empty($customer) ? 'rma::shop.guest.view' : 'rma::shop.customer.rma.view', compact(
            'skus',
            'rmaData',
            'reasons',
            'isGuest',
            'customer',
            'rmaImages',
            'productDetails',
            'customerLastName',
            'customerFirstName',
            'rmaAdditionalFieldValues',
            'dispute',
        ));
    }

    // =========================================================================
    // HELPERS INTERNOS
    // =========================================================================

    /**
     * Llama a parent::store() o parent::storeGuest() suprimiendo el correo
     * del vendor (CustomerRmaCreationEmail) sin interferir con el mailer real.
     *
     * Estrategia: escuchamos MessageSending e identificamos el correo del vendor
     * por su subject hardcodeado ("New RMA Request"). Devolver false desde el
     * listener cancela el envío en Laravel 10.
     */
    private function callParentStoreSuppressingVendorMail(string $method): mixed
    {
        Event::listen(
            \Illuminate\Mail\Events\MessageSending::class,
            static function (\Illuminate\Mail\Events\MessageSending $event): bool|null {
                if ($event->message->getSubject() === 'New RMA Request') {
                    return false; // cancela el envío
                }
                return null;
            }
        );

        try {
            return parent::$method();
        } finally {
            Event::forget(\Illuminate\Mail\Events\MessageSending::class);
        }
    }

    /**
     * Envía correos de nueva solicitud al cliente y al seller.
     * Se llama sólo cuando parent::store() respondió HTTP 200.
     */
    private function sendNewRequestEmails(int $orderId, string $rmaType): void
    {
        $rma = \DB::table('rma')->where('order_id', $orderId)->orderByDesc('id')->first();
        if (! $rma) {
            return;
        }

        $order      = $this->orderRepository->find($orderId);
        $items      = $this->rmaItemsRepository->findWhere(['rma_id' => $rma->id]);
        $orderItems = $this->orderItemRepository->findWhereIn('id', $items->pluck('order_item_id')->toArray());

        $rmaData = [
            'rma_id'          => $rma->id,
            'order_id'        => $orderId,
            'rma_type'        => $rmaType,
            'order_items'     => $orderItems,
            'rma_qty'         => $items->pluck('quantity')->toArray(),
            'resolution_type' => $items->pluck('resolution')->toArray(),
            'reason'          => $items->map(fn($i) => optional(\Webkul\RMA\Models\RMAReasons::find($i->rma_reason_id))->title ?? '')->toArray(),
        ];

        // Correo al cliente
        $customer = auth()->guard('customer')->user();
        $email    = $customer?->email ?? session()->get('guestEmail', $order?->customer_email);
        $name     = $customer?->name  ?? ($order ? $order->customer_first_name . ' ' . $order->customer_last_name : 'Cliente');

        if ($email) {
            RmaMailHelper::queueMail(new NewRequestCustomer(array_merge($rmaData, [
                'email' => $email,
                'name'  => $name,
            ])));
        }

        // Correo al seller
        $sellerData = RmaMailHelper::getSellerData($orderId);
        if ($sellerData) {
            RmaMailHelper::queueMail(new NewRequestSeller(array_merge($rmaData, $sellerData)));
        }
    }

    /**
     * El modal envía order_item_id, rma_qty, rma_reason_id y resolution_type
     * como arrays indexados por product_id (ej: rma_qty[547] = 1).
     * El vendor itera con `foreach ($items as $key => $itemId)` asumiendo que
     * todos los arrays comparten el mismo $key secuencial (0, 1, 2...).
     * Este método reindexea todos esos arrays para que el vendor los acepte.
     */
    private function normalizeResolutionType(): void
    {
        $fields = ['order_item_id', 'rma_qty', 'rma_reason_id', 'resolution_type'];
        $merge  = [];

        foreach ($fields as $field) {
            $raw = request()->input($field);
            if (is_array($raw) && array_keys($raw) !== range(0, count($raw) - 1)) {
                $merge[$field] = array_values($raw);
            }
        }

        if (! empty($merge)) {
            request()->merge($merge);
        }
    }

    /**
     * Obtiene la fecha de entrega del microservicio para la guía de una orden.
     * Retorna null si no hay guía registrada, si el microservicio no responde,
     * o si el pedido aún no fue entregado.
     */
    private function getDeliveryDate(int $orderId): ?string
    {
        $shipment = $this->getShipment($orderId);

        if (!$shipment) {
            return null;
        }

        return $this->trackingService->fechaEntrega($shipment->track_number);
    }

    /**
     * Obtiene el shipment más reciente con número de guía para una orden.
     */
    private function getShipment(int $orderId): ?object
    {
        return \DB::table('shipments')
            ->where('order_id', $orderId)
            ->whereNotNull('track_number')
            ->where('track_number', '!=', '')
            ->orderByDesc('id')
            ->first();
    }

    /**
     * Obtiene los IDs de categoría de todos los productos de una orden.
     *
     * @return int[]
     */
    private function getOrderCategoryIds(int $orderId): array
    {
        return \DB::table('order_items')
            ->join('product_categories', 'product_categories.product_id', '=', 'order_items.product_id')
            ->where('order_items.order_id', $orderId)
            ->pluck('product_categories.category_id')
            ->unique()
            ->map(fn($id) => (int) $id)
            ->values()
            ->toArray();
    }

    /**
     * Calcula si aplica retracto para una orden y retorna los datos a persistir.
     * Se llama ANTES de parent::store() para tener los datos listos.
     *
     * @return array{ rma_type: string, retracto_expires_at: string|null, retracto_seal_intact: bool|null }
     */
    private function buildRetractoData(int $orderId): array
    {
        $deliveryDate = $this->getDeliveryDate($orderId);

        if ($deliveryDate && $this->retractoService->isWithinWindow($deliveryDate)) {
            $categoryIds = $this->getOrderCategoryIds($orderId);
            $eligibility = $this->retractoService->checkCategories($categoryIds);

            if ($eligibility['eligible']) {
                $expiresAt  = $this->retractoService->calculateExpiresAt($deliveryDate);
                $sealIntact = request()->boolean('retracto_seal_intact');

                return [
                    'rma_type'             => 'retracto',
                    'retracto_expires_at'  => $expiresAt->toDateTimeString(),
                    'retracto_seal_intact' => $eligibility['has_conditional'] ? $sealIntact : null,
                ];
            }
        }

        return [
            'rma_type'             => 'standard',
            'retracto_expires_at'  => null,
            'retracto_seal_intact' => null,
        ];
    }

    /**
     * Actualiza la fila de RMA más reciente de la orden con los datos de retracto.
     * El padre crea el RMA pero no persiste nuestros campos custom, así que
     * hacemos un UPDATE inmediatamente después de su store().
     */
    private function persistRetractoData(int $orderId, array $retractoData): void
    {
        $rma = \DB::table('rma')
            ->where('order_id', $orderId)
            ->orderByDesc('id')
            ->first();

        if (!$rma) {
            return;
        }

        \DB::table('rma')
            ->where('id', $rma->id)
            ->update([
                'rma_type'             => $retractoData['rma_type'],
                'retracto_expires_at'  => $retractoData['retracto_expires_at'],
                'retracto_seal_intact' => $retractoData['retracto_seal_intact'],
            ]);
    }
}
