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
    ghost: 'border-transparent text-theme-on-surface-muted hover:bg-theme-primary-container/10 hover:text-theme-on-surface',
    secondary: 'border-theme-border bg-theme-surface-container-low text-theme-on-surface hover:border-theme-primary-container',
    danger: 'border-transparent text-theme-error hover:bg-theme-error-container/20 hover:text-theme-on-error-container',
}[props.variant] ?? 'border-transparent text-theme-on-surface-muted hover:bg-theme-primary-container/10 hover:text-theme-on-surface'));
</script>

<template>
    <component
        :is="tag"
        :href="href || undefined"
        :type="tag === 'button' ? type : undefined"
        :disabled="tag === 'button' ? disabled : undefined"
        :title="title || undefined"
        :aria-label="title || undefined"
        class="inline-flex h-9 w-9 items-center justify-center rounded border transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-theme-primary-container/45 disabled:cursor-not-allowed disabled:opacity-50"
        :class="variantClasses"
    >
        <slot />
    </component>
</template>
