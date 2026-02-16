<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import {Head, Link, router, usePage} from '@inertiajs/vue3'
import { computed } from 'vue'
import { GETCOURSE_URL } from '@/Utils/globals.js'

const props = defineProps({
    students: Object,
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
        route('mi.students.index'),
        {
            ...query.value,         // ✅ сохраняем course_id / lesson_id / rating / date_start / date_end
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
        route('mi.students.index'),
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
<!--            <div class="bg-white rounded-lg shadow p-4">-->
<!--                <h2 class="font-bold">-->
<!--                    Курс: {{ students.data[0]?.course_title }}-->
<!--                </h2>-->
<!--            </div>-->

            <div class="py-4">
                <div v-if="students.data?.length === 0" class="text-gray-600">
                    Записей пока нет
                </div>

                <div v-else class="bg-white rounded-lg p-1">
                    <DataTable
                        :value="students.data"
                        tableStyle="min-width: 50rem"
                        lazy
                        paginator
                        :rows="students.per_page"
                        :totalRecords="students.total"
                        :first="(students.current_page - 1) * students.per_page"
                        :rowsPerPageOptions="[10, 20, 50, 100]"
                        @page="onPage"
                        @sort="onSort"
                        :sortField="sortField"
                        :sortOrder="sortOrder"
                        removableSort
                        paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown CurrentPageReport"
                        currentPageReportTemplate="Всего {totalRecords}"
                        scrollable
                        :scrollHeight="'calc(100vh - 180px)'"
                    >
                        <Column field="fio" sortable style="width: 10%">
                            <template #header>
                                <span class="flex-1 text-center font-bold">Ученик</span>
                            </template>
                            <template #body="{ data }">
                                <div class="w-full text-center">
                                    <a
                                        class="text-blue-500 font-bold"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        :href="`${GETCOURSE_URL}/user/control/user/update/id/${data.user_id}`"
                                    >
                                        {{ data.fio }}
                                    </a>
                                </div>
                            </template>
                        </Column>

                        <Column field="grade_count" sortable style="width: 10%">
                            <template #header>
                                <span class="flex-1 text-center font-bold">Количество оценок</span>
                            </template>
                            <template #body="{ data }">
                                <div class="w-full text-center">
                                    {{ data.grade_count }}
                                </div>
                            </template>
                        </Column>

                        <Column field="avg_grade" sortable style="width: 10%">
                            <template #header>
                                <span class="flex-1 text-center font-bold">Средняя оценка</span>
                            </template>
                            <template #body="{ data }">
                                <div class="w-full text-center">
                                    {{ data.avg_grade }}
                                </div>
                            </template>
                        </Column>

                        <Column field="updated_at" sortable style="width: 10%">
                            <template #header>
                                <span class="flex-1 text-center font-bold">Дата</span>
                            </template>
                            <template #body="{ data }">
                                <div class="w-full text-center">
                                    {{ data.updated_at_ru }}
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
                                        :href="route('mi.students.show', data.id)"
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
        </div>
    </AuthenticatedLayout>
</template>
