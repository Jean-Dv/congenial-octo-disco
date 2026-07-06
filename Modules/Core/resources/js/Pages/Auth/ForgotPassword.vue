<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
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
    <Head title="Olvide mi contraseña" />

    <GuestLayout title="¿Olvidaste tu contraseña?" subtitle="Te enviamos un enlace para restablecerla">
        <div v-if="status" class="mb-5 rounded-lg border border-spectral-500/30 bg-spectral-500/10 px-4 py-3 text-sm text-spectral-400">
            {{ status }}
        </div>

        <form class="space-y-5" @submit.prevent="submit">
            <div>
                <InputLabel for="email" value="Correo electronico" />
                <TextInput id="email" v-model="form.email" type="email" autofocus placeholder="tucorreo@ejemplo.com" />
                <InputError :message="form.errors.email" />
            </div>

            <PrimaryButton :disabled="form.processing">Enviar enlace</PrimaryButton>
        </form>
    </GuestLayout>
</template>
