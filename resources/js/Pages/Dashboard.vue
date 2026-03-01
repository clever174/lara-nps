<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

// PrimeVue
import Card from 'primevue/card'
import Button from 'primevue/button'
import Divider from 'primevue/divider'
import Tag from 'primevue/tag'
import ProgressBar from 'primevue/progressbar'
import Chart from 'primevue/chart'

const page = usePage()

const props = defineProps({
    lesson: Object,
    employee: Object,
})


</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <Card class="shadow-sm">
                        <template #content>
                            <div class="flex items-start justify-between">
                                <div>
                                    <div class="text-sm text-gray-500">Всего оценок</div>
                                    <div class="text-3xl font-semibold mt-1 text-gray-900">{{ lesson.total }}</div>
                                    <div class="text-xs text-gray-400 mt-2">Оценка уроков • всё время</div>
                                </div>
                                <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center">
                                    <i class="pi pi-star text-gray-700"></i>
                                </div>
                            </div>
                        </template>
                    </Card>

                    <Card class="shadow-sm">
                        <template #content>
                            <div class="flex items-start justify-between">
                                <div>
                                    <div class="text-sm text-gray-500">Средняя оценка</div>
                                    <div class="text-3xl font-semibold mt-1 text-gray-900">
                                        {{ lesson.avg_rating ?? '—' }}
                                    </div>
                                    <div class="text-xs text-gray-400 mt-2">Среднее по оценкам уроков</div>
                                </div>
                                <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center">
                                    <i class="pi pi-percentage text-gray-700"></i>
                                </div>
                            </div>
                        </template>
                    </Card>

                    <Card class="shadow-sm">
                        <template #content>
                            <div class="flex items-start justify-between">
                                <div>
                                    <div class="text-sm text-gray-500">NPS - уроки</div>
                                    <div class="flex items-center gap-2 mt-1">
                                        <div class="text-3xl font-semibold text-gray-900">{{ lesson.nps ?? '—' }}</div>

                                    </div>
                                    <div class="text-xs text-gray-400 mt-2">Сторонники - {{ lesson.promoters }} | Критики - {{ lesson.detractors }} | Нейтралы - {{ lesson.passives }}</div>
                                </div>
                                <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center">
                                    <i class="pi pi-thumbs-up text-gray-700"></i>
                                </div>
                            </div>
                        </template>
                    </Card>
                </div>

                <Divider class="my-5" />

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <Card class="shadow-sm">
                        <template #content>
                            <div class="flex items-start justify-between">
                                <div>
                                    <div class="text-sm text-gray-500">Всего оценок</div>
                                    <div class="text-3xl font-semibold mt-1 text-gray-900">{{ employee.total }}</div>
                                    <div class="text-xs text-gray-400 mt-2">Оценка сотрудников • всё время</div>
                                </div>
                                <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center">
                                    <i class="pi pi-star text-gray-700"></i>
                                </div>
                            </div>
                        </template>
                    </Card>

                    <Card class="shadow-sm">
                        <template #content>
                            <div class="flex items-start justify-between">
                                <div>
                                    <div class="text-sm text-gray-500">Средняя оценка</div>
                                    <div class="text-3xl font-semibold mt-1 text-gray-900">
                                        {{ employee.avg_rating ?? '—' }}
                                    </div>
                                    <div class="text-xs text-gray-400 mt-2">Среднее по оценкам сотрудников</div>
                                </div>
                                <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center">
                                    <i class="pi pi-percentage text-gray-700"></i>
                                </div>
                            </div>
                        </template>
                    </Card>

                    <Card class="shadow-sm">
                        <template #content>
                            <div class="flex items-start justify-between">
                                <div>
                                    <div class="text-sm text-gray-500">NPS - сотрудники</div>
                                    <div class="flex items-center gap-2 mt-1">
                                        <div class="text-3xl font-semibold text-gray-900">{{ employee.nps ?? '—' }}</div>

                                    </div>
                                    <div class="text-xs text-gray-400 mt-2">Сторонники - {{ employee.promoters }} | Критики - {{ employee.detractors }} | Нейтралы - {{ employee.passives }}</div>
                                </div>
                                <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center">
                                    <i class="pi pi-thumbs-up text-gray-700"></i>
                                </div>
                            </div>
                        </template>
                    </Card>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
