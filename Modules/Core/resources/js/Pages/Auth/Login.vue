<script setup>
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

    <ThemeGuestLayout title="Bienvenido de vuelta" subtitle="Entra a tu panel de reino">
        <form class="space-y-5" @submit.prevent="submit">
            <ThemeField for="email" label="Correo electronico" :error="form.errors.email">
                <ThemeInput id="email" v-model="form.email" type="email" autofocus placeholder="tucorreo@ejemplo.com" />
            </ThemeField>

            <ThemeField for="password" label="Contrasena" :error="form.errors.password">
                <ThemeInput id="password" v-model="form.password" type="password" placeholder="********" />
            </ThemeField>

            <div class="flex items-center justify-between gap-3 text-sm">
                <ThemeCheckbox v-model="form.remember">Recordarme</ThemeCheckbox>
                <Link href="/forgot-password" class="theme-link">Olvidaste tu contrasena?</Link>
            </div>

            <ThemeButton type="submit" :disabled="form.processing" block>Entrar</ThemeButton>

            <p class="text-center text-sm text-theme-on-surface-muted">
                No tienes cuenta?
                <Link href="/register" class="theme-link">Registrate</Link>
            </p>
        </form>
    </ThemeGuestLayout>
</template>
