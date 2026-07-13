<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    status: { type: String, default: null },
});

const form = useForm({});

const verificationLinkSent = computed(() => props.status === 'verification-link-sent');

function submit() {
    form.post('/email/verification-notification');
}
</script>

<template>
    <Head title="Verifica tu correo" />

    <GuestLayout title="Verifica tu correo electronico" subtitle="Falta un ultimo paso">
        <p class="text-sm text-aeris-on-surface-muted">
            Gracias por registrarte. Antes de continuar, confirma tu correo con el enlace que acabamos de enviar.
        </p>

        <AerisAlert v-if="verificationLinkSent" tone="success" class="mt-4">
            Te enviamos un nuevo enlace de verificacion a tu correo.
        </AerisAlert>

        <form class="mt-6" @submit.prevent="submit">
            <AerisButton type="submit" :disabled="form.processing" block>Reenviar correo de verificacion</AerisButton>
        </form>
    </GuestLayout>
</template>
