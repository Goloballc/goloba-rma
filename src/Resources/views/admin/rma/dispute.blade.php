<x-admin::layouts>
    <x-slot:title>
        Disputa RMA #{{ $rma->id }}
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <div class="flex items-center gap-2.5">
            <p class="text-xl font-bold leading-6 text-gray-800 dark:text-white">
                Disputa — RMA #{{ $rma->id }}
            </p>
            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium
                {{ $dispute->isPending()  ? 'bg-yellow-500 text-white' : '' }}
                {{ $dispute->isApproved() ? 'bg-red-600 text-white'    : '' }}
                {{ $dispute->isRejected() ? 'bg-green-600 text-white'  : '' }}">
                {{ $dispute->isPending()  ? 'Pendiente'                  : '' }}
                {{ $dispute->isApproved() ? 'Aprobada — RMA rechazada'   : '' }}
                {{ $dispute->isRejected() ? 'Rechazada — RMA aceptada'   : '' }}
            </span>
        </div>
        <a href="{{ route('admin.sales.rma.view', $rma->id) }}"
           class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800">
            ← Volver al RMA
        </a>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">

        {{-- Columna principal --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Observaciones --}}
            <div class="rounded-xl border bg-white dark:bg-gray-900 p-6">
                <h2 class="text-base font-semibold text-gray-800 dark:text-white mb-1">
                    Observaciones del vendedor
                </h2>
                <p class="text-xs text-gray-400 mb-3">
                    Enviada el {{ \Carbon\Carbon::parse($dispute->created_at)->format('d M Y H:i') }}
                </p>
                <div class="rounded-lg bg-gray-50 dark:bg-gray-800 border p-4 text-sm
                            text-gray-700 dark:text-gray-300 whitespace-pre-line leading-relaxed">
                    {{ $dispute->observations }}
                </div>
            </div>

            {{-- Evidencia fotográfica --}}
            @if($dispute->images->count())
            <div class="rounded-xl border bg-white dark:bg-gray-900 p-6">
                <h2 class="text-base font-semibold text-gray-800 dark:text-white mb-4">
                    Evidencia fotográfica
                    <span class="text-xs font-normal text-gray-400">({{ $dispute->images->count() }} imagen/es)</span>
                </h2>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4">
                    @foreach($dispute->images as $img)
                        <a href="{{ Storage::url($img->path) }}" target="_blank" rel="noopener">
                            <img
                                src="{{ Storage::url($img->path) }}"
                                alt="{{ $img->original_name }}"
                                title="{{ $img->original_name }}"
                                class="h-36 w-full rounded-lg object-cover border hover:opacity-80 transition"
                            >
                            <p class="mt-1 text-xs text-gray-400 truncate">{{ $img->original_name }}</p>
                        </a>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Resolución --}}
            @if($dispute->isPending())
            <div class="rounded-xl border bg-white dark:bg-gray-900 p-6">
                <h2 class="text-base font-semibold text-gray-800 dark:text-white mb-1">
                    Resolución
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                    Decide si la disputa del vendedor es válida.
                    <strong>Aprobar</strong> marca la RMA como Rechazada (el vendedor tiene razón).
                    <strong>Rechazar</strong> la disputa continúa la RMA como Aceptada (el cliente tiene razón).
                </p>
                <form
                    method="POST"
                    action="{{ url(config('app.admin_url') . '/rma/disputes/' . $rma->id . '/resolve') }}"
                    onsubmit="return confirm('¿Confirmas tu decisión? Esta acción cambiará el estado de la RMA.')"
                    class="space-y-4"
                >
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Notas para vendedor y cliente
                            <span class="font-normal text-gray-400">(Opcional)</span>
                        </label>
                        <textarea
                            name="admin_notes"
                            rows="4"
                            maxlength="2000"
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600
                                   dark:bg-gray-800 dark:text-white p-3 text-sm
                                   focus:border-blue-500 focus:ring-blue-500"
                            placeholder="Explica brevemente el motivo de tu decisión..."
                        ></textarea>
                    </div>

                    <div class="flex gap-3">
                        <button
                            type="submit" name="resolution" value="approved"
                            class="flex-1 rounded-lg bg-red-600 px-4 py-3 text-sm font-semibold
                                   text-white hover:bg-red-700 transition"
                        >
                            ✓ Aprobar disputa — Rechazar RMA
                        </button>
                        <button
                            type="submit" name="resolution" value="rejected"
                            class="flex-1 rounded-lg bg-green-600 px-4 py-3 text-sm font-semibold
                                   text-white hover:bg-green-700 transition"
                        >
                            ✗ Rechazar disputa — Continuar RMA
                        </button>
                    </div>
                </form>
            </div>
            @else
            <div class="rounded-xl border p-6
                        {{ $dispute->isApproved() ? 'border-red-200 bg-red-50 dark:bg-red-900/20' : 'border-green-200 bg-green-50 dark:bg-green-900/20' }}">
                <p class="text-sm font-semibold
                          {{ $dispute->isApproved() ? 'text-red-700 dark:text-red-400' : 'text-green-700 dark:text-green-400' }}">
                    {{ $dispute->isApproved() ? '✓ Disputa aprobada — RMA rechazada' : '✗ Disputa rechazada — RMA aceptada' }}
                </p>
                <p class="mt-1 text-xs text-gray-400">
                    Resuelta el {{ \Carbon\Carbon::parse($dispute->resolved_at)->format('d M Y H:i') }}
                </p>
                @if($dispute->admin_notes)
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">{{ $dispute->admin_notes }}</p>
                @endif
            </div>
            @endif

        </div>{{-- fin columna principal --}}

        {{-- Columna lateral: resumen del RMA --}}
        <div class="space-y-4">
            <div class="rounded-xl border bg-white dark:bg-gray-900 p-6">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-white mb-3 uppercase tracking-wide">
                    Resumen del RMA
                </h3>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-gray-500">RMA</dt>
                        <dd class="font-medium text-gray-800 dark:text-white">#{{ $rma->id }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Orden</dt>
                        <dd class="font-medium text-gray-800 dark:text-white">#{{ $rma->order_id }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Tipo</dt>
                        <dd>
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
                                {{ $rma->rma_type === 'retracto' ? 'bg-blue-600 text-white' : 'bg-gray-600 text-white' }}">
                                {{ $rma->rma_type === 'retracto' ? 'Derecho de Retracto' : 'Estándar' }}
                            </span>
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Estado actual</dt>
                        <dd>
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-yellow-500 text-white">
                                {{ $rma->rma_status }}
                            </span>
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Creado</dt>
                        <dd class="text-gray-600 dark:text-gray-300">
                            {{ \Carbon\Carbon::parse($rma->created_at)->format('d M Y') }}
                        </dd>
                    </div>
                </dl>
                <div class="mt-4 border-t pt-4">
                    <a href="{{ route('admin.sales.rma.view', $rma->id) }}"
                       class="block w-full rounded-lg border border-gray-300 px-4 py-2 text-center
                              text-sm font-medium text-gray-700 hover:bg-gray-50 dark:text-white
                              dark:border-gray-600 dark:hover:bg-gray-800 transition">
                        Ver RMA completo
                    </a>
                </div>
            </div>
        </div>

    </div>{{-- fin grid --}}

</x-admin::layouts>
