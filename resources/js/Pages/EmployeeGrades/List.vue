<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import { GETCOURSE_URL } from '@/Utils/globals.js'

const props = defineProps({
    employeeGrades: Object,
})

const page = usePage()

const query = computed(() => {
    const url = new URL(page.url, window.location.origin)
    return Object.fromEntries(url.searchParams.entries())
})

const sortField = computed(() => query.value.sort_field || null)
const sortOrder = computed(() => {
    const v = Number(query.value.sort_order)
    return Number.isFinite(v) ? v : null // PrimeVue: 1 | -1 | null
})

const onPage = (event) => {
    router.get(
        route('employee-grades.list'),
        {
            ...query.value,         // сохраняем course_id / lesson_id / rating / date_start / date_end
            page: event.page + 1,   // 0-based -> 1-based
            per_page: event.rows,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    )
}

const onSort = (event) => {
    router.get(
        route('employee-grades.list'),
        {
            ...query.value, // ✅ сохраняем фильтры
            page: 1,
            sort_field: event.sortField ?? null,
            sort_order: event.sortOrder ?? null,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    )
}
</script>

<template>
    <Head title="Оценка уроков" />

    <AuthenticatedLayout>
        <div class="py-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow p-4">
                <h2 class="font-bold">
                    {{ employeeGrades.data[0]?.employee_name }}
                </h2>
            </div>

            <div class="py-4">
                <div v-if="employeeGrades.data?.length === 0" class="text-gray-600">
                    Записей пока нет
                </div>

                <div v-else class="bg-white rounded-lg p-1">
                    <DataTable
                        :value="employeeGrades.data"
                        tableStyle="min-width: 50rem"
                        lazy
                        paginator
                        :rows="employeeGrades.per_page"
                        :totalRecords="employeeGrades.total"
                        :first="(employeeGrades.current_page - 1) * employeeGrades.per_page"
                        :rowsPerPageOptions="[10, 20, 50, 100]"
                        @page="onPage"
                        @sort="onSort"
                        :sortField="sortField"
                        :sortOrder="sortOrder"
                        removableSort
                        paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown CurrentPageReport"
                        currentPageReportTemplate="Всего {totalRecords}"
                        scrollable
                        :scrollHeight="'calc(100vh - 230px)'"
                    >
                        <Column field="user_id" sortable style="width: 10%">
                            <template #header>
                                <span class="flex-1 text-center font-bold">Пользователь</span>
                            </template>
                            <template #body="{ data }">
                                <div class="w-full text-center">
                                    <a
                                        class="text-blue-500"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        :href="`${GETCOURSE_URL}/user/control/user/update/id/${data.user_id}`"
                                    >
                                        {{ data.user_id }}
                                    </a>
                                </div>
                            </template>
                        </Column>

                        <Column field="course_title" sortable style="width: 20%">
                            <template #header>
                                <span class="flex-1 text-center font-bold">Курс</span>
                            </template>
                            <template #body="{ data }">
                                <div class="w-full">
                                    {{ data.course_title }}
                                </div>
                            </template>
                        </Column>

                        <Column style="width: 30%">
                            <template #header>
                                <span class="flex-1 text-center font-bold">Комментарий</span>
                            </template>
                            <template #body="{ data }">
                                <div class="w-full">
                                    {{ data.comment }}
                                </div>
                            </template>
                        </Column>

                        <!-- Сортируем по created_at, но показываем created_at_ru -->
                        <Column field="created_at" sortable style="width: 10%">
                            <template #header>
                                <span class="flex-1 text-center font-bold">Дата</span>
                            </template>
                            <template #body="{ data }">
                                <div class="w-full text-center">
                                    {{ data.created_at_ru }}
                                </div>
                            </template>
                        </Column>

                        <Column field="rating" sortable style="width: 10%">
                            <template #header>
                                <span class="flex-1 text-center font-bold">Оценка</span>
                            </template>
                            <template #body="{ data }">
                                <div class="w-full text-center">
                                    <Tag
                                        :value="data.rating"
                                        :title="'Оценка ' + data.rating"
                                        :severity="data.rating >= 9 ? 'success' : data.rating >= 7 ? 'warn' : 'danger'"
                                        class="cursor-pointer hover:scale-110 transition-transform"
                                    />
                                </div>
                            </template>
                        </Column>
                    </DataTable>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
