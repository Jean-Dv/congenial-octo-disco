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
    primary: 'border-transparent bg-aeris-primary-container text-aeris-on-primary-container shadow-[0_0_24px_rgba(230,33,23,0.18)] hover:bg-aeris-primary hover:text-aeris-on-primary',
    secondary: 'border-aeris-border bg-aeris-surface-container-low/80 text-aeris-on-surface hover:border-aeris-primary-container hover:bg-aeris-primary-container/10',
    ghost: 'border-transparent bg-transparent text-aeris-on-surface-variant hover:bg-aeris-primary-container/10 hover:text-aeris-on-surface',
    danger: 'border-aeris-error-container/60 bg-aeris-error-container text-aeris-on-error-container hover:border-aeris-error hover:bg-aeris-error/20',
}[props.variant] ?? 'border-transparent bg-aeris-primary-container text-aeris-on-primary-container'));

const buttonClasses = computed(() => [
    'inline-flex items-center justify-center gap-2 rounded border font-semibold transition duration-150',
    'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-aeris-primary-container/45 focus-visible:ring-offset-2 focus-visible:ring-offset-aeris-background',
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
