<script setup>
import { computed } from 'vue';

const props = defineProps({
    tone: { type: String, default: 'neutral' },
});

const toneClasses = computed(() => ({
    neutral: 'border-aeris-border bg-aeris-surface-container-high text-aeris-on-surface-muted',
    active: 'border-aeris-primary-container/60 bg-aeris-primary-container/15 text-aeris-primary',
    critical: 'border-aeris-error-container bg-aeris-error-container/35 text-aeris-error',
    muted: 'border-aeris-border bg-aeris-surface-container-low text-aeris-on-surface-disabled',
}[props.tone] ?? 'border-aeris-border bg-aeris-surface-container-high text-aeris-on-surface-muted'));
</script>

<template>
    <span class="inline-flex items-center gap-1.5 rounded-full border px-2 py-0.5 font-mono-data text-xs font-bold" :class="toneClasses">
        <slot />
    </span>
</template>
