<x-marketplace::shop.layouts>
    <x-slot:title>
        Mis RMAs
    </x-slot>

    <div class="container px-[60px] max-lg:px-8 max-sm:px-4 mt-8">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-medium">
                {{ __('RMA - Devoluciones y Cambios') }}
            </h2>
        </div>

        {!! view_render_event('marketplace.sellers.account.rma.list.before') !!}

        @php
            $sellerId = auth()->guard('seller')->user()->id;
            
            $rmas = DB::table('rma')
                ->leftJoin('orders', 'orders.id', '=', 'rma.order_id')
                ->leftJoin('rma_items', 'rma_items.rma_id', '=', 'rma.id')
                ->leftJoin('marketplace_order_items', 'marketplace_order_items.order_item_id', '=', 'rma_items.order_item_id')
                ->leftJoin('marketplace_products', 'marketplace_products.id', '=', 'marketplace_order_items.marketplace_product_id')
                ->where('marketplace_products.marketplace_seller_id', $sellerId)
                ->select(
                    'rma.id',
                    'rma.order_id',
                    'rma.rma_type',
                    'rma.rma_status',
                    'rma.created_at',
                    DB::raw('CONCAT(orders.customer_first_name, " ", orders.customer_last_name) as customer_name')
                )
                ->groupBy('rma.id')
                ->orderBy('rma.id', 'desc')
                ->paginate(15);
        @endphp

        <div class="bg-white rounded-lg shadow overflow-hidden">
            @if($rmas->count() > 0)
                <table class="w-full">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                ID
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Orden
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Cliente
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Tipo RMA
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Estado
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Fecha
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Acciones
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($rmas as $rma)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                #{{ $rma->id }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                #{{ $rma->order_id }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $rma->customer_name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if($rma->rma_type === 'retracto')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        Derecho de Retracto
                                    </span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                        Estándar
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @php
                                    $badgeClass = match($rma->rma_status) {
                                        'Pending' => 'bg-yellow-100 text-yellow-800',
                                        'Accept' => 'bg-green-100 text-green-800',
                                        'Declined' => 'bg-red-100 text-red-800',
                                        default => 'bg-gray-100 text-gray-800',
                                    };
                                @endphp
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $badgeClass }}">
                                    {{ $rma->rma_status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ \Carbon\Carbon::parse($rma->created_at)->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="{{ route('goloba.seller.rma.view', $rma->id) }}" 
                                   class="text-blue-600 hover:text-blue-900">
                                    Ver Detalle
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="px-6 py-4 border-t">
                    {{ $rmas->links() }}
                </div>
            @else
                <div class="px-6 py-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No hay RMAs</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Aún no tienes solicitudes de devolución o cambio.
                    </p>
                </div>
            @endif
        </div>

        {!! view_render_event('marketplace.sellers.account.rma.list.after') !!}
    </div>
</x-marketplace::shop.layouts>
