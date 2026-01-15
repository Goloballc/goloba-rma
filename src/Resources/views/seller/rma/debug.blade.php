@php
    $sellerId = auth()->guard('seller')->user()->id;
    $sellerEmail = auth()->guard('seller')->user()->email;
    
    // Ver todas las RMAs sin filtrar
    $allRmas = DB::table('rma')->get();
    
    // Ver relación completa
    $rmaRelations = DB::table('rma')
        ->leftJoin('rma_items', 'rma_items.rma_id', '=', 'rma.id')
        ->leftJoin('marketplace_order_items', 'marketplace_order_items.order_item_id', '=', 'rma_items.order_item_id')
        ->leftJoin('marketplace_products', 'marketplace_products.id', '=', 'marketplace_order_items.marketplace_product_id')
        ->select(
            'rma.id as rma_id',
            'rma_items.order_item_id',
            'marketplace_order_items.marketplace_product_id',
            'marketplace_products.marketplace_seller_id'
        )
        ->get();
    
    // Ver todos los vendedores
    $sellers = DB::table('marketplace_sellers')->get();
    
    // Ver productos del vendedor actual
    $myProducts = DB::table('marketplace_products')
        ->where('marketplace_seller_id', $sellerId)
        ->leftJoin('products', 'products.id', '=', 'marketplace_products.product_id')
        ->select('marketplace_products.id', 'marketplace_products.product_id', 'products.sku')
        ->limit(10)
        ->get();
        
    // Ver órdenes con productos del vendedor actual
    $myOrders = DB::table('marketplace_order_items')
        ->leftJoin('marketplace_products', 'marketplace_products.id', '=', 'marketplace_order_items.marketplace_product_id')
        ->leftJoin('order_items', 'order_items.id', '=', 'marketplace_order_items.order_item_id')
        ->where('marketplace_products.marketplace_seller_id', $sellerId)
        ->select('marketplace_order_items.*', 'order_items.order_id')
        ->limit(10)
        ->get();
    
    // Ver específicamente la orden 27
    $order27Items = DB::table('order_items')
        ->where('order_id', 27)
        ->leftJoin('marketplace_order_items', 'marketplace_order_items.order_item_id', '=', 'order_items.id')
        ->leftJoin('marketplace_products', 'marketplace_products.id', '=', 'marketplace_order_items.marketplace_product_id')
        ->select('order_items.id as order_item_id', 'order_items.sku', 'marketplace_order_items.marketplace_product_id', 'marketplace_products.marketplace_seller_id')
        ->get();
@endphp

