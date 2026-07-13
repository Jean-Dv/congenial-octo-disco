<script setup>
defineProps({
    disabled: { type: Boolean, default: false },
    type: { type: String, default: 'submit' },
    block: { type: Boolean, default: true },
});
</script>

<template>
    <AerisButton
        :type="type"
        :disabled="disabled"
        :block="block"
    >
        <slot />
    </AerisButton>
</template>
