<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    href: { type: String, default: '' },
    type: { type: String, default: 'button' },
    variant: { type: String, default: 'primary' },
    size: { type: String, default: 'md' },
    disabled: { type: Boolean, default: false },
    block: { type: Boolean, default: false },
});

const tag = computed(() => (props.href && !props.disabled ? Link : 'button'));

const sizeClasses = computed(() => ({
    sm: 'min-h-8 px-3 py-1.5 text-xs',
    md: 'min-h-10 px-4 py-2.5 text-sm',
    lg: 'min-h-11 px-5 py-3 text-base',
}[props.size] ?? 'min-h-10 px-4 py-2.5 text-sm'));

const variantClasses = computed(() => ({
    primary: 'border-transparent bg-theme-primary-container text-theme-on-primary-container shadow-[0_0_24px_rgba(230,33,23,0.18)] hover:bg-theme-primary hover:text-theme-on-primary',
    secondary: 'border-theme-border bg-theme-surface-container-low/80 text-theme-on-surface hover:border-theme-primary-container hover:bg-theme-primary-container/10',
    ghost: 'border-transparent bg-transparent text-theme-on-surface-variant hover:bg-theme-primary-container/10 hover:text-theme-on-surface',
    danger: 'border-theme-error-container/60 bg-theme-error-container text-theme-on-error-container hover:border-theme-error hover:bg-theme-error/20',
}[props.variant] ?? 'border-transparent bg-theme-primary-container text-theme-on-primary-container'));

const buttonClasses = computed(() => [
    'inline-flex items-center justify-center gap-2 rounded border font-semibold transition duration-150',
    'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-theme-primary-container/45 focus-visible:ring-offset-2 focus-visible:ring-offset-theme-background',
    'disabled:cursor-not-allowed disabled:opacity-50',
    props.block ? 'w-full' : '',
    props.disabled ? 'pointer-events-none opacity-50' : '',
    sizeClasses.value,
    variantClasses.value,
]);
</script>

<template>
    <component
        :is="tag"
        :href="href || undefined"
        :type="tag === 'button' ? type : undefined"
        :disabled="tag === 'button' ? disabled : undefined"
        :aria-disabled="disabled ? 'true' : undefined"
        :class="buttonClasses"
    >
        <slot />
    </component>
</template>
