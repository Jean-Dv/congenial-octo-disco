<script setup>
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

    <ThemeGuestLayout title="Restablece tu contrasena" subtitle="Se actualizara en el panel y en cada reino">
        <form class="space-y-5" @submit.prevent="submit">
            <ThemeField for="email" label="Correo electronico" :error="form.errors.email">
                <ThemeInput id="email" v-model="form.email" type="email" />
            </ThemeField>

            <ThemeField for="password" label="Nueva contrasena" :error="form.errors.password">
                <ThemeInput id="password" v-model="form.password" type="password" autofocus placeholder="Maximo 16 caracteres" />
            </ThemeField>

            <ThemeField for="password_confirmation" label="Confirmar nueva contrasena" :error="form.errors.password_confirmation">
                <ThemeInput id="password_confirmation" v-model="form.password_confirmation" type="password" />
            </ThemeField>

            <ThemeButton type="submit" :disabled="form.processing" block>Restablecer contrasena</ThemeButton>
        </form>
    </ThemeGuestLayout>
</template>
