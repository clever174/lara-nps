<!-- resources/js/Pages/Mi/Students/Stats.vue -->
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head } from '@inertiajs/vue3'
import DateRangeFilter from '@/Components/Core/DateRangeFilter.vue'

// PrimeVue
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'

const props = defineProps({
    stats: Array,   // [{ attempt, qty, avg }]
    filters: Object // { date_start, date_end }
})

function fmtAvg(v) {
    const n = Number(v ?? 0)
    return n.toFixed(2)
}
</script>

<template>
    <Head title="Статистика попыток" />

    <AuthenticatedLayout>
        <div class="py-4 mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-4">
            <!-- Фильтр дат (snake_case) -->
            <DateRangeFilter
                routeName="mi.students.stats"
                :date_start="filters?.date_start"
                :date_end="filters?.date_end"
            />

            <!-- Таблица (только вывод, без server-side, без вычислений) -->
            <div class="bg-white rounded-lg p-1">
                <DataTable
                    :value="stats"
                    tableStyle=""
                    stripedRows
                    responsiveLayout="scroll"
                    dataKey="attempt"
                    :emptyMessage="'Нет данных за выбранный период'"
                >
                    <Column field="attempt" style="width: 33%">
                        <template #header>
                            <span class="flex-1 text-center font-bold">№ попытки</span>
                        </template>
                        <template #body="{ data }">
                            <div class="w-full text-center font-semibold">
                                {{ data.attempt }}
                            </div>
                        </template>
                    </Column>

                    <Column field="qty" style="width: 33%">
                        <template #header>
                            <span class="flex-1 text-center font-bold">Количество</span>
                        </template>
                        <template #body="{ data }">
                            <div class="w-full text-center">
                                {{ data.qty }}
                            </div>
                        </template>
                    </Column>

                    <Column field="avg" style="width: 33%">
                        <template #header>
                            <span class="flex-1 text-center font-bold">Средняя оценка</span>
                        </template>
                        <template #body="{ data }">
                            <div class="w-full text-center">
                                {{ fmtAvg(data.avg) }}
                            </div>
                        </template>
                    </Column>
                </DataTable>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
