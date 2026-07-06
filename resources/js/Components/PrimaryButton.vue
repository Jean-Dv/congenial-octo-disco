<script setup>
defineProps({
    disabled: { type: Boolean, default: false },
    type: { type: String, default: 'submit' },
});
</script>

<template>
    <button
        :type="type"
        :disabled="disabled"
        class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-rune-500 px-4 py-2.5 text-sm font-semibold text-ink-950 transition hover:bg-rune-400 disabled:cursor-not-allowed disabled:opacity-50"
    >
        <slot />
    </button>
</template>
