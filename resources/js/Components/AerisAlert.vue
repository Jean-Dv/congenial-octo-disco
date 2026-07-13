<script setup>
import { computed } from 'vue';

const props = defineProps({
    tone: { type: String, default: 'info' },
});

const toneClasses = computed(() => ({
    info: 'border-aeris-border bg-aeris-surface-container-high text-aeris-on-surface',
    success: 'border-aeris-primary-container/45 bg-aeris-primary-container/10 text-aeris-primary',
    error: 'border-aeris-error-container/60 bg-aeris-error-container/20 text-aeris-error',
}[props.tone] ?? 'border-aeris-border bg-aeris-surface-container-high text-aeris-on-surface'));
</script>

<template>
    <div class="rounded border px-4 py-3 text-sm" :class="toneClasses">
        <slot />
    </div>
</template>
