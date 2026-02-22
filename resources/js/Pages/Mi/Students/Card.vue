<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import ExpandableText from '@/Components/Mi/ExpandableText.vue'

// PrimeVue
import Checkbox from 'primevue/checkbox'
import Tag from 'primevue/tag'
import Button from 'primevue/button'

const props = defineProps({
    student: Object,
    results: Array,
})

const showGeneral = ref(true)

// ✅ фронтовый фильтр "только актуальные"
const onlyActual = ref(false)

// ✅ общая кнопка развернуть/свернуть тексты (только фронт)
const expandAll = ref(false)
// ✅ сигнал для всех ExpandableText (чтобы они применили новое значение)
const expandSignal = ref(0)

const filteredResults = computed(() => {
    if (!onlyActual.value) return props.results
    return props.results.filter(r => r.is_actual === true)
})

const title = computed(() => {
    const count = props.student.grade_count ?? 0
    const avg = props.student.avg_grade ?? 0
    return `${props.student.fio} | Попытки: ${count} | Оценка: ${avg}`
})

function gradeSeverity(row) {
    if (!row.is_actual) return 'secondary'
    if (row.feedback_grade >= 8) return 'success'
    if (row.feedback_grade >= 5) return 'info'
    return 'danger'
}

function bullets(arr) {
    return (arr ?? []).map(t => `• ${t}`).join('\n')
}

