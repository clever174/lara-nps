<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import DateRangeFilter from '@/Components/Core/DateRangeFilter.vue'

const props = defineProps({
    employeeData: Array,
    filters: Object, // ожидаем { date_start, date_end, ... }
})

function getRatingRoute(data, rating) {
    return {
        employee_name: data.employee_name ,

        rating: rating,

        // ✅ сохраняем даты из текущих фильтров (snake_case)
        date_start: props.filters?.date_start,
        date_end: props.filters?.date_end,
    }
}

function getDetailsRoute(node) {
    return {
        ...(node.children?.length
            ? { course_id: node.data.course_id }
            : { lesson_id: node.data.lesson_id }),

        // ✅ сохраняем даты
        date_start: props.filters?.date_start,
        date_end: props.filters?.date_end,
    }
}
</script>

<template>
    <Head title="Оценка сотрудников" />

    <AuthenticatedLayout>
        <div class="py-4 mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-4">
            <!-- Фильтр дат (snake_case) -->
            <DateRangeFilter
                routeName="employee-grades.index"
                :date_start="filters?.date_start"
                :date_end="filters?.date_end"
            />

            <!-- Таблица -->
            <div class="bg-white rounded-lg p-1">
                <DataTable
                    :value="employeeData"
                    tableStyle="min-width: 50rem"
                >
                    <Column style="width: 35%">
                        <template #header>
                            <span class="flex-1 text-center font-bold">Куратор</span>
                        </template>
                        <template #body="{data }">
                            <div class="w-full text-left">
                                {{ data.employee_name }}
                            </div>
                        </template>
                    </Column>

                    <Column style="width: 25%">
                        <template #header>
                            <span class="flex-1 text-center font-bold">Оценки</span>
                        </template>
                        <template #body="{ data }">
                            <div class="flex justify-center w-full gap-2">
                                <Link
                                    v-for="i in [...Array(10)].map((_, i) => 10 - i)"
                                    :key="i"
                                    :href="route('employee-grades.list', getRatingRoute(data, i))"
                                    class="no-underline"
                                >
                                    <Tag
                                        :value="data['rating_' + i]"
                                        :title="'Оценка ' + i"
                                        :severity="i >= 9 ? 'success' : i >= 7 ? 'warn' : 'danger'"
                                        class="cursor-pointer hover:scale-110 transition-transform"
                                    />
                                </Link>
                            </div>
                        </template>
                    </Column>

                    <Column style="width: 10%">
                        <template #header>
                            <span class="flex-1 text-center font-bold">Количество оценок</span>
                        </template>
                        <template #body="{ data }">
                            <div class="w-full text-center">
                                {{ data.quantity }}
                            </div>
                        </template>
                    </Column>

                    <Column field="avg_rating" style="width: 10%">
                        <template #header>
                            <span class="flex-1 text-center font-bold">Средняя оценка</span>
                        </template>
                        <template #body="{ data }">
                            <div class="w-full text-center">
                                {{ data.avg_rating }}
                            </div>
                        </template>
                    </Column>

                    <Column field="nps" style="width: 10%">
                        <template #header>
                            <span class="flex-1 text-center font-bold">NPS</span>
                        </template>
                        <template #body="{ data }">
                            <div class="w-full text-center">
                                {{ data.nps }}
                            </div>
                        </template>
                    </Column>

                    <Column style="width: 10%">
                        <template #header>
                            <span class="flex-1 text-center font-bold">Подробнее</span>
                        </template>
                        <template #body="{ data }">
                            <div class="w-full text-center">
                                <Link
                                    :href="route('employee-grades.list', {employee_name: data.employee_name})"
                                    class="inline-flex items-center justify-center w-10 h-10 rounded-full text-gray-700 hover:bg-gray-100 transition"
                                    title="Подробнее"
                                >
                                    <i class="pi pi-arrow-circle-right" style="font-size: 1.5rem;"></i>
                                </Link>
                            </div>
                        </template>
                    </Column>
                </DataTable>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
