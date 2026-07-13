<script setup>
const model = defineModel({ type: String, default: '' });

defineProps({
    type: { type: String, default: 'text' },
    placeholder: { type: String, default: '' },
    autofocus: { type: Boolean, default: false },
});
</script>

<template>
    <AerisInput
        v-model="model"
        :type="type"
        :placeholder="placeholder"
        :autofocus="autofocus"
    />
</template>
