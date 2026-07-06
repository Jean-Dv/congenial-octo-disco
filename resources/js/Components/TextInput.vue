<script setup>
defineModel({ type: String, default: '' });

defineProps({
    type: { type: String, default: 'text' },
    placeholder: { type: String, default: '' },
    autofocus: { type: Boolean, default: false },
});
</script>

<template>
    <input
        v-model="$model"
        :type="type"
        :placeholder="placeholder"
        :autofocus="autofocus"
        class="w-full rounded-lg border border-ink-600 bg-ink-800 px-3.5 py-2.5 text-sm text-parchment-100 placeholder-parchment-500 outline-none transition focus:border-rune-500 focus:ring-2 focus:ring-rune-500/30"
    />
</template>
