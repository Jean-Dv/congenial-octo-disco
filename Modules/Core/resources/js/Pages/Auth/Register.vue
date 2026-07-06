<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
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

    <GuestLayout title="Crea tu cuenta" subtitle="El mismo usuario y contraseña te serviran para jugar">
        <form class="space-y-5" @submit.prevent="submit">
            <div>
                <InputLabel for="username" value="Usuario" />
                <TextInput id="username" v-model="form.username" autofocus placeholder="MaxDe16Caracteres" />
                <InputError :message="form.errors.username" />
                <p class="mt-1 text-xs text-parchment-500">Maximo 16 caracteres, solo letras y numeros: es el mismo usuario para entrar al juego.</p>
            </div>

            <div>
                <InputLabel for="email" value="Correo electronico" />
                <TextInput id="email" v-model="form.email" type="email" placeholder="tucorreo@ejemplo.com" />
                <InputError :message="form.errors.email" />
            </div>

            <div>
                <InputLabel for="password" value="Contraseña" />
                <TextInput id="password" v-model="form.password" type="password" placeholder="Maximo 16 caracteres" />
                <InputError :message="form.errors.password" />
            </div>

            <div>
                <InputLabel for="password_confirmation" value="Confirmar contraseña" />
                <TextInput id="password_confirmation" v-model="form.password_confirmation" type="password" placeholder="Repite tu contraseña" />
                <InputError :message="form.errors.password_confirmation" />
            </div>

            <PrimaryButton :disabled="form.processing">Crear cuenta</PrimaryButton>

            <p class="text-center text-sm text-parchment-500">
                ¿Ya tienes cuenta?
                <Link href="/login" class="font-medium text-rune-400 hover:text-rune-300">Inicia sesion</Link>
            </p>
        </form>
    </GuestLayout>
</template>
