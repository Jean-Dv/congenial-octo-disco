<script setup>
import { computed } from 'vue';

const props = defineProps({
    tone: { type: String, default: 'info' },
});

const toneClasses = computed(() => ({
    info: 'border-theme-border bg-theme-surface-container-high text-theme-on-surface',
    success: 'border-theme-primary-container/45 bg-theme-primary-container/10 text-theme-primary',
    error: 'border-theme-error-container/60 bg-theme-error-container/20 text-theme-error',
}[props.tone] ?? 'border-theme-border bg-theme-surface-container-high text-theme-on-surface'));
</script>

<template>
    <div class="rounded border px-4 py-3 text-sm" :class="toneClasses">
        <slot />
    </div>
</template>
