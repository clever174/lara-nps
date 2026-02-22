<!-- resources/js/Pages/Students/Stats.vue -->
<script setup>
import { Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import DateRangeFilter from '@/Components/Core/DateRangeFilter.vue'

const props = defineProps({
    filters: Object, // { date_start, date_end }
    stats: Array,    // [{ attempt, qty, avg }]
})

function fmtAvg(v) {
    const n = Number(v ?? 0)
    return n.toFixed(2)
}
</script>

<template>
    <Head title="Статистика попыток" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight">
                Статистика попыток
            </h2>
        </template>

        <div class="py-6 space-y-4">
            <!-- только фильтр (он меняет query date_start/date_end) -->
            <DateRangeFilter
                :filters="filters"
                route-name="students.stats"
            />

            <div class="bg-white shadow-sm rounded-2xl p-4">
                <DataTable
                    :value="stats"
                    dataKey="attempt"
                    stripedRows
                    showGridlines
                    responsiveLayout="scroll"
                    :emptyMessage="'Нет данных за выбранный период'"
                >
                    <Column field="attempt" header="№ попытки" style="width: 140px" />
                    <Column field="qty" header="Количество" style="width: 160px" />
                    <Column field="avg" header="Средняя оценка">
                        <template #body="{ data }">
                            {{ fmtAvg(data.avg) }}
                        </template>
                    </Column>
                </DataTable>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
