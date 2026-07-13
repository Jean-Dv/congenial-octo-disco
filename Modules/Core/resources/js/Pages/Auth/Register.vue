<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
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

    <GuestLayout title="Crea tu cuenta" subtitle="El mismo usuario y contrasena te serviran para jugar">
        <form class="space-y-5" @submit.prevent="submit">
            <AerisField
                for="username"
                label="Usuario"
                help="Maximo 16 caracteres, solo letras y numeros."
                :error="form.errors.username"
            >
                <AerisInput id="username" v-model="form.username" autofocus placeholder="MaxDe16Caracteres" />
            </AerisField>

            <AerisField for="email" label="Correo electronico" :error="form.errors.email">
                <AerisInput id="email" v-model="form.email" type="email" placeholder="tucorreo@ejemplo.com" />
            </AerisField>

            <AerisField for="password" label="Contrasena" :error="form.errors.password">
                <AerisInput id="password" v-model="form.password" type="password" placeholder="Maximo 16 caracteres" />
            </AerisField>

            <AerisField for="password_confirmation" label="Confirmar contrasena" :error="form.errors.password_confirmation">
                <AerisInput id="password_confirmation" v-model="form.password_confirmation" type="password" placeholder="Repite tu contrasena" />
            </AerisField>

            <AerisButton type="submit" :disabled="form.processing" block>Crear cuenta</AerisButton>

            <p class="text-center text-sm text-aeris-on-surface-muted">
                Ya tienes cuenta?
                <Link href="/login" class="aeris-link">Inicia sesion</Link>
            </p>
        </form>
    </GuestLayout>
</template>
