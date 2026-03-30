<x-admin::layouts>
    <x-slot:title>
        Festivos {{ $year }}
    </x-slot>

    {{-- Encabezado --}}
    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <div class="flex items-center gap-3">
            <a href="{{ url(config('app.admin_url') . '/rma/festivos') }}"
               class="flex items-center gap-1 text-sm text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-white">
                <span class="icon-arrow-left text-lg"></span>
                Volver
            </a>
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                Festivos Colombianos — {{ $year }}
            </p>
        </div>
        <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-800 dark:bg-blue-900 dark:text-blue-200">
            {{ $holidays->count() }} festivo(s)
        </span>
    </div>

    {{-- Tabla de festivos --}}
    <div class="mt-6 box-shadow rounded-lg bg-white p-6 dark:bg-gray-900">
        <v-holidays-show
            :holidays="{{ json_encode($holidays) }}"
            :year="{{ $year }}"
            delete-url-base="{{ url(config('app.admin_url') . '/rma/festivos/' . $year) }}"
        ></v-holidays-show>
    </div>

    @pushOnce('scripts')
        <script type="text/x-template" id="v-holidays-show-template">
            <div>
                <table class="w-full text-sm text-gray-600 dark:text-gray-300">
                    <thead>
                        <tr class="border-b dark:border-gray-700">
                            <th class="pb-2 text-left font-semibold">Fecha</th>
                            <th class="pb-2 text-left font-semibold">Día</th>
                            <th class="pb-2 text-left font-semibold">Nombre</th>
                            <th class="pb-2 text-right font-semibold">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="holiday in localHolidays"
                            :key="holiday.date"
                            class="border-b dark:border-gray-800"
                        >
                            <td class="py-2 font-mono text-xs" v-text="holiday.date"></td>
                            <td class="py-2 capitalize" :class="weekdayClass(holiday.date)" v-text="weekdayName(holiday.date)"></td>
                            <td class="py-2" v-text="holiday.name"></td>
                            <td class="py-2 text-right">
                                <button
                                    class="rounded px-2 py-1 text-xs text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20"
                                    :disabled="deleting === holiday.date"
                                    @click="confirmDelete(holiday)"
                                >
                                    <span v-if="deleting === holiday.date">Eliminando…</span>
                                    <span v-else>Eliminar</span>
                                </button>
                            </td>
                        </tr>
                        <tr v-if="localHolidays.length === 0">
                            <td colspan="4" class="py-6 text-center text-gray-400">
                                No quedan festivos para este año.
                            </td>
                        </tr>
                    </tbody>
                </table>

                {{-- Modal de confirmación --}}
                <teleport to="body">
                    <div v-if="pendingDelete" class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/50">
                        <div class="max-w-sm rounded-xl border border-gray-800 bg-white p-6 shadow-xl dark:bg-gray-900">
                            <p class="text-base font-semibold text-gray-800 dark:text-white">¿Eliminar festivo?</p>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                <strong class="text-gray-700 dark:text-gray-200" v-text="pendingDelete.name"></strong>
                                — <span class="text-gray-700 dark:text-gray-200" v-text="pendingDelete.date"></span>
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

        <script type="module">
            app.component('v-holidays-show', {
                template: '#v-holidays-show-template',

                props: {
                    holidays:      { type: Array,  required: true },
                    year:          { type: Number, required: true },
                    deleteUrlBase: { type: String, required: true },
                },

                data() {
                    return {
                        localHolidays: [...this.holidays],
                        deleting:      null,   // date string del festivo en proceso de borrado
                        pendingDelete: null,   // objeto festivo esperando confirmación
                    };
                },

                methods: {
                    weekdayName(dateStr) {
                        const days = ['domingo','lunes','martes','miércoles','jueves','viernes','sábado'];
                        return days[new Date(dateStr + 'T12:00:00').getDay()];
                    },

                    weekdayClass(dateStr) {
                        const day = new Date(dateStr + 'T12:00:00').getDay();
                        // domingo=0, sábado=6
                        return (day === 0 || day === 6)
                            ? 'text-red-500 dark:text-red-400'
                            : 'text-gray-600 dark:text-gray-300';
                    },

                    confirmDelete(holiday) {
                        this.pendingDelete = holiday;
                    },

                    async doDelete() {
                        const holiday = this.pendingDelete;
                        this.pendingDelete = null;
                        this.deleting = holiday.date;

                        try {
                            await this.$axios.delete(
                                `${this.deleteUrlBase}/${holiday.date}`
                            );

                            this.localHolidays = this.localHolidays.filter(
                                h => h.date !== holiday.date
                            );

                            this.$emitter.emit('add-flash', {
                                type: 'success',
                                message: `Festivo "${holiday.name}" eliminado.`,
                            });
                        } catch (error) {
                            const msg = error.response?.data?.message ?? 'Error al eliminar.';
                            this.$emitter.emit('add-flash', { type: 'error', message: msg });
                        } finally {
                            this.deleting = null;
                        }
                    },
                },
            });
        </script>
    @endPushOnce
</x-admin::layouts>
