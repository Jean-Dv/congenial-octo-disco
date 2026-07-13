<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    email: { type: String, required: true },
    token: { type: String, required: true },
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

function submit() {
    form.post('/reset-password', {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
}
</script>

<template>
    <Head title="Restablecer contrasena" />

    <GuestLayout title="Restablece tu contrasena" subtitle="Se actualizara en el panel y en cada reino">
        <form class="space-y-5" @submit.prevent="submit">
            <AerisField for="email" label="Correo electronico" :error="form.errors.email">
                <AerisInput id="email" v-model="form.email" type="email" />
            </AerisField>

            <AerisField for="password" label="Nueva contrasena" :error="form.errors.password">
                <AerisInput id="password" v-model="form.password" type="password" autofocus placeholder="Maximo 16 caracteres" />
            </AerisField>

            <AerisField for="password_confirmation" label="Confirmar nueva contrasena" :error="form.errors.password_confirmation">
                <AerisInput id="password_confirmation" v-model="form.password_confirmation" type="password" />
            </AerisField>

            <AerisButton type="submit" :disabled="form.processing" block>Restablecer contrasena</AerisButton>
        </form>
    </GuestLayout>
</template>
