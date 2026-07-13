<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    href: { type: String, default: '' },
    type: { type: String, default: 'button' },
    title: { type: String, default: '' },
    variant: { type: String, default: 'ghost' },
    disabled: { type: Boolean, default: false },
});

const tag = computed(() => (props.href && !props.disabled ? Link : 'button'));

const variantClasses = computed(() => ({
    ghost: 'border-transparent text-aeris-on-surface-muted hover:bg-aeris-primary-container/10 hover:text-aeris-on-surface',
    secondary: 'border-aeris-border bg-aeris-surface-container-low text-aeris-on-surface hover:border-aeris-primary-container',
    danger: 'border-transparent text-aeris-error hover:bg-aeris-error-container/20 hover:text-aeris-on-error-container',
}[props.variant] ?? 'border-transparent text-aeris-on-surface-muted hover:bg-aeris-primary-container/10 hover:text-aeris-on-surface'));
</script>

<template>
    <component
        :is="tag"
        :href="href || undefined"
        :type="tag === 'button' ? type : undefined"
        :disabled="tag === 'button' ? disabled : undefined"
        :title="title || undefined"
        :aria-label="title || undefined"
        class="inline-flex h-9 w-9 items-center justify-center rounded border transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-aeris-primary-container/45 disabled:cursor-not-allowed disabled:opacity-50"
        :class="variantClasses"
    >
        <slot />
    </component>
</template>
