@php
$customAttributes = app('Webkul\RMA\Repositories\RmaCustomFieldRepository')->with('options')->where('status', 1)->get();
@endphp

<x-shop::layouts.account>
    <x-slot:title>
        Derecho de Retracto
    </x-slot>

    @section('breadcrumbs')
        <x-shop::breadcrumbs name="rma.create"></x-shop::breadcrumbs>
    @endSection

    <div class="mx-4">
        <x-shop::layouts.account.navigation />
    </div>

    <div class="mx-4 flex-auto max-md:mx-6 max-sm:mx-4">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl max-lg:text-base font-medium">
                Solicitud de Retracto
            </h2>
            <a
                href="{{ route('rma.customers.all-rma') }}"
                class="secondary-button flex items-center gap-x-2 border-[#E9E9E9] px-5 max-lg:px-3 max-lg:text-xs py-3 font-normal"
            >
                @lang('shop::app.checkout.onepage.address.back')
            </a>
        </div>

        {{-- Banner informativo: días restantes --}}
        <div class="mt-4 rounded-lg border border-blue-200 bg-blue-50 p-4">
            <div class="flex items-start gap-3">
                <span class="mt-0.5 text-blue-500 text-xl">ℹ️</span>
                <div>
                    <p class="font-medium text-blue-800">Tienes derecho a retracto</p>
                    <p class="text-sm text-blue-700 mt-1">
                        Según la Ley 1480 de 2011 (Estatuto del Consumidor), tienes
                        <strong>{{ $remainingDays }} día{{ $remainingDays !== 1 ? 's' : '' }} hábil{{ $remainingDays !== 1 ? 'es' : '' }}</strong>
                        para ejercer tu derecho de retracto.
                        La solicitud debe completarse antes del
                        <strong>{{ $expiresAt->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY') }}</strong>.
                    </p>
                </div>
            </div>
        </div>

        {!! view_render_event('bagisto.shop.customers.account.new-rma.list.before') !!}

        <v-customer-new-rma></v-customer-new-rma>

        {!! view_render_event('bagisto.shop.customers.account.new-rma.list.after') !!}
    </div>

    @pushOnce('scripts')
        {{--
            El formulario de retracto reutiliza todo el JS del formulario estándar
            (datagrid, modal, v-order-items-list, etc.) incluyendo sus scripts.
            Solo sobreescribimos el contenido del modal para agregar:
            - Campo hidden rma_type = retracto
            - Checkbox de declaración de sello para productos condicionados
        --}}
        @include('rma::shop.customer.rma.create')

        {{-- Override: reemplaza el template del modal con la versión retracto --}}
        <script>
        // Registrar componente Vue con el template de retracto (mismo ID sobreescribe el original)
        // El script del vendor usa app.component() por lo que redefinirlo aquí lo reemplaza.
        // Si el vendor usa define() con ID único, este override no aplica visualmente
        // y los campos hidden se agregan directamente al form vía Blade.
        </script>

        {{-- Campos adicionales que se inyectan vía hidden al submit --}}
        <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Inyectar rma_type=retracto en el form antes del submit
            document.querySelectorAll('form[ref="rmaSubmit"], form').forEach(function(form) {
                if (!form.querySelector('[name="rma_type"]')) {
                    var input = document.createElement('input');
                    input.type  = 'hidden';
                    input.name  = 'rma_type';
                    input.value = 'retracto';
                    form.appendChild(input);
                }
            });
        });
        </script>
    @endPushOnce

    {{-- Checkbox de sello: se muestra solo si hay productos condicionados --}}
    @if($hasConditional)
    <div class="mx-4 mt-4 rounded-lg border border-amber-200 bg-amber-50 p-4" id="retracto-seal-notice">
        <div class="flex items-start gap-3">
            <span class="mt-0.5 text-amber-500 text-xl">⚠️</span>
            <div class="w-full">
                <p class="font-medium text-amber-800">Declaración requerida — Productos con sello de seguridad</p>
                <p class="text-sm text-amber-700 mt-1 mb-3">
                    Tu pedido incluye cosméticos, perfumes u otros productos con sello de seguridad.
                    El derecho de retracto aplica únicamente si el sello de seguridad del producto
                    <strong>no ha sido abierto</strong>. Si el sello está roto, el producto no es elegible
                    para retracto por razones de higiene y seguridad (Ley 1480/2011, Art. 47).
                </p>
                <label class="flex items-start gap-2 cursor-pointer">
                    <input
                        type="checkbox"
                        name="retracto_seal_intact"
                        value="1"
                        id="retracto_seal_intact"
                        class="mt-1 h-4 w-4 rounded border-gray-300 text-blue-600"
                        required
                    />
                    <span class="text-sm text-amber-800">
                        <strong>Declaro</strong> que el sello de seguridad de todos los productos cosméticos
                        o con restricción de higiene incluidos en este pedido está intacto y no ha sido abierto.
                    </span>
                </label>
            </div>
        </div>
    </div>
    @endif

</x-shop::layouts.account>
