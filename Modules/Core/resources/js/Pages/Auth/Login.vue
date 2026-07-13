<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

function submit() {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
}
</script>

<template>
    <Head title="Iniciar sesion" />

    <GuestLayout title="Bienvenido de vuelta" subtitle="Entra a tu panel de reino">
        <form class="space-y-5" @submit.prevent="submit">
            <AerisField for="email" label="Correo electronico" :error="form.errors.email">
                <AerisInput id="email" v-model="form.email" type="email" autofocus placeholder="tucorreo@ejemplo.com" />
            </AerisField>

            <AerisField for="password" label="Contrasena" :error="form.errors.password">
                <AerisInput id="password" v-model="form.password" type="password" placeholder="********" />
            </AerisField>

            <div class="flex items-center justify-between gap-3 text-sm">
                <AerisCheckbox v-model="form.remember">Recordarme</AerisCheckbox>
                <Link href="/forgot-password" class="aeris-link">Olvidaste tu contrasena?</Link>
            </div>

            <AerisButton type="submit" :disabled="form.processing" block>Entrar</AerisButton>

            <p class="text-center text-sm text-aeris-on-surface-muted">
                No tienes cuenta?
                <Link href="/register" class="aeris-link">Registrate</Link>
            </p>
        </form>
    </GuestLayout>
</template>
