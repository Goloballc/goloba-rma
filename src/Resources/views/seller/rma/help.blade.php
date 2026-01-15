<x-marketplace::shop.layouts>
    <x-slot:title>
        Crear RMA - Ayuda
    </x-slot>

    <div class="container px-[60px] max-lg:px-8 max-sm:px-4 mt-8">
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
            <h2 class="text-xl font-semibold text-blue-900 mb-4">✅ Tu orden está lista para RMA</h2>
            <p class="text-blue-800 mb-4">La orden 27 está correctamente configurada y puede generar una RMA.</p>
            
            <div class="bg-white rounded p-4 mb-4">
                <h3 class="font-semibold mb-2">Detalles de la Orden 27:</h3>
                <ul class="list-disc list-inside text-sm space-y-1">
                    <li>Order Item ID: 37</li>
                    <li>Marketplace Product ID: 89</li>
                    <li>Seller ID: 3 (azacarias@silcon.tech - uShop)</li>
                </ul>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h3 class="text-lg font-semibold mb-4">📝 Cómo crear una RMA como Cliente</h3>
            
            <div class="space-y-4">
                <div class="border-l-4 border-green-500 pl-4 py-2">
                    <p class="font-semibold text-green-700">Opción 1: Acceso Directo (Recomendado)</p>
                    <p class="text-sm text-gray-600 mt-1">Como cliente, visita directamente:</p>
                    <a href="/customer/account/rma/create" 
                       class="inline-block mt-2 px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                        👉 Ir a Crear RMA
                    </a>
                </div>

                <div class="border-l-4 border-blue-500 pl-4 py-2">
                    <p class="font-semibold text-blue-700">Opción 2: Panel de Cliente</p>
                    <ol class="text-sm text-gray-600 mt-1 list-decimal list-inside space-y-1">
                        <li>Cierra sesión como vendedor</li>
                        <li>Inicia sesión como el cliente que hizo la orden 27</li>
                        <li>Ve a: <code class="bg-gray-100 px-2 py-1 rounded">/customer/account/rma</code></li>
                        <li>Haz clic en "Crear Nueva RMA"</li>
                    </ol>
                </div>

                <div class="border-l-4 border-purple-500 pl-4 py-2">
                    <p class="font-semibold text-purple-700">Opción 3: Script de Prueba</p>
                    <p class="text-sm text-gray-600 mt-1">Crear RMA de prueba directamente en la base de datos:</p>
                    <button onclick="createTestRMA()" 
                            class="mt-2 px-4 py-2 bg-purple-600 text-white rounded hover:bg-purple-700">
                        🔧 Crear RMA de Prueba (Orden 27)
                    </button>
                    <p id="result" class="mt-2 text-sm"></p>
                </div>
            </div>
        </div>

        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-yellow-900 mb-2">⚠️ Nota Importante</h3>
            <p class="text-yellow-800 text-sm">
                Si el menú RMA no aparece en el panel del cliente, puede ser porque:
            </p>
            <ul class="list-disc list-inside text-yellow-800 text-sm mt-2 space-y-1">
                <li>El módulo RMA no está habilitado en la configuración</li>
                <li>El menú no está registrado en el tema actual</li>
                <li>Las rutas fueron cachadas antes de instalar el módulo RMA</li>
            </ul>
            <p class="text-yellow-800 text-sm mt-2">
                Solución: Usa el <strong>Acceso Directo</strong> de la Opción 1 arriba.
            </p>
        </div>
    </div>

    @push('scripts')
    <script>
        function createTestRMA() {
            if (!confirm('¿Crear una RMA de prueba para la orden 27?')) return;
            
            const resultDiv = document.getElementById('result');
            resultDiv.innerHTML = '<span class="text-blue-600">Creando RMA...</span>';
            
            fetch('/vendedor/cuenta/rma/create-test-rma', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ order_id: 27 })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    resultDiv.innerHTML = `<span class="text-green-600">✅ RMA #${data.rma_id} creada exitosamente!</span>`;
                    setTimeout(() => {
                        window.location.href = '/vendedor/cuenta/rma';
                    }, 2000);
                } else {
                    resultDiv.innerHTML = `<span class="text-red-600">❌ Error: ${data.message}</span>`;
                }
            })
            .catch(error => {
                resultDiv.innerHTML = `<span class="text-red-600">❌ Error: ${error.message}</span>`;
            });
        }
    </script>
    @endpush
</x-marketplace::shop.layouts>
