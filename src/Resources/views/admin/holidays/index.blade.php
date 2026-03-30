<x-admin::layouts>
    <x-slot:title>
        Festivos Colombianos
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            Festivos Colombianos
        </p>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">

        {{-- Tabla de años cargados --}}
        <div class="box-shadow rounded-lg bg-white p-6 dark:bg-gray-900">
            <p class="mb-4 text-base font-semibold text-gray-700 dark:text-gray-300">
                Años cargados en base de datos
            </p>

            @if ($yearCounts->isEmpty())
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    No hay festivos cargados aún.
                </p>
            @else
                <v-holidays-index
                    :year-counts="{{ json_encode($yearCounts) }}"
                    base-url="{{ url(config('app.admin_url') . '/rma/festivos') }}"
                ></v-holidays-index>
            @endif

            <p class="mt-4 text-xs text-gray-400 dark:text-gray-500">
                Para agregar un año nuevo, sube el CSV correspondiente desde el formulario.
            </p>
        </div>

        {{-- Formulario de importación --}}
        <div class="box-shadow rounded-lg bg-white p-6 dark:bg-gray-900">
            <p class="mb-1 text-base font-semibold text-gray-700 dark:text-gray-300">
                Importar festivos desde CSV
            </p>
            <p class="mb-4 text-xs text-gray-400 dark:text-gray-500">
                El CSV debe tener dos columnas: <code>nombre</code> y <code>fecha</code> (formato <code>YYYY-MM-DD</code>).
                Solo se insertarán fechas que no existan en BD — las existentes se omiten sin error.
            </p>

            <v-holidays-import>
                <div class="h-24 animate-pulse rounded-md bg-gray-100 dark:bg-gray-800"></div>
            </v-holidays-import>
        </div>

    </div>

    @pushOnce('scripts')
        {{-- Template: tabla de años con botón eliminar año --}}
        <script type="text/x-template" id="v-holidays-index-template">
            <div>
                <table class="w-full text-sm text-gray-600 dark:text-gray-300">
                    <thead>
                        <tr class="border-b dark:border-gray-700">
                            <th class="pb-2 text-left font-semibold">Año</th>
                            <th class="pb-2 text-left font-semibold">Festivos</th>
                            <th class="pb-2 text-right font-semibold">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="row in localYears" :key="row.year" class="border-b dark:border-gray-800">
                            <td class="py-2">
                                <a :href="`${baseUrl}/${row.year}`"
                                   class="font-medium text-blue-600 hover:underline dark:text-blue-400"
                                   v-text="row.year"></a>
                            </td>
                            <td class="py-2" v-text="row.total"></td>
                            <td class="py-2 text-right">
                                <button
                                    class="rounded px-2 py-1 text-xs text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20"
                                    :disabled="deleting === row.year"
                                    @click="confirmDelete(row)"
                                >
                                    <span v-if="deleting === row.year">Eliminando…</span>
                                    <span v-else>Eliminar año</span>
                                </button>
                            </td>
                        </tr>
                        <tr v-if="localYears.length === 0">
                            <td colspan="3" class="py-6 text-center text-gray-400">No hay festivos cargados.</td>
                        </tr>
                    </tbody>
                </table>
                <teleport to="body">
                    <div v-if="pendingDelete" class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/50">
                        <div class="max-w-sm rounded-xl border border-gray-800 bg-white p-6 shadow-xl dark:bg-gray-900">
                            <p class="text-base font-semibold text-gray-800 dark:text-white">¿Eliminar todos los festivos?</p>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                Se eliminarán los <strong class="text-gray-700 dark:text-gray-200" v-text="pendingDelete.total"></strong>
                                festivos del año <strong class="text-gray-700 dark:text-gray-200" v-text="pendingDelete.year"></strong>.
                            </p>
                            <p class="mt-1 text-xs text-red-500 dark:text-red-400">Esta acción no se puede deshacer.</p>
                            <div class="mt-4 flex justify-end gap-3">
                                <button class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800" @click="pendingDelete = null">Cancelar</button>
                                <button class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700" @click="doDelete">Eliminar</button>
                            </div>
                        </div>
                    </div>
                </teleport>
            </div>
        </script>

        {{-- Template: formulario de importación CSV --}}
        <script type="text/x-template" id="v-holidays-import-template">
            <div>
                <div class="mb-4">
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Archivo CSV</label>
                    <input
                        type="file"
                        accept=".csv"
                        ref="csvInput"
                        @change="onFileChange"
                        class="block w-full text-sm text-gray-600 file:mr-4 file:rounded-md file:border-0 file:bg-blue-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-blue-700 hover:file:bg-blue-100 dark:text-gray-400"
                    />
                </div>
                <button class="primary-button" :disabled="! file || loading" @click="upload">
                    <span v-if="loading">Importando...</span>
                    <span v-else>Importar</span>
                </button>
                <div v-if="result" class="mt-4 rounded-md p-4 text-sm" :class="resultClass">
                    <p class="font-semibold" v-text="result.message"></p>
                    <ul v-if="result.errors && result.errors.length" class="mt-2 list-disc pl-4 text-xs">
                        <li v-for="err in result.errors" :key="err" v-text="err"></li>
                    </ul>
                </div>
            </div>
        </script>

        <script type="module">
            app.component('v-holidays-index', {
                template: '#v-holidays-index-template',
                props: {
                    yearCounts: { type: Array,  required: true },
                    baseUrl:    { type: String, required: true },
                },
                data() {
                    return {
                        localYears:    [...this.yearCounts],
                        deleting:      null,
                        pendingDelete: null,
                    };
                },
                methods: {
                    confirmDelete(row) { this.pendingDelete = row; },
                    async doDelete() {
                        const row = this.pendingDelete;
                        this.pendingDelete = null;
                        this.deleting = row.year;
                        try {
                            const response = await this.$axios.delete(`${this.baseUrl}/${row.year}`);
                            this.localYears = this.localYears.filter(r => r.year !== row.year);
                            this.$emitter.emit('add-flash', { type: 'success', message: response.data.message });
                        } catch (error) {
                            const msg = error.response?.data?.message ?? 'Error al eliminar.';
                            this.$emitter.emit('add-flash', { type: 'error', message: msg });
                        } finally {
                            this.deleting = null;
                        }
                    },
                },
            });

            app.component('v-holidays-import', {
                template: '#v-holidays-import-template',
                data() {
                    return { file: null, loading: false, result: null };
                },
                computed: {
                    resultClass() {
                        if (! this.result) return '';
                        return this.result.errors && this.result.errors.length
                            ? 'bg-yellow-50 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200'
                            : 'bg-green-50 text-green-800 dark:bg-green-900 dark:text-green-200';
                    },
                },
                methods: {
                    onFileChange(e) { this.file = e.target.files[0] ?? null; this.result = null; },
                    async upload() {
                        if (! this.file) return;
                        this.loading = true;
                        this.result  = null;
                        const formData = new FormData();
                        formData.append('csv', this.file);
                        try {
                            const response = await this.$axios.post(
                                '{{ route('admin.rma.holidays.import') }}',
                                formData,
                                { headers: { 'Content-Type': 'multipart/form-data' } }
                            );
                            this.result = response.data;
                            this.$emitter.emit('add-flash', { type: 'success', message: response.data.message });
                            setTimeout(() => window.location.reload(), 1500);
                        } catch (error) {
                            const msg = error.response?.data?.message ?? 'Error al importar el archivo.';
                            this.result = { message: msg, errors: [] };
                            this.$emitter.emit('add-flash', { type: 'error', message: msg });
                        } finally {
                            this.loading = false;
                        }
                    },
                },
            });
        </script>
    @endPushOnce
</x-admin::layouts>
