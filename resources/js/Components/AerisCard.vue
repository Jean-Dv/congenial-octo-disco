<script setup>
import { computed } from 'vue';

const props = defineProps({
    as: { type: String, default: 'section' },
    title: { type: String, default: '' },
    subtitle: { type: String, default: '' },
    tone: { type: String, default: 'default' },
    padding: { type: String, default: 'md' },
});

const paddingClasses = computed(() => ({
    none: '',
    sm: 'p-4',
    md: 'p-5 sm:p-6',
}[props.padding] ?? 'p-5 sm:p-6'));

const toneClasses = computed(() => ({
    default: 'border-theme-border bg-theme-surface-container-low/90',
    elevated: 'border-theme-outline-variant/70 bg-theme-surface-container/95',
    important: 'border-theme-border border-t-theme-primary-container bg-theme-surface-container-low/90',
}[props.tone] ?? 'border-theme-border bg-theme-surface-container-low/90'));
</script>

<template>
    <component :is="as" class="rounded-lg border backdrop-blur-sm" :class="[toneClasses, paddingClasses, tone === 'important' ? 'border-t-2' : '']">
        <header v-if="title || subtitle || $slots.actions" class="mb-5 flex items-start justify-between gap-4">
            <div class="min-w-0">
                <h2 v-if="title" class="text-base font-semibold text-theme-on-surface">{{ title }}</h2>
                <p v-if="subtitle" class="mt-1 text-sm text-theme-on-surface-muted">{{ subtitle }}</p>
            </div>

            <div v-if="$slots.actions" class="shrink-0">
                <slot name="actions" />
            </div>
        </header>

        <slot />
    </component>
</template>
