@php
$customAttributes = app('Webkul\RMA\Repositories\RmaCustomFieldRepository')->with('options')->where('status', 1)->get();
@endphp

<x-shop::layouts.account>
    <!-- Title of the page -->
    <x-slot:title>
        @lang('rma::app.shop.customer.title')
    </x-slot>

    <!-- Breadcrumbs -->
    @section('breadcrumbs')
        <x-shop::breadcrumbs name="rma.create"></x-shop::breadcrumbs>
    @endSection

    <div class="mx-4">
        <x-shop::layouts.account.navigation />
    </div>
    <!--Customers logout-->
    <div class="mx-4 flex-auto max-md:mx-6 max-sm:mx-4">
        <!-- Heading of the page -->
        <div class="flex items-center justify-between">
            <h2 class="text-2xl max-lg:text-base font-medium">
                @lang('rma::app.shop.customer.create.heading')
            </h2>

            <a
                href="{{ route('rma.customers.all-rma') }}"
                class="secondary-button flex items-center gap-x-2 border-[#E9E9E9] px-5 max-lg:px-3 max-lg:text-xs py-3 font-normal"
            >
                @lang('shop::app.checkout.onepage.address.back')
            </a>
        </div>

        {!! view_render_event('bagisto.shop.customers.account.new-rma.list.before') !!}
        
        <v-customer-new-rma></v-customer-new-rma>

        {!! view_render_event('bagisto.shop.customers.account.new-rma.list.after') !!}
    </div>

    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="v-customer-new-rma-template"
        >
            <div class="w-full overflow-auto">
                <div class="max-md:hidden">
                    <x-shop::datagrid :src="route('rma.customers.create')" >
                        <!-- Datagrid Header -->
                        <template #header="{
                            isLoading,
                            available,
                            applied,
                            selectAll,
                            sort,
                            performAction
                        }">
                            <template v-if="isLoading">
                                <x-shop::shimmer.datagrid.table.head :isMultiRow="true"/>
                            </template>

                            <template v-else>
                                <div 
                                    class="row grid items-center gap-2.5 border-b border-zinc-200 bg-zinc-100 px-6 py-4 text-sm font-medium text-black max-md:p-4" 
                                    style="grid-template-columns: repeat(6, minmax(0, 1fr));"
                                >
                                    <div
                                        class="flex gap-2.5 items-center select-none"
                                        v-for="(columnGroup, index) in [['increment_id'], ['created_at'], ['grand_total'], ['method_title'], ['status']]"
                                    >
                                        <p class="text-gray-600">
                                            <span class="[&>*]:after:content-['_/_']">
                                                <template v-for="column in columnGroup">
                                                    <span
                                                        class="after:content-['/'] last:after:content-['']"
                                                        :class="{
                                                            'text-gray-800 font-medium': applied.sort.column == column,
                                                            'cursor-pointer hover:text-gray-800': available.columns.find(columnTemp => columnTemp.index === column)?.sortable,
                                                        }"
                                                        @click="
                                                            available.columns.find(columnTemp => columnTemp.index === column)?.sortable ? sort(available.columns.find(columnTemp => columnTemp.index === column)): {}
                                                        "
                                                    >
                                                        @{{ available.columns.find(columnTemp => columnTemp.index === column)?.label }}
                                                    </span>
                                                </template>
                                            </span>

                                            <i
                                                class="align-text-bottom text-base text-gray-800 ltr:ml-1.5 rtl:mr-1.5"
                                                :class="[applied.sort.order === 'asc' ? 'icon-down-stat': 'icon-up-stat']"
                                                v-if="columnGroup.includes(applied.sort.column)"
                                            ></i>
                                        </p>
                                    </div>

                                    <p class="flex justify-end text-gray-600 cursor-pointer">
                                        @lang('admin::app.settings.data-transfer.imports.edit.action')
                                    </p>
                                </div>
                            </template>
                        </template>

                        <template #body="{
                            isLoading,
                            available,
                            applied,
                            selectAll,
                            sort,
                            performAction
                        }">
                            <template v-if="isLoading">
                                <x-shop::shimmer.datagrid.table.body :isMultiRow="true"/>
                            </template>

                            <template v-else>
                                <div
                                    class="row grid px-4 py-2.5 border-b transition-all hover:bg-gray-50"
                                    style="grid-template-columns: repeat(6, minmax(0, 1fr));"
                                    v-for="record in available.records"
                                >
                                        <!-- Order Id, Created -->
                                        <p
                                            class="text-base text-gray-800 "
                                            v-html="record.increment_id"
                                        >
                                        </p>

                                        <p
                                            class="text-gray-600"
                                            v-html="record.created_at"
                                        >
                                        </p>

                                        <!--  Grand Total, Method Title -->
                                        <p 
                                            class="text-base text-gray-800 font-semibold"
                                            v-html="record.grand_total"
                                        >
                                        </p>

                                        <p 
                                            class="text-gray-600 "
                                            v-html="record.method_title"
                                        >
                                        </p>

                                        <p v-html="record.status"></p>
                                        
                                        <p class="flex justify-end">
                                            <!-- Arrow -->
                                            <a
                                                class="icon-edit text-2xl"
                                                @click="productAvail(record)"
                                            >
                                            </a>
                                        </p>
                                </div>
                            </template>
                        </template>
                    </x-shop::datagrid>
                </div>
                
                <div class="md:hidden">
                    <x-shop::datagrid :src="route('rma.customers.create')" >
                        <!-- Datagrid Header -->
                        <template #header="{
                            isLoading,
                            available,
                            applied,
                            selectAll,
                            sort,
                            performAction
                        }">
                            <template v-if="isLoading">
                                <x-shop::shimmer.datagrid.table.head :isMultiRow="true"/>
                            </template>

                            <template v-else>
                                <div 
                                    class="row grid items-center gap-2.5 border-b border-zinc-200 bg-zinc-100 px-6 py-4 text-sm font-medium text-black max-md:p-4" 
                                    style="grid-template-columns: repeat(2, minmax(0, 1fr));"
                                >
                                    <div
                                        class="flex gap-2.5 items-center select-none"
                                        v-for="(columnGroup, index) in [['increment_id', 'created_at', 'grand_total'], ['method_title', 'status']]"
                                    >
                                        <p class="text-gray-600">
                                            <span class="[&>*]:after:content-['_/_']">
                                                <template v-for="column in columnGroup">
                                                    <span
                                                        class="after:content-['/'] last:after:content-['']"
                                                        :class="{
                                                            'text-gray-800 font-medium': applied.sort.column == column,
                                                            'cursor-pointer hover:text-gray-800': available.columns.find(columnTemp => columnTemp.index === column)?.sortable,
                                                        }"
                                                        @click="
                                                            available.columns.find(columnTemp => columnTemp.index === column)?.sortable ? sort(available.columns.find(columnTemp => columnTemp.index === column)): {}
                                                        "
                                                    >
                                                        @{{ available.columns.find(columnTemp => columnTemp.index === column)?.label }}
                                                    </span>
                                                </template>
                                            </span>

                                            <i
                                                class="align-text-bottom text-base text-gray-800 ltr:ml-1.5 rtl:mr-1.5"
                                                :class="[applied.sort.order === 'asc' ? 'icon-down-stat': 'icon-up-stat']"
                                                v-if="columnGroup.includes(applied.sort.column)"
                                            ></i>
                                        </p>
                                    </div>
                                </div>
                            </template>
                        </template>

                        <template #body="{
                            isLoading,
                            available,
                            applied,
                            selectAll,
                            sort,
                            performAction
                        }">
                            <template v-if="isLoading">
                                <x-shop::shimmer.datagrid.table.body :isMultiRow="true"/>
                            </template>

                            <template v-else>
                                <div
                                    class="row grid px-4 py-2.5 border-b transition-all hover:bg-gray-50"
                                    style="grid-template-columns: repeat(2, minmax(0, 1fr));"
                                    v-for="record in available.records"
                                >
                                    <div class="flex gap-x-4 justify-between items-center">
                                        <div class="flex flex-col gap-1.5">
                                            <p
                                                class="text-gray-600"
                                                v-html="record.increment_id ?? 'N/A'"
                                            >
                                            </p>
                
                                            <p
                                                class="text-gray-600"
                                                v-html="record.created_at"
                                            >
                                            </p>
                
                                            <p
                                                class="text-gray-600"
                                                v-html="record.grand_total"
                                            >
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <div class="flex gap-x-4 justify-between items-center">
                                        <div class="flex flex-col gap-1.5">

                                        <p 
                                            class="text-gray-600 "
                                            v-html="record.method_title"
                                        >
                                        </p>

                                        <p v-html="record.status"></p>
                                        
                                        <p class="flex justify-end">
                                            <!-- Arrow -->
                                            <a
                                                class="icon-edit text-2xl"
                                                @click="productAvail(record)"
                                            >
                                            </a>
                                        </p>
                                    </div>
                                </div>
                                </div>
                            </template>
                        </template>
                    </x-shop::datagrid>
                </div>

                <x-shop::form
                    v-slot="{ meta, errors, handleSubmit }"
                    as="div"
                >
                    <form
                        @submit="handleSubmit($event, rmaSubmit)"
                        ref="rmaSubmit"
                    >
                        <x-shop::modal ref="rmaModel">
                            <!-- Modal Header -->
                            <x-slot:header>
                                <h2 class="text-base font-medium max-md:text-base">
                                    @lang('rma::app.shop.customer.create.heading')
                                </h2>
                            </x-slot>

                            <!-- Modal Content -->
                            <x-slot:content class="bg-white p-4 max-sm:p-3">
                                <div class="overflow-auto" style="min-height: 400px; max-height: 400px;">
                                    <v-order-items-list :key="refreshComponent" :order-id="isSelect"></v-order-items-list>
                                </div>
                            </x-slot>

                            <x-slot:footer>
                                <div class="flex justify-end">
                                    <button
                                        type="submit"
                                        :disabled="!rmaFormButton || !rmaFormSubmit"
                                        class="primary-button"
                                    >
                                        <svg v-if="!rmaFormSubmit" aria-hidden="true" class="w-5 h-5 text-gray-200 animate-spin fill-blue-600" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/>
                                            <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill"/>
                                        </svg>
                                        
                                        @lang('rma::app.shop.customer.submit-req')
                                    </button>
                                </div>
                            </x-slot>
                        </x-shop::modal>
                    </form>
                </x-shop::form>

                {{-- ============================================================
                     MODAL DERECHO DE RETRACTO
                     Se abre cuando checkRetracto() confirma que aplica.
                     Reutiliza v-order-items-list para mostrar los productos,
                     y agrega el banner informativo + checkbox de sello.
                ============================================================ --}}
                <form ref="rmaRetractoSubmit">
                    <x-shop::modal ref="rmaModelRetracto">
                        <x-slot:header>
                            <h2 class="text-base font-medium max-md:text-base">
                                Solicitud de Derecho de Retracto
                            </h2>
                        </x-slot>

                        <x-slot:content class="bg-white p-4 max-sm:p-3">
                            {{-- Banner informativo --}}
                            <div class="mb-4 rounded-lg border border-blue-200 bg-blue-50 p-4">
                                <div class="flex items-start gap-3">
                                    <span class="mt-0.5 text-blue-500 text-xl">ℹ️</span>
                                    <div>
                                        <p class="font-medium text-blue-800">Tienes derecho a retracto</p>
                                        <p class="text-sm text-blue-700 mt-1">
                                            Según la Ley 1480 de 2011 (Estatuto del Consumidor), tienes
                                            <strong>@{{ retracto.remainingDays }} día@{{ retracto.remainingDays !== 1 ? 's' : '' }} hábil@{{ retracto.remainingDays !== 1 ? 'es' : '' }}</strong>
                                            para ejercer tu derecho de retracto.
                                            La solicitud debe completarse antes del
                                            <strong>@{{ retracto.expiresAt }}</strong>.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {{-- Productos de la orden --}}
                            <div class="overflow-auto" style="min-height: 300px; max-height: 350px;">
                                <v-order-items-list :key="refreshComponent" :order-id="isSelect"></v-order-items-list>
                            </div>

                            {{-- Checkbox de sello para productos condicionados --}}
                            <div v-if="retracto.hasConditional" class="mt-4 rounded-lg border border-amber-200 bg-amber-50 p-4">
                                <div class="flex items-start gap-3">
                                    <span class="mt-0.5 text-amber-500 text-xl">⚠️</span>
                                    <div class="w-full">
                                        <p class="font-medium text-amber-800">Declaración requerida — Productos con sello de seguridad</p>
                                        <p class="text-sm text-amber-700 mt-1 mb-3">
                                            Tu pedido incluye cosméticos, perfumes u otros productos con sello de seguridad.
                                            El derecho de retracto aplica únicamente si el sello <strong>no ha sido abierto</strong>
                                            (Ley 1480/2011, Art. 47).
                                        </p>
                                        <label class="flex items-start gap-2 cursor-pointer">
                                            <input
                                                type="checkbox"
                                                v-model="retracto.sealIntact"
                                                class="mt-1 h-4 w-4 rounded border-gray-300 text-blue-600"
                                            />
                                            <span class="text-sm text-amber-800">
                                                <strong>Declaro</strong> que el sello de seguridad de todos los productos
                                                incluidos en este pedido está intacto y no ha sido abierto.
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            {{--
                                Cambio 1 (imágenes) y Cambio 2 (T&C) NO se agregan aquí.
                                El componente v-order-items-list ya renderiza ambos campos
                                una vez que el usuario selecciona producto + cantidad + resolución.
                                Agregarlos aquí causaría duplicados.
                                rmaRetractoSubmit() los localiza con querySelector sobre el form.
                            --}}
                        </x-slot>

                        <x-slot:footer>
                            <div class="flex justify-end">
                                <button
                                    type="button"
                                    :disabled="!rmaFormButton || !rmaFormSubmit"
                                    @click="rmaRetractoSubmit()"
                                    class="primary-button"
                                >
                                    <svg v-if="!rmaFormSubmit" aria-hidden="true" class="w-5 h-5 text-gray-200 animate-spin fill-blue-600" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/>
                                        <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill"/>
                                    </svg>
                                    Enviar solicitud de retracto
                                </button>
                            </div>
                        </x-slot>
                    </x-shop::modal>
                </form>

            </div>
        </script>

        <script
            type="text/x-template"
            id="v-order-items-list-template"
        >
            <div v-if="products.length > 0">
                <div v-for="product in products">
                    <div class="flex-row gap-2.5 border-b mt-2 mb-2">
                        <div class="flex gap-2.5 mb-3">
                            <!-- Checkbox -->
                            <p>
                                <div v-if="product.currentQuantity > '0'">
                                    <input 
                                        type="checkbox" 
                                        :name="'isChecked[' + getProductId(product) + ']'" 
                                        :id="'isChecked[' + getProductId(product) + ']'" 
                                        class="mt-6"
                                        v-model="isChecked[getProductId(product)]"
                                    >
                                </div>

                                <div v-else>
                                    <div class="ltr:ml-3 rtl:mr-3"></div>
                                </div>

                                <div v-if="isChecked[getProductId(product)]">
                                    <x-shop::form.control-group>
                                        <x-shop::form.control-group.control
                                            type="hidden"
                                            name="order_id" 
                                            ::value="product.order_id"
                                        />
                                    </x-shop::form.control-group>

                                    <x-shop::form.control-group>
                                        <x-shop::form.control-group.control
                                            type="hidden"
                                            ::name="'order_item_id[' + getProductId(product) + ']'" 
                                            :label="trans('rma::app.shop.customer.rma-qty')"
                                            :placeholder="trans('rma::app.shop.customer.rma-qty')"
                                            ::value="product.order_item_id"
                                        />
                                    </x-shop::form.control-group>
                                </div>
                            </p>

                            <!-- Image -->
                            <p>
                                <template v-if="product.base_image">
                                    <img
                                        class="min-h-[80px] max-h-[80px] min-w-[80px] max-w-[80px] rounded"
                                        :src="`${baseImageUrl}${product.base_image}`"
                                        :alt="`${product.base_image}`"
                                    />
                                </template>

                                <template v-else>
                                    <img
                                        class="min-h-[80px] max-h-[80px] min-w-[80px] max-w-[80px] rounded"
                                        src="{{ bagisto_asset('images/medium-product-placeholder.webp') }}"
                                        alt="medium-product-placeholder.webp"
                                    >
                                </template>
                            </p>

                            <p style="width: 100px; max-width: 100px;">
                                <div v-if="product.url_key && product.visible_individually">
                                    <a 
                                        :href="`{{ route('shop.product_or_category.index', '') }}/${product.url_key}`" 
                                        target='_blank' 
                                        class="text-blue-500 text-xs"
                                    > 
                                        @{{ product.name }}
                                        
                                        <br/>
                                        <span v-for="(attribute) in product.attributes" v-if="product.attributes">
                                            <b>@{{ attribute.attribute_name }} : </b>@{{ attribute.option_label }}<br>
                                        </span> 
                                    </a>
                                </div>

                                <div v-else>
                                    @{{ product.name }}
                                    
                                    <br/>
                                    <span v-for="(attribute) in product.attributes" v-if="product.attributes">
                                        <b>@{{ attribute.attribute_name }} : </b>@{{ attribute.option_label }}<br>
                                    </span> 
                                </div>
                            </p>

                            <!-- Sku, Price, Return Window -->
                            <p class="w-full">
                                <p class="flex text-sm justify-between whitespace-nowrap">
                                    <span>
                                        @lang('admin::app.catalog.products.index.create.sku'):
                                    </span>
                                    
                                    <span style="width: 300px; max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">@{{ product.sku }}</span>
                                </p>

                                <p class="flex text-sm justify-between whitespace-nowrap">
                                    <span>
                                        @lang('admin::app.catalog.attributes.create.price'):  
                                    </span>
                                    
                                    <span>@{{ formatPrice(product.price) }}</span>
                                </p>

                                <p class="flex text-sm justify-between whitespace-nowrap">
                                    <span>
                                        @lang('rma::app.admin.configuration.index.sales.rma.current-order-quantity'):
                                    </span>
                                    
                                    <span>
                                        @{{ product.currentQuantity }}
                                    </span>
                                </p>

                                <span v-if="product.rma_rules && products['0'].order_status != 'pending'">
                                    <p 
                                        v-if="resolutionType[getProductId(product)] == 'return'" 
                                        class="flex text-sm justify-between gap-3 whitespace-nowrap"
                                    >
                                        <span>
                                            @lang('rma::app.shop.customer.create.return-window'): 
                                        </span>

                                        <span>
                                            @{{ calculateDeliveredReturnWindow(product.created_at, product.rma_return_period) }}
                                        </span>
                                    </p>

                                    <p 
                                        v-if="resolutionType[getProductId(product)] == 'exchange'" 
                                        class="flex text-sm justify-between gap-3 whitespace-nowrap"
                                    >
                                        <span>
                                            @lang('rma::app.shop.customer.create.exchange-window'): 
                                        </span>

                                        <span>
                                            @{{ calculateDeliveredReturnWindow(product.created_at, product.rma_exchange_period) }}
                                        </span>
                                    </p>
                                </span>

                                <p 
                                    v-else 
                                    class="flex text-sm justify-between gap-3 whitespace-nowrap"
                                >
                                    <span v-if="! product.rma_exchange_period && ! product.rma_return_period">
                                        <span>
                                            @lang('rma::app.shop.customer.create.return-window'): 
                                        </span>

                                        <span>
                                            @{{ calculateReturnWindow(product.created_at) }}
                                        </span>
                                    </span>
                                </p>
                            </p>
                        </div>

                        <!-- RMA QTY -->
                        <p class="w-full" v-if="! notAllowed">
                            <div v-if="isChecked[getProductId(product)] && product.currentQuantity > '0'">
                                <!-- RMA Quantity -->
                                <x-shop::form.control-group>
                                    <x-shop::form.control-group.label class="required text-sm flex">
                                        @lang('rma::app.shop.customer.rma-qty')
                                    </x-shop::form.control-group.label>

                                    <x-shop::form.control-group.control
                                        type="text"
                                        ::name="'rma_qty[' + getProductId(product) + ']'" 
                                        ::rules="'min_value:1|required|max_value:' + product.currentQuantity"
                                        :label="trans('rma::app.shop.customer.rma-qty')"
                                        :placeholder="trans('rma::app.shop.customer.rma-qty')"
                                        v-model="rma_qty[getProductId(product)]"
                                    />

                                    <x-shop::form.control-group.error ::name="'rma_qty[' + getProductId(product) + ']'" class="flex"/>
                                </x-shop::form.control-group>
                            </div>

                            <div 
                                v-if="product.currentQuantity <= '0'" 
                                class="text-sm text-red-600 flex mb-2"
                            >
                                @lang('rma::app.admin.configuration.index.sales.rma.product-already-raw')
                            </div>
                        </p>

                        <div class="flex gap-3" v-if="! notAllowed">
                            <!-- Resolution Type for rules product -->
                            <p class="w-full" v-if="product.rma_exchange_period || product.rma_return_period">
                                <div v-if="isChecked[getProductId(product)] && product.currentQuantity > '0'">
                                    <x-shop::form.control-group>
                                        <x-shop::form.control-group.label class="required text-sm flex">
                                            @lang('rma::app.admin.configuration.index.sales.rma.resolution-type')
                                        </x-shop::form.control-group.label>

                                        <x-shop::form.control-group.control
                                            type="select"
                                            ::name="'resolution_type[' + getProductId(product) + ']'" 
                                            rules="required"
                                            v-model="resolutionType[getProductId(product)]"
                                            @change="getResolutionReason(getProductId(product))"
                                            :label="trans('rma::app.admin.configuration.index.sales.rma.resolution-type')"
                                        >
                                            <option value="">
                                                @lang('admin::app.catalog.products.edit.types.bundle.update-create.select')
                                            </option>

                                            <option 
                                                v-if="products['0'].order_status != 'pending' && (products['0'].order_status == 'processing' || products['0'].order_status == 'completed')  && product.rma_return_period" 
                                                value="return"
                                            >
                                                @lang('rma::app.admin.configuration.index.sales.rma.return')
                                            </option>

                                            <option
                                                v-if="products['0'].order_status != 'pending' && products['0'].order_status != 'processing' && products['0'].order_status == 'completed' && product.rma_exchange_period" 
                                                value="exchange"
                                            >
                                                @lang('rma::app.admin.configuration.index.sales.rma.exchange')
                                            </option>
                                            
                                            <option 
                                                v-if="products['0'].order_status == 'pending' && products['0'].order_status != 'processing' && products['0'].order_status != 'completed'"  
                                                value="cancel-items"
                                            >
                                                @lang('rma::app.admin.configuration.index.sales.rma.cancel-items')
                                            </option>
                                        </x-shop::form.control-group.control>

                                        <x-shop::form.control-group.error ::name="'resolution_type[' + getProductId(product) + ']'" class="flex"/>
                                    </x-shop::form.control-group>
                                </div>
                            </p>

                            <!-- Resolution Type -->
                            <p class="w-full" v-else>
                                <div v-if="isChecked[getProductId(product)] && product.currentQuantity > '0'">
                                    <x-shop::form.control-group>
                                        <x-shop::form.control-group.label class="required text-sm flex">
                                            @lang('rma::app.admin.configuration.index.sales.rma.resolution-type')
                                        </x-shop::form.control-group.label>

                                        <x-shop::form.control-group.control
                                            type="select"
                                            ::name="'resolution_type[' + getProductId(product) + ']'" 
                                            rules="required"
                                            v-model="resolutionType[getProductId(product)]"
                                            @change="getResolutionReason(getProductId(product))"
                                            :label="trans('rma::app.admin.configuration.index.sales.rma.resolution-type')"
                                        >
                                            <option value="">
                                                @lang('admin::app.catalog.products.edit.types.bundle.update-create.select')
                                            </option>

                                            <option 
                                                v-if="products['0'].order_status != 'pending' && (products['0'].order_status == 'processing' || products['0'].order_status == 'completed')" 
                                                value="return"
                                            >
                                                @lang('rma::app.admin.configuration.index.sales.rma.return')
                                            </option>

                                            <option 
                                                v-if="products['0'].order_status != 'pending' && products['0'].order_status != 'processing' && products['0'].order_status == 'completed'" 
                                                value="exchange"
                                            >
                                                @lang('rma::app.admin.configuration.index.sales.rma.exchange')
                                            </option>

                                            <option 
                                                v-if="products['0'].order_status == 'pending' && products['0'].order_status != 'processing' && products['0'].order_status != 'completed'"  
                                                value="cancel-items"
                                            >
                                                @lang('rma::app.admin.configuration.index.sales.rma.cancel-items')
                                            </option>
                                        </x-shop::form.control-group.control>

                                        <x-shop::form.control-group.error ::name="'resolution_type[' + getProductId(product) + ']'" class="flex"/>
                                    </x-shop::form.control-group>
                                </div>
                            </p>

                            <!-- Reasons -->
                            <p class="w-full">
                                <div 
                                    v-if="isChecked[getProductId(product)] 
                                        && product.currentQuantity > '0'
                                        && resolutionType[getProductId(product)] 
                                        && resolutionReason[getProductId(product)]
                                        && resolutionReason[getProductId(product)].length"
                                >
                                    <x-shop::form.control-group>
                                        <x-shop::form.control-group.label class="required text-sm flex">
                                            @lang('rma::app.shop.customer.create.reason')
                                        </x-shop::form.control-group.label>

                                        <x-shop::form.control-group.control
                                            type="select"
                                            ::name="'rma_reason_id[' + getProductId(product) + ']'" 
                                            v-model="rma_reason_id[getProductId(product)]"
                                            rules="required"
                                            :label="trans('rma::app.shop.customer.create.reason')"
                                        >
                                            <option
                                                v-for="reason in resolutionReason[getProductId(product)]"
                                                :value="reason.id"
                                                :key="reason.id"
                                            >
                                                @{{ formatTitle(reason.title) }}
                                            </option>
                                        </x-shop::form.control-group.control>

                                        <x-shop::form.control-group.error ::name="'rma_reason_id[' + getProductId(product) + ']'" class="flex"/>
                                    </x-shop::form.control-group>
                                </div>
                            </p>
                        </div>
                    </div>
                </div>

                <div 
                    class="gap-5" 
                    v-if="isChecked.length == rma_reason_id.length && rma_reason_id.length && rma_qty.length"
                >
                    <!-- Delivery Status -->
                    <x-shop::form.control-group>
                        <x-shop::form.control-group.label class="required text-sm mt-4 flex">
                            @lang('rma::app.admin.configuration.index.sales.rma.product-delivery-status')
                        </x-shop::form.control-group.label>

                        <x-shop::form.control-group.control
                            type="select"
                            name="order_status" 
                            rules="required"
                            v-model="orderStatus"
                            :label="trans('rma::app.admin.configuration.index.sales.rma.product-delivery-status')"
                            >
                            <option value="">
                                @lang('admin::app.catalog.products.edit.types.bundle.update-create.select')
                            </option>

                            <option 
                                v-if="products['0'].order_status != 'pending' && products['0'].order_status != 'processing'" 
                                value="1"
                            >
                                @lang('rma::app.shop.customer.delivered')
                            </option>

                            <option value="0">
                                @lang('rma::app.shop.customer.undelivered')
                            </option>
                        </x-shop::form.control-group.control>

                        <x-shop::form.control-group.error name="order_status" class="flex"/>
                    </x-shop::form.control-group>

                    <div v-if="orderStatus == '1'">
                        {{--
                            Los campos de dirección y horario de recogida han sido eliminados.
                            En Goloba, el cliente gestiona el envío de devolución directamente
                            con Servientrega y comparte el número de guía por chat interno.
                        --}}
                        <div class="mt-4 rounded-lg border border-blue-100 bg-blue-50 p-4 text-sm text-blue-800">
                            <p class="font-medium mb-1">¿Cómo enviar tu devolución?</p>
                            <p>Genera tu guía de devolución con Servientrega y comparte el número de guía con el vendedor a través del chat de esta solicitud.</p>
                        </div>

                        <!-- Additionally -->
                        @foreach ($customAttributes as $attribute)
                            <x-shop::form.control-group>
                                <x-shop::form.control-group.label class="flex text-sm mt-4">
                                    {!! $attribute->label . ($attribute->is_required == '1' ? '<span class="required"></span>' : '') !!}
                                </x-shop::form.control-group.label>

                                @if ($attribute->is_required == '1')
                                   @php 
                                    $attribute->is_required = 'required'; 
                                   @endphp
                                @elseif ($attribute->is_required == '0')
                                    @php 
                                    $attribute->is_required = ''; 
                                   @endphp
                                @endif

                                @switch($attribute->type)
                                    @case('text')
                                        <x-shop::form.control-group.control
                                            type="text"
                                            name="{{ $attribute->code }}"
                                            rules="{{ $attribute->is_required }}"
                                            :value="old($attribute->code)"
                                            label="{{ $attribute->label }}"
                                            placeholder="{{ $attribute->label }}"
                                        />

                                        <x-shop::form.control-group.error class="flex" control-name="{{ $attribute->code }}" />
                                        
                                        @break

                                    @case('textarea')
                                        <x-shop::form.control-group.control
                                            type="textarea"
                                            name="{{ $attribute->code }}"
                                            rules="{{ $attribute->is_required }}"
                                            :value="old($attribute->code)"
                                            label="{{ $attribute->label }}"
                                            placeholder="{{ $attribute->label }}"
                                            rows="12"
                                        />
        
                                        <x-shop::form.control-group.error class="flex" control-name="{{ $attribute->code }}" />
                                        
                                        @break

                                    @case('date')
                                        <x-shop::form.control-group.control
                                            type="date"
                                            name="{{ $attribute->code }}"
                                            rules="{{ $attribute->is_required }}"
                                            :value="old($attribute->code)"
                                            label="{{ $attribute->label }}"
                                            placeholder="{{ $attribute->label }}"
                                        />

                                        <x-shop::form.control-group.error class="flex" control-name="{{ $attribute->code }}" />

                                        @break

                                    @case('select')
                                        <x-shop::form.control-group.control
                                            type="select"
                                            id="{{ $attribute->code }}"
                                            class="cursor-pointer"
                                            name="{{ $attribute->code }}"
                                            rules="{{ $attribute->is_required }}"
                                            :value="old($attribute->code)"
                                            label="{{ $attribute->label }}"
                                        >
                                            <!-- Here! All Needed types are defined -->
                                            @foreach($attribute->options ?? [] as $option)
                                                <option
                                                    value="{{ $option->option_value }}"
                                                >
                                                    {{ $option->option_name }}
                                                </option>
                                            @endforeach
                                        </x-shop::form.control-group.control>

                                        <x-shop::form.control-group.error class="flex" control-name="{{ $attribute->code }}" />

                                        @break

                                    @case('multiselect')
                                        <x-shop::form.control-group.control
                                            type="multiselect"
                                            id="{{ $attribute->code }}"
                                            class="cursor-pointer"
                                            name="{{ $attribute->code }}[]"
                                            rules="{{ $attribute->is_required }}"
                                            :value="old($attribute->code)"
                                            label="{{ $attribute->label }}"
                                        >
                                            <!-- Here! All Needed types are defined -->
                                            @foreach($attribute->options ?? [] as $option)
                                                <option
                                                    value="{{ $option->option_value }}"
                                                >
                                                    {{ $option->option_name }}
                                                </option>
                                            @endforeach
                                        </x-shop::form.control-group.control>

                                        <x-shop::form.control-group.error class="flex" control-name="{{ $attribute->code }}" />

                                        @break

                                    @case('checkbox')
                                        @foreach($attribute->options ?? [] as $index => $option)
                                            <label class="relative mb-2 flex cursor-pointer items-start">
                                                <v-field
                                                    type="checkbox"
                                                    class="flex"
                                                    name="{{ $attribute->code }}[{{ $index }}]" 
                                                    rules="{{ $attribute->is_required }}"
                                                    v-slot="{ field }"
                                                    value="{{ $option->option_value }}"
                                                >
                                                    <input
                                                        type="checkbox"
                                                        class="peer sr-only"
                                                        id="{{ $attribute->code }}-{{ $index }}"
                                                        rules="required"
                                                        name="{{ $attribute->code }}[{{ $index }}]"
                                                        value="{{ $option->option_value }}"
                                                        v-bind="field"
                                                    />
                                                </v-field>

                                                <label
                                                    class="icon-uncheck peer-checked:icon-check-box cursor-pointer text-base peer-checked:text-navyBlue"
                                                    for="{{ $attribute->code }}-{{ $index }}"
                                                >
                                                    {{ $option->option_name }}
                                                </label>
                                            </label>
                                    
                                            <x-admin::form.control-group.error control-name="{{ $attribute->code }}[{{ $index }}]" class="flex"/>
                                        @endforeach
                                        
                                        @break
                                    
                                    @case('radio')
                                        @foreach($attribute->options ?? [] as $option)
                                            <label class="relative mb-2 flex cursor-pointer items-start">
                                                <v-field
                                                    type="radio"
                                                    class="flex"
                                                    name="attribute_{{ $attribute->id }}"
                                                    rules="{{ $attribute->is_required }}"
                                                    v-slot="{ field }"
                                                    value="{{ $option->option_name }}"
                                                >
                                                    <input
                                                        type="radio"
                                                        class="sr-only peer"
                                                        id="option_{{ $loop->index }}"
                                                        rules="{{ $attribute->is_required }}"
                                                        name="attribute_{{ $attribute->id }}"
                                                        value="{{ $option->option_name }}"
                                                        v-bind="field"
                                                    />
                                                </v-field>
                                        
                                                <label
                                                    class="icon-radio-unselect text-base peer-checked:icon-radio-select peer-checked:text-navyBlue cursor-pointer"
                                                    for="option_{{ $loop->index }}"
                                                >
                                                    {{ $option->option_name }}
                                                </label>
                                            </label>
                                        @endforeach
                                            
                                        <x-admin::form.control-group.error control-name="{{ $option->option_name }}" class="flex"/>

                                    @break

                                @endswitch
                            </x-shop::form.control-group>
                        @endforeach
                    </div>

                    <!-- Additional information -->
                    <x-shop::form.control-group>
                        <x-shop::form.control-group.label class="text-sm flex">
                            @lang('rma::app.shop.customer.create.information')
                        </x-shop::form.control-group.label>

                        <x-shop::form.control-group.control
                            type="textarea"
                            name="information"
                            id="information"
                            v-model="information"
                            :label="trans('rma::app.shop.customer.create.information')"
                            :placeholder="trans('rma::app.shop.customer.create.information')"
                            @input="sanitizeTextarea"
                            rows="4"
                            maxlength="250"
                        />

                        <x-shop::form.control-group.error control-name="information" class="flex"/>
                    </x-shop::form.control-group>

                    <!-- Images (obligatorio - Cambio 1) -->
                    <x-shop::form.control-group class="mt-4">
                        <x-shop::form.control-group.label class="required text-sm flex">
                            @lang('admin::app.catalog.products.edit.images.title')
                        </x-shop::form.control-group.label>
                        
                        <x-shop::form.control-group.control
                            type="image"
                            class="!p-0 rounded-xl text-gray-700 mb-0"
                            name="images[]"
                            :label="trans('admin::app.catalog.products.edit.images.title')"
                            :is-multiple="false"
                            accepted-types="image/*"
                        />

                        <x-shop::form.control-group.error control-name="images[]" class="flex"/>

                        <p
                            v-if="imageError"
                            class="mt-1 text-sm text-red-600"
                        >
                            @{{ imageError }}
                        </p>
                    </x-shop::form.control-group>

                    @include('rma::shop.customer.rma.terms')
                </div>
            </div>

            <div v-else>
                @lang('rma::app.shop.customer.create.rma-not-available-quotes')
            </div>
        </script>
        
        <script type="module">
            app.component('v-customer-new-rma', {
                template: '#v-customer-new-rma-template',

                data() {
                    return {
                        isSelect: 0,
                        refreshComponent: 1,
                        rmaFormButton: false,
                        rmaFormSubmit: true,
                        imageError: null,
                        // Datos del Derecho de Retracto para el modal alternativo
                        retracto: {
                            remainingDays:  0,
                            expiresAt:      '',
                            hasConditional: false,
                            sealIntact:     false,
                        },
                    }
                },

                mounted() {
                    // adding eventBus listener
                    this.$emitter.on('valid-rma', (data) => {
                        if (data.isValid) {
                            this.rmaFormButton = true;
                        }
                    })

                    @isset($preselectedOrderId)
                    // Orden pre-seleccionada por el servidor (create-form determinó
                    // que no aplica retracto y redirigió aquí con el order_id).
                    // Abrimos el modal automáticamente sin que el usuario tenga
                    // que volver a buscar y seleccionar la orden.
                    this.$nextTick(() => {
                        this.isSelect = {{ (int) $preselectedOrderId }};
                        ++this.refreshComponent;
                        this.$refs.rmaModel.toggle();
                    });
                    @endisset
                },

                methods: {
                    productAvail(record) {
                        this.isSelect = record.id;
                        ++this.refreshComponent;

                        // Consultar si aplica retracto antes de abrir el modal
                        this.$axios.get(`{{ url('customer/account/rma/check-retracto') }}?order_id=${record.id}`)
                            .then(response => {
                                const data = response.data;
                                if (data.applies) {
                                    this.retracto.remainingDays  = data.remainingDays;
                                    this.retracto.expiresAt      = data.expiresAt;
                                    this.retracto.hasConditional = data.hasConditional;
                                    this.retracto.sealIntact     = false;
                                    this.$refs.rmaModelRetracto.toggle();
                                } else {
                                    this.$refs.rmaModel.toggle();
                                }
                            })
                            .catch(() => {
                                // Si falla la verificación, abrir modal estándar
                                this.$refs.rmaModel.toggle();
                            });
                    },

                    rmaSubmit(params, { resetForm, setErrors  }) {
                        // Cambio 1: validar imagen obligatoria en modal estándar
                        const imagesInput = this.$refs.rmaSubmit.querySelector('input[name="images[]"]');
                        if (!imagesInput || !imagesInput.files || imagesInput.files.length === 0) {
                            this.imageError = 'Debes adjuntar al menos una imagen de los productos.';
                            return;
                        }
                        this.imageError = null;

                        let formData = new FormData(this.$refs.rmaSubmit);

                        this.rmaFormSubmit = false; 
                        
                        this.$axios.post("{{ route('rma.customers.store') }}", formData)
                            .then((response) => {

                                this.$emitter.emit('add-flash', { type: 'success', message: response.data.messages });

                                setTimeout(() => {
                                    window.location.reload();
                                }, 3000);
                            })
                            .catch((error) => {
                                this.rmaFormSubmit = true;

                                const message = error?.response?.data?.messages
                                    ?? 'Ocurrió un error al enviar la solicitud. Por favor intenta de nuevo.';

                                this.$emitter.emit('add-flash', { type: 'error', message: message });
                            });
                    },

                    rmaRetractoSubmit() {
                        if (this.retracto.hasConditional && !this.retracto.sealIntact) {
                            this.$emitter.emit('add-flash', { type: 'error', message: 'Debes declarar que el sello de seguridad está intacto para continuar.' });
                            return;
                        }

                        // Cambio 1: validar imagen obligatoria — el input viene de v-order-items-list
                        const imagesInput = this.$refs.rmaRetractoSubmit.querySelector('input[type="file"][name="images[]"]');
                        if (!imagesInput || !imagesInput.files || imagesInput.files.length === 0) {
                            this.$emitter.emit('add-flash', { type: 'error', message: 'Debes adjuntar al menos una imagen de los productos.' });
                            return;
                        }
                        this.retractoImageError = null;

                        // Cambio 2: validar términos y condiciones — el checkbox viene de v-order-items-list
                        const agreementInput = this.$refs.rmaRetractoSubmit.querySelector('input[name="agreement"]');
                        if (!agreementInput || !agreementInput.checked) {
                            this.$emitter.emit('add-flash', { type: 'error', message: 'Debes aceptar los Términos y Condiciones para continuar.' });
                            return;
                        }

                        let formData = new FormData(this.$refs.rmaRetractoSubmit);
                        formData.append('rma_type', 'retracto');
                        if (this.retracto.hasConditional) {
                            formData.append('retracto_seal_intact', this.retracto.sealIntact ? '1' : '0');
                        }

                        this.rmaFormSubmit = false;

                        this.$axios.post("{{ route('rma.customers.store') }}", formData)
                            .then((response) => {
                                this.$emitter.emit('add-flash', { type: 'success', message: response.data.messages });
                                setTimeout(() => { window.location.reload(); }, 3000);
                            })
                            .catch(error => {
                                this.rmaFormSubmit = true;
                                const message = error?.response?.data?.messages
                                    ?? 'Ocurrió un error al enviar la solicitud. Por favor intenta de nuevo.';
                                this.$emitter.emit('add-flash', { type: 'error', message: message });
                            });
                    },
                }
            });

            app.component('v-order-items-list', {
                template: '#v-order-items-list-template',

                props: ['orderId'],

                data() {
                    return {
                        isLoading: true,

                        isChecked: [],

                        orderStatus: '',
 
                        resolutionReason: [],

                        rma: [],

                        rma_qty: [],

                        rma_reason_id: [],

                        products: '',

                        resolutionType: [],

                        notAllowed: false,

                        baseImageUrl: '{{ Storage::url('') }}',

                        returnWindowDays: parseInt('{{ core()->getConfigData('sales.rma.setting.default_allow_days') }}'),
                    }
                },

                updated() {
                    let isValid = this.isChecked.length == this.rma_reason_id.length && this.rma_reason_id.length && this.rma_qty.length;

                    this.$emitter.emit('valid-rma', {
                        isValid: isValid,
                    });
                },
                
                mounted() {
                    this.getOrderItems();
                },

                methods: {
                    formatPrice(price) {
                        return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(price);
                    },

                    getProductId(product) {
                        return product.type === 'configurable' 
                            ? product.additional.selected_configurable_option 
                            : product.product_id;
                    },

                    getOrderItems(orderId) {
                        if (this.orderId) {
                            this.$axios.get('{{ route("rma.customers.getOrderProduct", "") }}/' + this.orderId)
                                .then(response => {
                                    this.isLoading = false;
                                    
                                    this.products = response.data;
                                }).catch(error => {
                                    console.log(error);
                                });
                        }
                    },

                    sanitizeTextarea(event) {
                        this.information = this.sanitizeInput(event.target.value);
                    },

                    sanitizeInput(value) {
                        if (!value) return '';

                        return String(value)
                            .replace(/[<>]/g, '') 
                            .replace(/&/g, '&amp;')
                            .replace(/"/g, '&quot;')
                            .replace(/'/g, '&#39;');
                    },

                    calculateReturnWindow(createdAt) {
                        const createdDate = new Date(createdAt);
                        const returnDate = new Date(createdDate);
                        returnDate.setDate(createdDate.getDate() + this.returnWindowDays);

                        const currentDate = new Date();

                        if (returnDate < currentDate) {
                            this.notAllowed = true;

                            return 'Not Allowed';
                        }

                        return new Intl.DateTimeFormat('en-GB', {
                            day: 'numeric',
                            month: 'long',
                            year: 'numeric'
                        }).format(returnDate);
                    },

                    calculateDeliveredReturnWindow(created_At, rulesDays) {
                        const createdAt = new Date(created_At);
                        const returnDate = new Date(
                            createdAt.getTime() + rulesDays * 24 * 60 * 60 * 1000
                        );

                        returnDate.setUTCDate(returnDate.getDate());

                        const currentDate = new Date();

                        if (returnDate < currentDate) {
                            this.notAllowed = true;

                            return 'Not Allowed';
                        }

                        return new Intl.DateTimeFormat('en-GB', {
                            day: 'numeric',
                            month: 'long',
                            year: 'numeric',
                        }).format(returnDate);
                    },

                    getResolutionReason(product_id) {
                        let resolutionType = this.resolutionType[product_id];

                        let url = '{{route("rma.customers.getResolutionReason", ":resolutionType")}}';

                        url = url.replace(':resolutionType', resolutionType);

                        if (resolutionType) {
                            this.$axios.get(url)
                                .then(response => {
                                    if (response.data['0'] == null) {
                                        this.resolutionReason[product_id] = null;

                                        return;
                                    }
                                    
                                    this.resolutionReason[product_id] = response.data;
                                    
                                }).catch(error => {
                                    console.log(error);
                                });
                        }
                    },

                    formatTitle(title) {
                        if (title.length > 100) {
                            return title.slice(0, 100) + '...';
                        }

                        return title;
                    },
                },
            });
        </script>
    @endPushOnce
</x-shop::layouts.account>