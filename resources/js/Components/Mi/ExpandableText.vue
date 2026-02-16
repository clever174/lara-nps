<script setup>
import { computed, ref, watch } from 'vue'

const props = defineProps({
    text: { type: String, default: '' },
    lines: { type: Number, default: 4 },

    // ✅ сигнал от родителя: меняется — применяем expandAll
    expandSignal: { type: Number, default: 0 },
    // ✅ что именно применить по сигналу
    expandAll: { type: Boolean, default: false },
})

const expanded = ref(false)

// когда родитель прислал сигнал — принудительно выставляем состояние
watch(
    () => props.expandSignal,
    () => {
        expanded.value = !!props.expandAll
    }
)

const clampStyle = computed(() => {
    if (expanded.value) return {}
    return { maxHeight: `${props.lines * 1.4}em`, overflow: 'hidden' }
})

function toggleLocal() {
    expanded.value = !expanded.value
}
</script>

<template>
    <div class="space-y-2">
        <div class="whitespace-pre-line text-surface-700" :style="clampStyle">
            {{ text }}
        </div>

        <!-- ✅ локальная кнопка всегда доступна -->
        <button
            v-if="text && text.length > 160"
            type="button"
            class="text-xs text-surface-600 hover:text-surface-900 underline"
            @click="toggleLocal"
        >
            {{ expanded ? 'Скрыть' : 'Показать ещё' }}
        </button>
    </div>
</template>
