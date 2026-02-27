<script setup>
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'

import DatePicker from 'primevue/datepicker'
import Button from 'primevue/button'

const props = defineProps({
    routeName: { type: String, required: true },
    date_start: { type: String, default: null },
    date_end: { type: String, default: null },
    extraQuery: { type: Object, default: () => ({}) },
    preserveState: { type: Boolean, default: true },
    replace: { type: Boolean, default: true },
})

const emit = defineEmits(['submit', 'reset'])

const start = ref(props.date_start ? new Date(props.date_start) : null)
const end = ref(props.date_end ? new Date(props.date_end) : null)

watch(() => props.date_start, v => {
    start.value = v ? new Date(v) : null
})

watch(() => props.date_end, v => {
    end.value = v ? new Date(v) : null
})

function formatYmd(d) {
    if (!d) return null
    const y = d.getFullYear()
    const m = String(d.getMonth() + 1).padStart(2, '0')
    const day = String(d.getDate()).padStart(2, '0')
    return `${y}-${m}-${day}`
}

function clean(obj) {
    return Object.fromEntries(
        Object.entries(obj).filter(([_, v]) => v !== null && v !== undefined && v !== '')
    )
}

function doSubmit() {
    const payload = clean({
        ...props.extraQuery,     // сохраняем sort_field, per_page, course_id и т.д.
        page: 1,                 // 🔥 сбрасываем страницу
        date_start: formatYmd(start.value),
        date_end: formatYmd(end.value),
    })

    router.get(route(props.routeName), payload, {
        preserveState: props.preserveState,
        replace: props.replace,
    })

    emit('submit', payload)
}

function doReset() {
    start.value = null
    end.value = null

    router.get(route(props.routeName), {}, {
        preserveState: false,   // полностью сбросить состояние
        replace: false,         // новая запись в history
    })

    emit('reset')
}
</script>

<template>
    <div class="bg-white rounded-lg shadow p-4">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 w-full sm:max-w-2xl">
                <div class="flex flex-col gap-1">
                    <DatePicker
                        v-model="start"
                        inputClass="text-center"
                        dateFormat="dd.mm.yy"
                        :manualInput="false"
                        showIcon
                        showOtherMonths
                        selectOtherMonths
                        class="w-full"
                        placeholder="Дата начала"
                    />
                </div>

                <div class="flex flex-col gap-1">
                    <DatePicker
                        v-model="end"
                        inputClass="text-center"
                        dateFormat="dd.mm.yy"
                        :manualInput="false"
                        showIcon
                        showOtherMonths
                        selectOtherMonths
                        class="w-full"
                        placeholder="Дата окончания"
                        :minDate="start || undefined"
                    />
                </div>
            </div>

            <div class="flex gap-2 justify-end">
                <Button label="Найти" icon="pi pi-search" @click="doSubmit" />
                <Button label="Сбросить" icon="pi pi-times" severity="secondary" outlined @click="doReset" />
            </div>
        </div>
    </div>
</template>
