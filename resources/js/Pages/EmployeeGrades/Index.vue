<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import DateRangeFilter from '@/Components/Core/DateRangeFilter.vue'

const props = defineProps({
    courseData: Array,
    filters: Object, // ожидаем { date_start, date_end, ... }
})

function getRatingRoute(node, rating) {
    return {
        ...(node.children?.length
            ? { course_id: node.data.course_id }
            : { lesson_id: node.data.lesson_id }),

        rating,

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
    <Head title="Уроки" />

    <AuthenticatedLayout>
        <div class="py-4 mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-4">
            <!-- Фильтр дат (snake_case) -->
            <DateRangeFilter
                routeName="lesson-grades.index"
                :date_start="filters?.date_start"
                :date_end="filters?.date_end"
            />

            <!-- Таблица -->
            <div class="bg-white rounded-lg p-1">
               test
            </div>
        </div>
    </AuthenticatedLayout>
</template>
