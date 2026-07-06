<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
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
            <div>
                <InputLabel for="email" value="Correo electronico" />
                <TextInput id="email" v-model="form.email" type="email" autofocus placeholder="tucorreo@ejemplo.com" />
                <InputError :message="form.errors.email" />
            </div>

            <div>
                <InputLabel for="password" value="Contraseña" />
                <TextInput id="password" v-model="form.password" type="password" placeholder="••••••••" />
                <InputError :message="form.errors.password" />
            </div>

            <div class="flex items-center justify-between text-sm">
                <label class="flex items-center gap-2 text-parchment-300">
                    <input v-model="form.remember" type="checkbox" class="rounded border-ink-600 bg-ink-800 text-rune-500 focus:ring-rune-500/30" />
                    Recordarme
                </label>
                <Link href="/forgot-password" class="text-rune-400 hover:text-rune-300">¿Olvidaste tu contraseña?</Link>
            </div>

            <PrimaryButton :disabled="form.processing">Entrar</PrimaryButton>

            <p class="text-center text-sm text-parchment-500">
                ¿No tienes cuenta?
                <Link href="/register" class="font-medium text-rune-400 hover:text-rune-300">Registrate</Link>
            </p>
        </form>
    </GuestLayout>
</template>
