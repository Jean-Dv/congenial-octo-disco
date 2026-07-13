<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps({
    status: { type: String, default: null },
});

const form = useForm({ email: '' });

function submit() {
    form.post('/forgot-password');
}
</script>

<template>
    <Head title="Olvide mi contrasena" />

    <GuestLayout title="Olvidaste tu contrasena?" subtitle="Te enviamos un enlace para restablecerla">
        <AerisAlert v-if="status" tone="success" class="mb-5">
            {{ status }}
        </AerisAlert>

        <form class="space-y-5" @submit.prevent="submit">
            <AerisField for="email" label="Correo electronico" :error="form.errors.email">
                <AerisInput id="email" v-model="form.email" type="email" autofocus placeholder="tucorreo@ejemplo.com" />
            </AerisField>

            <AerisButton type="submit" :disabled="form.processing" block>Enviar enlace</AerisButton>
        </form>
    </GuestLayout>
</template>
