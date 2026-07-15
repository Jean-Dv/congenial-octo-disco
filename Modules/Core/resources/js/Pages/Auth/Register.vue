<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    username: '',
    email: '',
    password: '',
    password_confirmation: '',
});

function submit() {
    form.post('/register', {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
}
</script>

<template>
    <Head title="Crear cuenta" />

    <ThemeGuestLayout title="Crea tu cuenta" subtitle="El mismo usuario y contrasena te serviran para jugar">
        <form class="space-y-5" @submit.prevent="submit">
            <ThemeField
                for="username"
                label="Usuario"
                help="Maximo 16 caracteres, solo letras y numeros."
                :error="form.errors.username"
            >
                <ThemeInput id="username" v-model="form.username" autofocus placeholder="MaxDe16Caracteres" />
            </ThemeField>

            <ThemeField for="email" label="Correo electronico" :error="form.errors.email">
                <ThemeInput id="email" v-model="form.email" type="email" placeholder="tucorreo@ejemplo.com" />
            </ThemeField>

            <ThemeField for="password" label="Contrasena" :error="form.errors.password">
                <ThemeInput id="password" v-model="form.password" type="password" placeholder="Maximo 16 caracteres" />
            </ThemeField>

            <ThemeField for="password_confirmation" label="Confirmar contrasena" :error="form.errors.password_confirmation">
                <ThemeInput id="password_confirmation" v-model="form.password_confirmation" type="password" placeholder="Repite tu contrasena" />
            </ThemeField>

            <ThemeButton type="submit" :disabled="form.processing" block>Crear cuenta</ThemeButton>

            <p class="text-center text-sm text-theme-on-surface-muted">
                Ya tienes cuenta?
                <Link href="/login" class="theme-link">Inicia sesion</Link>
            </p>
        </form>
    </ThemeGuestLayout>
</template>