function toggleExpandAll() {
    expandAll.value = !expandAll.value
    expandSignal.value++ // 🔥 важно: триггерим все компоненты
}
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="`MI — ${student.fio}`" />

        <div class="py-4 mx-auto max-w-7xl sm:px-6 lg:px-8">

            <!-- Header -->
            <div class="mb-4 bg-white rounded-2xl shadow-sm p-4 md:p-5 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div class="flex flex-col gap-1">
                    <div class="flex items-center gap-3">
                        <Link
                            :href="route('mi.students.index')"
                            class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border text-sm hover:bg-gray-50"
                        >
                            ← Все ученики
                        </Link>

                        <span class="text-xs text-gray-500">
                             ID: {{ student.id }} · GC user_id: {{ student.user_id }}
                        </span>
                    </div>

                    <div class="mb-2 mt-2">
                        <h1 class="text-xl md:text-2xl font-semibold">
                            {{ student.fio }}
                        </h1>
                    </div>

                    <div class="flex flex-wrap gap-2 text-sm">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-gray-100">
                          Попытки: <b class="ml-1">{{ student.grade_count }}</b>
                        </span>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-gray-100">
                            Средняя оценка: <b class="ml-1">{{ student.avg_grade }}</b>
                        </span>
                    </div>
                </div>

                <button
                    v-if="student.general_feedback"
                    type="button"
                    class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-gray-900 text-white hover:bg-gray-800"
                    @click="showGeneral = !showGeneral"
                >
                    {{ showGeneral ? 'Скрыть общую рекомендацию' : 'Показать общую рекомендацию' }}
                </button>
            </div>

            <!-- General feedback -->
            <div
                v-if="student.general_feedback && showGeneral"
                class="bg-white rounded-2xl shadow-sm p-4 md:p-6 space-y-4"
            >
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold">Общая рекомендация</h2>
                </div>

                <div class="rounded-xl border p-4 bg-gray-50">
                    <div class="font-semibold mb-2">🌟 Общий итог</div>
                    <p class="text-gray-800 whitespace-pre-line">
                        {{ student.general_feedback.summary }}
                    </p>
                </div>

                <div v-if="student.general_feedback.steps?.length" class="space-y-3">
                    <h3 class="text-base font-semibold">Шаги</h3>

                    <div
                        v-for="(step, idx) in student.general_feedback.steps"
                        :key="idx"
                        class="rounded-xl border p-4"
                    >
                        <div class="font-semibold mb-2">
                            {{ idx + 1 }}. {{ step.description }}
                        </div>

                        <div class="space-y-2 text-sm text-gray-800">
                            <div>
                                <div class="text-gray-500 font-medium mb-1">Улучшения</div>
                                <p class="whitespace-pre-line">{{ step.improvements }}</p>
                            </div>

                            <div>
                                <div class="text-gray-500 font-medium mb-1">Дальше</div>
                                <p class="whitespace-pre-line">{{ step.next_steps }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="student.general_feedback.growth_areas?.length" class="rounded-xl border p-4">
                    <h3 class="text-base font-semibold mb-2">💪 Основные направления роста</h3>
                    <ul class="list-disc pl-5 space-y-1 text-sm">
                        <li v-for="(g, idx) in student.general_feedback.growth_areas" :key="idx">
                            {{ g }}
                        </li>
                    </ul>
                </div>

                <div v-if="student.general_feedback.final_advice" class="rounded-xl border p-4 bg-gray-50">
                    <div class="font-semibold mb-2">💬 Совет</div>
                    <p class="text-gray-800 whitespace-pre-line">
                        {{ student.general_feedback.final_advice }}
                    </p>
                </div>
            </div>

            <!-- Attempts table -->
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                <div class="p-4 md:p-5 flex items-center justify-between">
                    <h2 class="text-lg font-semibold">Попытки</h2>

                    <div class="flex items-center gap-4">
                        <!-- ✅ общий разворот текстов -->
                        <Button
                            :label="expandAll ? 'Свернуть тексты' : 'Развернуть тексты'"
                            :icon="expandAll ? 'pi pi-chevron-up' : 'pi pi-chevron-down'"
                            severity="secondary"
                            outlined
                            size="small"
                            @click="toggleExpandAll"
                        />

                        <!-- ✅ фильтр только на фронте -->
                        <Checkbox
                            v-model="onlyActual"
                            :binary="true"
                            inputId="onlyActual"
                        />
                        <label for="onlyActual" class="text-sm text-surface-700 cursor-pointer">
                            Только актуальные
                        </label>

                        <div class="text-sm text-gray-500">
                            Показано: {{ filteredResults.length }} / {{ results.length }}
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="text-center px-3 py-3 w-20">№</th>
                            <th class="text-center px-3 py-3 min-w-[260px]">Текст ученика</th>
                            <th class="text-center px-3 py-3 w-28">Оценка</th>
                            <th class="text-center px-3 py-3 min-w-[280px]">Комментарий</th>
                            <th class="text-center px-3 py-3 min-w-[280px]">Рекомендация</th>
                            <th class="text-center px-3 py-3 w-44">Дата</th>
                        </tr>
                        </thead>

                        <tbody>
                        <tr
                            v-for="row in filteredResults"
                            :key="row.id"
                            class="border-t hover:bg-gray-50 align-top"
                        >
                            <td class="text-center px-3 py-3 font-semibold">
                                {{ row.attempt_number }}
                            </td>

                            <td class="px-3 py-3">
                                <ExpandableText
                                    :text="row.prompt"
                                    :lines="4"
                                    :expandAll="expandAll"
                                    :expandSignal="expandSignal"
                                />
                            </td>

                            <td class="text-center px-3 py-3">
                                <Tag
                                    :value="row.feedback_grade"
                                    :severity="gradeSeverity(row)"
                                    rounded
                                    :title="row.is_actual ? 'Актуальная попытка' : 'Неактуальная попытка'"
                                />
                            </td>

                            <td class="px-3 py-3">
                                <ExpandableText
                                    :text="bullets(row.feedback_comment)"
                                    :lines="4"
                                    :expandAll="expandAll"
                                    :expandSignal="expandSignal"
                                />
                            </td>

                            <td class="px-3 py-3">
                                <ExpandableText
                                    :text="bullets(row.feedback_recommendation)"
                                    :lines="4"
                                    :expandAll="expandAll"
                                    :expandSignal="expandSignal"
                                />
                            </td>

                            <td class="text-center px-3 py-3 whitespace-nowrap text-gray-700">
                                {{ row.created_at_ru }}
                            </td>
                        </tr>

                        <tr v-if="filteredResults.length === 0">
                            <td colspan="6" class="text-center py-10 text-gray-500">
                                Нет попыток
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
