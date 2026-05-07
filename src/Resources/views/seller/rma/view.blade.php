<x-marketplace::shop.layouts>
    <x-slot:title>
        RMA #{{ $rmaData->id }}
    </x-slot>

    <!-- Encabezado -->
    <div class="flex items-center justify-between gap-4 max-xl:flex-wrap">
        <div>
            <p class="text-2xl font-medium">
                RMA #{{ $rmaData->id }}
            </p>
            <p class="mt-1 text-sm text-gray-600">
                Orden #{{ $rmaData->order_id }} • 
                {{ core()->formatDate($rmaData->created_at, 'd M Y H:i') }}
            </p>
        </div>
        
        <div class="flex items-center gap-2.5">
            <a
                href="{{ route('goloba.seller.rma.index') }}"
                class="secondary-button px-5 py-2.5"
            >
                Volver al Listado
            </a>
        </div>
    </div>

    <!-- Estado y Tipo -->
    <div class="mt-4 flex items-center gap-2.5 max-xl:flex-wrap">
        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium
            {{ $rmaData->rma_status === 'Pending' ? 'bg-yellow-500 text-white' : '' }}
            {{ $rmaData->rma_status === 'Accept' ? 'bg-green-600 text-white' : '' }}
            {{ $rmaData->rma_status === 'Declined' ? 'bg-red-600 text-white' : '' }}
        ">
            {{ $rmaData->rma_status }}
        </span>

        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium
            {{ $rmaData->rma_type === 'retracto' ? 'bg-blue-600 text-white' : 'bg-gray-600 text-white' }}
        ">
            {{ $rmaData->rma_type === 'retracto' ? 'Derecho de Retracto' : 'RMA Estándar' }}
        </span>

        @if($rmaData->package_condition)
            <span class="text-xs text-gray-600">
                Condición: {{ ucfirst($rmaData->package_condition) }}
            </span>
        @endif
    </div>

    <!-- Contenido Principal -->
    <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-3">
        
        <!-- Columna Principal (2/3) -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Productos -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold mb-4">Productos Solicitados</h2>
                
                <div class="space-y-4">
                    @foreach($rmaItemDetails as $item)
                        <div class="flex gap-4 border-b pb-4 last:border-b-0 last:pb-0">
                            <!-- Imagen del Producto -->
                            <div class="h-20 w-20 flex-shrink-0">
                                @if(isset($item['product_image']) && $item['product_image'])
                                    <img 
                                        src="{{ Storage::url($item['product_image']) }}"
                                        alt="{{ $item['product_name'] }}"
                                        class="h-full w-full rounded object-cover"
                                    >
                                @else
                                    <div class="h-full w-full rounded bg-gray-100 flex items-center justify-center">
                                        <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                @endif
                            </div>

                            <!-- Información del Producto -->
                            <div class="flex-1">
                                <h3 class="font-medium text-gray-900">{{ $item['product_name'] }}</h3>
                                <p class="text-sm text-gray-600">SKU: {{ $item['sku'] }}</p>
                                <p class="text-sm text-gray-600">Cantidad: {{ $item['quantity'] }}</p>
                                
                                <div class="mt-2 flex gap-4">
                                    <span class="text-xs text-gray-500">
                                        Resolución: <span class="font-medium">{{ ['return' => 'Devolución', 'exchange' => 'Reemplazo', 'cancel-items' => 'Cancelación'][$item['resolution']] ?? ucfirst($item['resolution']) }}</span>
                                    </span>
                                    @if($item['reason'])
                                        <span class="text-xs text-gray-500">
                                            Razón: <span class="font-medium">{{ $item['reason'] }}</span>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Imágenes Adjuntas -->
            @if(isset($rmaImages) && $rmaImages->count() > 0)
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold mb-4">Imágenes Adjuntas</h2>
                    
                    <div class="grid grid-cols-3 gap-4">
                        @foreach($rmaImages as $image)
                            <a 
                                href="{{ Storage::url($image->path) }}" 
                                target="_blank"
                                class="group relative aspect-square overflow-hidden rounded-lg"
                            >
                                <img 
                                    src="{{ Storage::url($image->path) }}"
                                    alt="Imagen RMA"
                                    class="h-full w-full object-cover transition group-hover:scale-105"
                                >
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Información Adicional -->
            @if($rmaData->information)
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold mb-4">Información del Cliente</h2>
                    <p class="text-gray-700 whitespace-pre-line">{{ $rmaData->information }}</p>
                </div>
            @endif
        </div>

        <!-- Columna Lateral (1/3) -->
        <div class="space-y-6">
            
            <!-- Información del Cliente -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold mb-4">Cliente</h2>
                
                <div class="space-y-3 text-sm">
                    @if($rmaData->order && $rmaData->order->customer)
                        <div>
                            <p class="text-gray-600">Nombre</p>
                            <p class="font-medium">{{ $rmaData->order->customer_first_name }} {{ $rmaData->order->customer_last_name }}</p>
                        </div>
                        
                        <div>
                            <p class="text-gray-600">Email</p>
                            <p class="font-medium">{{ $rmaData->order->customer_email }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Estado de la Orden -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold mb-4">Estado de la Orden</h2>
                
                <div class="space-y-3 text-sm">
                    <div>
                        <p class="text-gray-600">Estado del Pedido</p>
                        <p class="font-medium">{{ $rmaData->order_status ? 'Activo' : 'Inactivo' }}</p>
                    </div>
                    
                    <div>
                        <p class="text-gray-600">Condición del Paquete</p>
                        <p class="font-medium">{{ ucfirst($rmaData->package_condition ?? 'N/A') }}</p>
                    </div>
                </div>
            </div>

            <!-- Tracking Servientrega -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold mb-4">Tracking Servientrega</h2>

                @if($trackingGuia)
                    <p class="text-xs text-gray-500 mb-3">Guía: <span class="font-mono font-medium text-gray-700">{{ $trackingGuia }}</span></p>

                    @if($trackingEstado)
                        <div class="space-y-3 text-sm">
                            {{-- Badge de estado --}}
                            @php
                                $estadoId = $trackingEstado->idEstadoEnvio;
                                $badgeClass = match($estadoId) {
                                    3       => 'bg-green-100 text-green-800',
                                    4       => 'bg-orange-100 text-orange-800',
                                    5       => 'bg-red-100 text-red-800',
                                    2       => 'bg-blue-100 text-blue-800',
                                    default => 'bg-gray-100 text-gray-700',
                                };
                            @endphp
                            <div>
                                <p class="text-gray-600 mb-1">Estado actual</p>
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $badgeClass }}">
                                    {{ $trackingEstado->estadoEnvio ?: 'Sin información' }}
                                </span>
                            </div>

                            {{-- Fecha de entrega --}}
                            @if($trackingEstado->fechaEntrega)
                                <div>
                                    <p class="text-gray-600">Fecha de entrega</p>
                                    <p class="font-medium text-green-700">
                                        {{ \Carbon\Carbon::parse($trackingEstado->fechaEntrega)->format('d M Y H:i') }}
                                    </p>
                                </div>
                            @else
                                <div>
                                    <p class="text-gray-600">Fecha de entrega</p>
                                    <p class="text-gray-400 italic">Pendiente</p>
                                </div>
                            @endif
                        </div>
                    @else
                        {{--
                            El microservicio no tiene esta guía aún.
                            Causa más probable: el webhook de Servientrega todavía no ha llegado
                            (el paquete fue generado pero aún no tiene movimientos logísticos).
                            No es un error — es el estado inicial normal de una guía recién creada.
                        --}}
                        <div class="space-y-3 text-sm">
                            <div>
                                <p class="text-gray-600 mb-1">Estado actual</p>
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-gray-100 text-gray-600">
                                    Sin movimientos registrados
                                </span>
                            </div>
                            <p class="text-xs text-gray-400">
                                Servientrega aún no ha reportado actividad para esta guía.
                                El estado se actualizará automáticamente cuando el paquete sea movilizado.
                            </p>
                        </div>
                    @endif
                @else
                    <p class="text-sm text-gray-500 italic">
                        No hay guía de envío registrada para esta orden.
                    </p>
                @endif
            </div>

            {{-- ── Acciones (solo cuando está Pendiente) ────────────────────────── --}}
            @if($rmaData->rma_status === 'Pending')

                {{-- Aceptar --}}
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold mb-1">Aceptar solicitud</h2>
                    <p class="text-sm text-gray-500 mb-4">
                        Acepta la devolución si el paquete llegó en condiciones correctas.
                    </p>
                    <form method="POST" action="{{ route('goloba.seller.rma.change_status') }}">
                        @csrf
                        <input type="hidden" name="rma_id"     value="{{ $rmaData->id }}">
                        <input type="hidden" name="rma_status" value="Accept">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Mensaje (Opcional)
                            </label>
                            <textarea
                                name="message"
                                rows="2"
                                class="w-full rounded-lg border border-gray-300 p-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Ej: Paquete recibido en buen estado."
                            ></textarea>
                        </div>
                        <button type="submit" class="w-full rounded-lg bg-magentaGoloba px-4 py-2.5 text-sm font-medium text-white hover:opacity-90">
                            ✓ Aceptar RMA
                        </button>
                    </form>
                </div>

                {{-- Abrir disputa --}}
                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-red-500">
                    <h2 class="text-lg font-semibold mb-1 text-red-700">Disputar solicitud</h2>
                    <p class="text-sm text-gray-500 mb-4">
                        Si el paquete recibido no corresponde con lo declarado por el cliente (contenido incorrecto, producto adulterado, etc.), documenta tu caso aquí. El administrador revisará la evidencia y tomará la decisión final.
                    </p>

                    <form
                        method="POST"
                        action="{{ route('goloba.seller.rma.dispute') }}"
                        enctype="multipart/form-data"
                        class="space-y-4"
                        id="dispute-form"
                        ref="disputeForm"
                    >
                        @csrf
                        <input type="hidden" name="rma_id" value="{{ $rmaData->id }}">

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Observaciones <span class="text-red-500">*</span>
                            </label>
                            <textarea
                                name="observations"
                                rows="5"
                                required
                                maxlength="3000"
                                class="w-full rounded-lg border border-gray-300 p-2 text-sm shadow-sm focus:border-red-400 focus:ring-red-400"
                                placeholder="Describe detalladamente qué encontraste al recibir el paquete: qué contenía, en qué condición llegó, por qué no corresponde a lo devuelto por el cliente..."
                            ></textarea>
                            <p class="mt-1 text-xs text-gray-400">Máximo 3000 caracteres.</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Evidencia fotográfica
                                <span class="text-gray-400 font-normal">(Opcional — máx. 10 imágenes, 5 MB c/u)</span>
                            </label>
                            <input
                                type="file"
                                name="images[]"
                                multiple
                                accept="image/jpg,image/jpeg,image/png,image/webp"
                                class="block w-full text-sm text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-red-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-red-700 hover:file:bg-red-100"
                                id="dispute-images"
                            >
                            <div id="dispute-image-preview" class="mt-2 flex flex-wrap gap-2"></div>
                        </div>

                        <button
                            type="button"
                            class="w-full rounded-lg bg-red-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-red-700"
                            @click="$emitter.emit('open-confirm-modal', { agree: () => { $refs.disputeForm.submit() } })"
                        >
                            ⚠ Enviar disputa al administrador
                        </button>
                    </form>
                </div>

            @endif

            {{-- ── Disputa enviada (estado Disputed) ──────────────────────────── --}}
            @if($rmaData->rma_status === 'Disputed')
                @php
                    $dispute = \Goloba\GolobaRMA\Models\RmaDispute::with('images')
                        ->where('rma_id', $rmaData->id)->latest()->first();
                @endphp
                @if($dispute)
                    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-yellow-500">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="inline-flex items-center rounded-full bg-yellow-500 px-3 py-1 text-xs font-medium text-white">
                                En revisión por el administrador
                            </span>
                        </div>
                        <h2 class="text-base font-semibold text-gray-800 mb-2">Tu disputa fue enviada</h2>
                        <p class="text-sm text-gray-600 mb-3">
                            Enviada el {{ \Carbon\Carbon::parse($dispute->created_at)->format('d M Y H:i') }}
                        </p>
                        <div class="rounded-lg bg-gray-50 border p-4 text-sm text-gray-700 whitespace-pre-line mb-4">{{ $dispute->observations }}</div>
                        @if($dispute->images->count())
                            <p class="text-sm font-medium text-gray-600 mb-2">Evidencia adjuntada ({{ $dispute->images->count() }} imagen/es):</p>
                            <div class="flex flex-wrap gap-2">
                                @foreach($dispute->images as $img)
                                    <a href="{{ Storage::url($img->path) }}" target="_blank">
                                        <img src="{{ Storage::url($img->path) }}" alt="{{ $img->original_name }}"
                                             class="h-20 w-20 rounded object-cover border hover:opacity-80">
                                    </a>
                                @endforeach
                            </div>
                        @endif
                        @if($dispute->admin_resolution)
                            <div class="mt-4 rounded-lg p-4 {{ $dispute->admin_resolution === 'approved' ? 'bg-red-50 border border-red-200' : 'bg-green-50 border border-green-200' }}">
                                <p class="text-sm font-semibold {{ $dispute->admin_resolution === 'approved' ? 'text-red-700' : 'text-green-700' }}">
                                    {{ $dispute->admin_resolution === 'approved' ? '✓ Disputa aprobada — RMA rechazada' : '✗ Disputa rechazada — RMA aceptada' }}
                                </p>
                                @if($dispute->admin_notes)
                                    <p class="mt-1 text-sm text-gray-600">{{ $dispute->admin_notes }}</p>
                                @endif
                            </div>
                        @endif
                    </div>
                @endif
            @endif

            {{-- ── Cambiar Estado del RMA ──────────────────────────────────────── --}}
            {{-- Solo visible para RMA estándar con resolución exchange o cancel-items --}}
            @php
                $rmaResolutionForStatus = $productDetails['0']?->resolution ?? '';
                $currentStatus          = $rmaData->rma_status ?? '';
                $esRetracto             = ($rmaData->rma_type ?? '') === 'retracto';
                $esExchange             = $rmaResolutionForStatus === 'exchange';
                $esCancelItems          = $rmaResolutionForStatus === 'cancel-items';
                $tieneDisputa           = isset($dispute) && $dispute !== null;

                // Estados que habilitan el selector por tipo de resolución
                $estadosExchange    = ['Accept', 'Dispatched Package'];
                $estadosCancelItems = ['Accept'];

                $estaEnEstadoValido = $esExchange
                    ? in_array($currentStatus, $estadosExchange)
                    : ($esCancelItems ? in_array($currentStatus, $estadosCancelItems) : false);

                $mostrarCambioEstado = ! $esRetracto && $estaEnEstadoValido && ! $tieneDisputa;

                if ($mostrarCambioEstado) {
                    if ($esExchange) {
                        // Mostrar solo el estado siguiente al actual
                        $statusArr = $currentStatus === 'Accept'
                            ? ['Dispatched Package']
                            : ['Replaced'];
                    } else {
                        $statusArr = ['Item Canceled'];
                    }
                }
            @endphp

            @if ($mostrarCambioEstado)
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold mb-1">Actualizar estado</h2>
                    <p class="text-sm text-gray-500 mb-4">
                        El cliente será notificado automáticamente al guardar el cambio.
                    </p>
                    <form
                        method="POST"
                        action="{{ route('goloba.seller.rma.save.status') }}"
                        class="space-y-4"
                    >
                        @csrf
                        <input type="hidden" name="rma_id" value="{{ $rmaData['id'] }}">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Nuevo estado
                            </label>
                            <select
                                name="rma_status"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                required
                            >
                                @foreach ($statusArr as $status)
                                    <option
                                        value="{{ $status }}"
                                        {{ $rmaData['rma_status'] === $status ? 'selected' : '' }}
                                    >
                                        {{ $status }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button
                            type="submit"
                            class="primary-button px-6 py-2.5"
                        >
                            Guardar estado
                        </button>
                    </form>
                </div>
            @endif

        </div>
    </div>

    <!-- Línea de Tiempo / Historial -->
    <div class="mt-6 bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold mb-4">Historial de Cambios</h2>
        
        <div class="space-y-4">
            <!-- Creación -->
            <div class="flex gap-4">
                <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-blue-100">
                    <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="font-medium">RMA Creada</p>
                    <p class="text-sm text-gray-600">{{ core()->formatDate($rmaData->created_at, 'd M Y H:i') }}</p>
                </div>
            </div>

            <!-- Estado Actual -->
            <div class="flex gap-4">
                <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full 
                    {{ $rmaData->rma_status === 'Pending' ? 'bg-yellow-100' : '' }}
                    {{ $rmaData->rma_status === 'Accept' ? 'bg-green-100' : '' }}
                    {{ $rmaData->rma_status === 'Declined' ? 'bg-red-100' : '' }}
                ">
                    <svg class="h-5 w-5
                        {{ $rmaData->rma_status === 'Pending' ? 'text-yellow-600' : '' }}
                        {{ $rmaData->rma_status === 'Accept' ? 'text-green-600' : '' }}
                        {{ $rmaData->rma_status === 'Declined' ? 'text-red-600' : '' }}
                    " fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="font-medium">Estado: {{ $rmaData->rma_status }}</p>
                    <p class="text-sm text-gray-600">{{ core()->formatDate($rmaData->updated_at, 'd M Y H:i') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Chat / Mensajes con Vue.js -->
    <div class="mt-6 bg-white rounded-lg shadow">
        <script type="text/x-template" id="rma-chat-template">
            <div>
                <div class="border-b p-6">
                    <h2 class="text-lg font-semibold">Conversación con el Cliente</h2>
                    <p class="text-sm text-gray-600 mt-1">Comunícate directamente con el cliente sobre esta RMA</p>
                </div>

                <!-- Lista de Mensajes -->
                <div 
                    class="p-6 space-y-4 max-h-96 overflow-y-auto"
                    ref="messagesContainer"
                    :class="! messages.length ? 'flex justify-center items-center' : ''"
                >
                    <div v-if="messages.length">
                        <div
                            v-for="message in messages"
                            :key="message.id"
                            class="border rounded-lg p-4"
                            :class="getMessageClasses(message)"
                        >
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-900">@{{ getSenderLabel(message) }}</span>
                                <span class="text-xs text-gray-500">@{{ formatDate(message.created_at) }}</span>
                            </div>
                            <p class="text-gray-700 whitespace-pre-line">@{{ message.message }}</p>
                            
                            <div v-if="message.attachment" class="mt-2">
                                <a 
                                    :href="'{{ config('app.url') }}/storage/' + message.attachment_path" 
                                    target="_blank" 
                                    class="inline-flex items-center gap-2 text-sm text-blue-600 hover:text-blue-800"
                                >
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                    </svg>
                                    @{{ message.attachment }}
                                </a>
                            </div>
                        </div>
                    </div>

                    <div v-else>
                        <div class="flex flex-col items-center justify-center py-8 text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                            <p class="mt-2">No hay mensajes aún</p>
                            <p class="text-sm">Sé el primero en enviar un mensaje</p>
                        </div>
                    </div>
                </div>

                <!-- Formulario de Envío -->
                <div class="border-t p-6">
                    <x-shop::form
                        v-slot="{ meta, errors, handleSubmit }"
                        as="div"
                    >
                        <form 
                            @submit="handleSubmit($event, chatSubmit)"
                            ref="chatForm"
                            enctype="multipart/form-data"
                            class="space-y-4"
                        >
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Tu Mensaje
                                </label>
                                <x-shop::form.control-group.control
                                    type="textarea"
                                    name="message"
                                    v-model="messageText"
                                    rules="required"
                                    :label="'Mensaje'"
                                    :placeholder="'Escribe tu mensaje aquí...'"
                                    rows="3"
                                />
                                <x-shop::form.control-group.error control-name="message" />
                            </div>

                            <input type="hidden" name="rma_id" :value="rmaId" />

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Adjuntar Archivo (Opcional)
                                </label>
                                <input 
                                    type="file" 
                                    name="file"
                                    ref="fileInput"
                                    class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                                >
                                <p class="mt-1 text-xs text-gray-500">Máximo 10MB</p>
                            </div>

                            <div class="flex items-center gap-4">
                                <button 
                                    type="submit"
                                    class="primary-button px-6 py-2.5"
                                    :disabled="isSending"
                                >
                                    <span v-if="!isSending">Enviar Mensaje</span>
                                    <span v-else class="flex items-center gap-2">
                                        <svg class="animate-spin h-5 w-5 text-white inline" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Enviando...
                                    </span>
                                </button>
                                <button 
                                    type="button"
                                    @click="loadMessages"
                                    class="secondary-button px-6 py-2.5"
                                >
                                    Actualizar Chat
                                </button>
                            </div>
                        </form>
                    </x-shop::form>
                </div>
            </div>
        </script>

        <rma-chat></rma-chat>
    </div>

    @push('scripts')
        <script type="text/x-template" id="rma-chat-template">
            <div>
                <div class="border-b p-6">
                    <h2 class="text-lg font-semibold">Conversación con el Cliente</h2>
                    <p class="text-sm text-gray-600 mt-1">Comunícate directamente con el cliente sobre esta RMA</p>
                </div>

                <!-- Lista de Mensajes -->
                <div 
                    class="p-6 space-y-4 max-h-96 overflow-y-auto"
                    ref="messagesContainer"
                    :class="! messages.length ? 'flex justify-center items-center' : ''"
                >
                    <div v-if="messages.length">
                        <div
                            v-for="message in messages"
                            :key="message.id"
                            class="border rounded-lg p-4"
                            :class="getMessageClasses(message)"
                        >
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-900">@{{ getSenderLabel(message) }}</span>
                                <span class="text-xs text-gray-500">@{{ formatDate(message.created_at) }}</span>
                            </div>
                            <p class="text-gray-700 whitespace-pre-line">@{{ message.message }}</p>
                            
                            <div v-if="message.attachment" class="mt-2">
                                <a 
                                    :href="'{{ config('app.url') }}/storage/' + message.attachment_path" 
                                    target="_blank" 
                                    class="inline-flex items-center gap-2 text-sm text-blue-600 hover:text-blue-800"
                                >
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                    </svg>
                                    @{{ message.attachment }}
                                </a>
                            </div>
                        </div>
                    </div>

                    <div v-else>
                        <div class="flex flex-col items-center justify-center py-8 text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                            <p class="mt-2">No hay mensajes aún</p>
                            <p class="text-sm">Sé el primero en enviar un mensaje</p>
                        </div>
                    </div>
                </div>

                <!-- Formulario de Envío -->
                <div class="border-t p-6">
                    <x-shop::form
                        v-slot="{ meta, errors, handleSubmit }"
                        as="div"
                    >
                        <form 
                            @submit="handleSubmit($event, chatSubmit)"
                            ref="chatForm"
                            enctype="multipart/form-data"
                            class="space-y-4"
                        >
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Tu Mensaje
                                </label>
                                <x-shop::form.control-group.control
                                    type="textarea"
                                    name="message"
                                    v-model="messageText"
                                    rules="required"
                                    :label="'Mensaje'"
                                    :placeholder="'Escribe tu mensaje aquí...'"
                                    rows="3"
                                />
                                <x-shop::form.control-group.error control-name="message" />
                            </div>

                            <input type="hidden" name="rma_id" :value="rmaId" />

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Adjuntar Archivo (Opcional)
                                </label>
                                <input 
                                    type="file" 
                                    name="file"
                                    ref="fileInput"
                                    class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                                >
                                <p class="mt-1 text-xs text-gray-500">Máximo 10MB</p>
                            </div>

                            <div class="flex items-center gap-4">
                                <button 
                                    type="submit"
                                    class="primary-button px-6 py-2.5"
                                    :disabled="isSending"
                                >
                                    <span v-if="!isSending">Enviar Mensaje</span>
                                    <span v-else class="flex items-center gap-2">
                                        <svg class="animate-spin h-5 w-5 text-white inline" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Enviando...
                                    </span>
                                </button>
                                <button 
                                    type="button"
                                    @click="loadMessages"
                                    class="secondary-button px-6 py-2.5"
                                >
                                    Actualizar Chat
                                </button>
                            </div>
                        </form>
                    </x-shop::form>
                </div>
            </div>
        </script>

        <script type="module">
            app.component('rma-chat', {
                template: '#rma-chat-template',

                data() {
                    return {
                        messages: [],
                        messageText: '',
                        rmaId: {{ $rmaData->id }},
                        isSending: false,
                    };
                },

                mounted() {
                    console.log('RMA Chat component mounted', this.rmaId);
                    this.loadMessages();
                    
                    // Auto-actualizar cada 30 segundos
                    setInterval(() => {
                        this.loadMessages();
                    }, 30000);
                },

                methods: {
                    loadMessages() {
                        console.log('Loading messages...');
                        this.$axios.get('{{ route('goloba.seller.rma.messages') }}', {
                            params: {
                                rma_id: this.rmaId
                            }
                        })
                        .then(response => {
                            console.log('Messages loaded:', response.data);
                            this.messages = response.data.messages;
                            this.$nextTick(() => {
                                this.scrollToBottom();
                            });
                        })
                        .catch(error => {
                            console.error('Error loading messages:', error);
                            this.$emitter.emit('add-flash', { 
                                type: 'error', 
                                message: 'Error al cargar los mensajes' 
                            });
                        });
                    },

                    chatSubmit(params, { resetForm, setErrors }) {
                        this.isSending = true;
                        
                        let formData = new FormData(this.$refs.chatForm);
                        
                        this.$axios.post('{{ route('goloba.seller.rma.send_message') }}', formData)
                            .then(response => {
                                if (response.data.success) {
                                    this.messageText = '';
                                    if (this.$refs.fileInput) {
                                        this.$refs.fileInput.value = '';
                                    }
                                    
                                    this.$emitter.emit('add-flash', { 
                                        type: 'success', 
                                        message: 'Mensaje enviado correctamente' 
                                    });
                                    
                                    this.loadMessages();
                                    resetForm();
                                }
                            })
                            .catch(error => {
                                console.error('Error sending message:', error);
                                this.$emitter.emit('add-flash', { 
                                    type: 'error', 
                                    message: 'Error al enviar el mensaje' 
                                });
                            })
                            .finally(() => {
                                this.isSending = false;
                            });
                    },

                    getSenderLabel(message) {
                        if (message.is_admin == 1) return 'Admin';
                        if (message.is_seller == 1) return 'Tú (Vendedor)';
                        return 'Cliente';
                    },

                    getMessageClasses(message) {
                        if (message.is_admin == 1) {
                            return 'bg-purple-50 border-purple-200';
                        }
                        if (message.is_seller == 1) {
                            return 'bg-blue-50 border-blue-200';
                        }
                        return 'bg-gray-50 border-gray-200';
                    },

                    formatDate(dateString) {
                        const date = new Date(dateString);
                        return date.toLocaleDateString('es-CO', { 
                            day: '2-digit', 
                            month: 'short', 
                            year: 'numeric'
                        }) + ' ' + date.toLocaleTimeString('es-CO', {
                            hour: '2-digit', 
                            minute: '2-digit'
                        });
                    },

                    scrollToBottom() {
                        if (this.$refs.messagesContainer) {
                            this.$refs.messagesContainer.scrollTop = this.$refs.messagesContainer.scrollHeight;
                        }
                    }
                }
            });
        </script>
    </div>

    {{-- Preview de imágenes del formulario de disputa --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const input   = document.getElementById('dispute-images');
            const preview = document.getElementById('dispute-image-preview');
            if (!input || !preview) return;

            input.addEventListener('change', function () {
                preview.innerHTML = '';
                const files = Array.from(this.files).slice(0, 10);
                files.forEach(file => {
                    if (!file.type.startsWith('image/')) return;
                    const reader = new FileReader();
                    reader.onload = e => {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.className = 'h-20 w-20 rounded object-cover border';
                        img.title = file.name;
                        preview.appendChild(img);
                    };
                    reader.readAsDataURL(file);
                });
            });
        });
    </script>

    @endpush

</x-marketplace::shop.layouts>
