<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
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
        <p class="text-sm text-parchment-300">
            Gracias por registrarte. Antes de continuar, ¿puedes confirmar tu correo haciendo clic en el enlace que te
            acabamos de enviar? Si no lo recibiste, con gusto te enviamos otro.
        </p>

        <div v-if="verificationLinkSent" class="mt-4 rounded-lg border border-spectral-500/30 bg-spectral-500/10 px-4 py-3 text-sm text-spectral-400">
            Te enviamos un nuevo enlace de verificacion a tu correo.
        </div>

        <form class="mt-6" @submit.prevent="submit">
            <PrimaryButton :disabled="form.processing">Reenviar correo de verificacion</PrimaryButton>
        </form>
    </GuestLayout>
</template>
