<script setup>
import { computed } from 'vue';

const props = defineProps({
    tone: { type: String, default: 'neutral' },
});

const toneClasses = computed(() => ({
    neutral: 'border-theme-border bg-theme-surface-container-high text-theme-on-surface-muted',
    active: 'border-theme-primary-container/60 bg-theme-primary-container/15 text-theme-primary',
    critical: 'border-theme-error-container bg-theme-error-container/35 text-theme-error',
    muted: 'border-theme-border bg-theme-surface-container-low text-theme-on-surface-disabled',
}[props.tone] ?? 'border-theme-border bg-theme-surface-container-high text-theme-on-surface-muted'));
</script>

<template>
    <span class="inline-flex items-center gap-1.5 rounded-full border px-2 py-0.5 font-mono-data text-xs font-bold" :class="toneClasses">
        <slot />
    </span>
</template>
