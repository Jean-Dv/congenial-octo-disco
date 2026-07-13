<script setup>
defineProps({
    checked: { type: Boolean, default: false },
    disabled: { type: Boolean, default: false },
    label: { type: String, default: '' },
});

const emit = defineEmits(['toggle']);
</script>

<template>
    <button
        type="button"
        role="switch"
        :aria-checked="checked"
        :aria-label="label || undefined"
        :disabled="disabled"
        class="relative h-6 w-11 shrink-0 rounded-full border transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-aeris-primary-container/45 disabled:cursor-not-allowed disabled:opacity-50"
        :class="checked ? 'border-aeris-primary-container bg-aeris-primary-container shadow-[0_0_18px_rgba(230,33,23,0.25)]' : 'border-aeris-border bg-aeris-surface-container-high'"
        @click="emit('toggle')"
    >
        <span
            class="absolute top-0.5 h-5 w-5 rounded-full bg-aeris-on-surface transition"
            :class="checked ? 'left-5' : 'left-0.5'"
        />
    </button>
</template>