<x-marketplace::shop.layouts>
    <x-slot:title>
        Debug RMA
    </x-slot>

    <div class="container px-[60px] max-lg:px-8 max-sm:px-4 mt-8">
        <h2 class="text-2xl font-bold mb-4">Debug Información</h2>
        
        <div class="bg-white rounded-lg shadow p-6 mb-4">
            <h3 class="text-lg font-semibold mb-2">Vendedor Actual</h3>
            <p>ID: {{ $sellerId }}</p>
            <p>Email: {{ $sellerEmail }}</p>
        </div>

        <div class="bg-white rounded-lg shadow p-6 mb-4">
            <h3 class="text-lg font-semibold mb-2">Todos los Vendedores</h3>
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left">ID</th>
                        <th class="px-4 py-2 text-left">Shop Title</th>
                        <th class="px-4 py-2 text-left">Email</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sellers as $seller)
                    <tr class="border-b">
                        <td class="px-4 py-2">{{ $seller->id }}</td>
                        <td class="px-4 py-2">{{ $seller->shop_title ?? 'N/A' }}</td>
                        <td class="px-4 py-2">{{ $seller->email ?? 'N/A' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="bg-white rounded-lg shadow p-6 mb-4">
            <h3 class="text-lg font-semibold mb-2">Mis Productos del Marketplace ({{ $myProducts->count() }})</h3>
            @if($myProducts->count() > 0)
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left">MP Product ID</th>
                            <th class="px-4 py-2 text-left">Product ID</th>
                            <th class="px-4 py-2 text-left">SKU</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($myProducts as $product)
                        <tr class="border-b">
                            <td class="px-4 py-2">{{ $product->id }}</td>
                            <td class="px-4 py-2">{{ $product->product_id }}</td>
                            <td class="px-4 py-2">{{ $product->sku ?? 'N/A' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-gray-500">No tienes productos en el marketplace</p>
            @endif
        </div>

        <div class="bg-white rounded-lg shadow p-6 mb-4">
            <h3 class="text-lg font-semibold mb-2">Mis Órdenes ({{ $myOrders->count() }})</h3>
            @if($myOrders->count() > 0)
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left">Order ID</th>
                            <th class="px-4 py-2 text-left">Order Item ID</th>
                            <th class="px-4 py-2 text-left">MP Product ID</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($myOrders as $order)
                        <tr class="border-b">
                            <td class="px-4 py-2">{{ $order->order_id }}</td>
                            <td class="px-4 py-2">{{ $order->order_item_id }}</td>
                            <td class="px-4 py-2">{{ $order->marketplace_product_id }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-gray-500">No tienes órdenes registradas</p>
            @endif
        </div>

        <div class="bg-white rounded-lg shadow p-6 mb-4">
            <h3 class="text-lg font-semibold mb-2">Detalle Orden 27 ({{ $order27Items->count() }} items)</h3>
            @if($order27Items->count() > 0)
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left">Order Item ID</th>
                            <th class="px-4 py-2 text-left">SKU</th>
                            <th class="px-4 py-2 text-left">MP Product ID</th>
                            <th class="px-4 py-2 text-left">Seller ID</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order27Items as $item)
                        <tr class="border-b {{ $item->marketplace_seller_id == $sellerId ? 'bg-green-50' : '' }}">
                            <td class="px-4 py-2">{{ $item->order_item_id }}</td>
                            <td class="px-4 py-2">{{ $item->sku }}</td>
                            <td class="px-4 py-2">{{ $item->marketplace_product_id ?? 'NULL ⚠️' }}</td>
                            <td class="px-4 py-2">
                                {{ $item->marketplace_seller_id ?? 'NULL ⚠️' }}
                                @if($item->marketplace_seller_id == $sellerId)
                                    <span class="text-green-600 font-bold">← TU</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-red-500">⚠️ Orden 27 no encontrada o no tiene items</p>
            @endif
        </div>

        <div class="bg-white rounded-lg shadow p-6 mb-4">
            <h3 class="text-lg font-semibold mb-2">Todas las RMAs ({{ $allRmas->count() }})</h3>
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left">RMA ID</th>
                        <th class="px-4 py-2 text-left">Order ID</th>
                        <th class="px-4 py-2 text-left">Status</th>
                        <th class="px-4 py-2 text-left">Type</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($allRmas as $rma)
                    <tr class="border-b">
                        <td class="px-4 py-2">{{ $rma->id }}</td>
                        <td class="px-4 py-2">{{ $rma->order_id }}</td>
                        <td class="px-4 py-2">{{ $rma->rma_status }}</td>
                        <td class="px-4 py-2">{{ $rma->rma_type ?? 'N/A' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-2">Relaciones RMA → Vendedor</h3>
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left">RMA ID</th>
                        <th class="px-4 py-2 text-left">Order Item ID</th>
                        <th class="px-4 py-2 text-left">MP Product ID</th>
                        <th class="px-4 py-2 text-left">Seller ID</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rmaRelations as $rel)
                    <tr class="border-b {{ $rel->marketplace_seller_id == $sellerId ? 'bg-green-50' : '' }}">
                        <td class="px-4 py-2">{{ $rel->rma_id }}</td>
                        <td class="px-4 py-2">{{ $rel->order_item_id ?? 'NULL' }}</td>
                        <td class="px-4 py-2">{{ $rel->marketplace_product_id ?? 'NULL' }}</td>
                        <td class="px-4 py-2">
                            {{ $rel->marketplace_seller_id ?? 'NULL' }}
                            @if($rel->marketplace_seller_id == $sellerId)
                                <span class="text-green-600 font-bold">← TU</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-marketplace::shop.layouts>
