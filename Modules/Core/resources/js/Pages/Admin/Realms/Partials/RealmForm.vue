<script setup>
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { ref } from 'vue';

const props = defineProps({
    form: { type: Object, required: true },
    coreTypes: { type: Array, required: true },
    submitLabel: { type: String, default: 'Guardar' },
});

const emit = defineEmits(['submit']);

const includeCharactersDb = ref(!!(props.form.characters_database && props.form.characters_database.host));

function toggleCharactersDb() {
    includeCharactersDb.value = !includeCharactersDb.value;
    if (!includeCharactersDb.value) {
        props.form.characters_database = null;
    } else if (!props.form.characters_database) {
        props.form.characters_database = { host: '', port: 3306, database: '', username: '', password: '' };
    }
}
</script>

<template>
    <form class="space-y-8" @submit.prevent="emit('submit')">
        <!-- Datos generales -->
        <section class="rounded-xl border border-ink-700 bg-ink-900 p-6">
            <h3 class="font-display text-base font-semibold text-parchment-100">Datos generales</h3>
            <div class="mt-4 grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <InputLabel value="Nombre" />
                    <TextInput v-model="form.name" placeholder="Reino Azuremyst" />
                    <InputError :message="form.errors['name']" />
                </div>
                <div>
                    <InputLabel value="Slug" />
                    <TextInput v-model="form.slug" placeholder="azuremyst" />
                    <InputError :message="form.errors['slug']" />
                </div>
                <div>
                    <InputLabel value="Core" />
                    <select v-model="form.core_type" class="w-full rounded-lg border border-ink-600 bg-ink-800 px-3.5 py-2.5 text-sm text-parchment-100 outline-none focus:border-rune-500 focus:ring-2 focus:ring-rune-500/30">
                        <option v-for="core in coreTypes" :key="core.value" :value="core.value">
                            {{ core.label }}{{ core.has_full_support ? '' : ' (sin implementar todavia)' }}
                        </option>
                    </select>
                    <InputError :message="form.errors['core_type']" />
                </div>
                <div>
                    <InputLabel value="RealmID para GM (account_access)" />
                    <TextInput v-model="form.gm_realm_id" type="number" placeholder="-1 (todos los reinos)" />
                    <InputError :message="form.errors['gm_realm_id']" />
                </div>
            </div>
            <label class="mt-5 flex items-center gap-2 text-sm text-parchment-300">
                <input v-model="form.enabled" type="checkbox" class="rounded border-ink-600 bg-ink-800 text-rune-500 focus:ring-rune-500/30" />
                Reino habilitado (se aprovisionan cuentas de juego automaticamente aqui)
            </label>
        </section>

        <!-- Base de datos auth -->
        <section class="rounded-xl border border-ink-700 bg-ink-900 p-6">
            <h3 class="font-display text-base font-semibold text-parchment-100">Base de datos "auth"</h3>
            <p class="mt-1 text-xs text-parchment-500">Se guarda cifrada. Se resuelve en caliente, no toca tu config/database.php.</p>
            <div class="mt-4 grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <InputLabel value="Host" />
                    <TextInput v-model="form.auth_database.host" placeholder="127.0.0.1" />
                    <InputError :message="form.errors['auth_database.host']" />
                </div>
                <div>
                    <InputLabel value="Puerto" />
                    <TextInput v-model="form.auth_database.port" type="number" placeholder="3306" />
                    <InputError :message="form.errors['auth_database.port']" />
                </div>
                <div>
                    <InputLabel value="Base de datos" />
                    <TextInput v-model="form.auth_database.database" placeholder="auth" />
                    <InputError :message="form.errors['auth_database.database']" />
                </div>
                <div>
                    <InputLabel value="Usuario" />
                    <TextInput v-model="form.auth_database.username" placeholder="trinity" />
                    <InputError :message="form.errors['auth_database.username']" />
                </div>
                <div>
                    <InputLabel value="Contraseña" />
                    <TextInput v-model="form.auth_database.password" type="password" />
                    <InputError :message="form.errors['auth_database.password']" />
                </div>
            </div>
        </section>

        <!-- Base de datos characters (opcional) -->
        <section class="rounded-xl border border-ink-700 bg-ink-900 p-6">
            <div class="flex items-center justify-between">
                <h3 class="font-display text-base font-semibold text-parchment-100">Base de datos "characters"</h3>
                <button type="button" @click="toggleCharactersDb" class="text-xs font-medium text-rune-400 hover:text-rune-300">
                    {{ includeCharactersDb ? 'Quitar' : 'Añadir' }}
                </button>
            </div>
            <p class="mt-1 text-xs text-parchment-500">Opcional en esta version del core: la usaran los modulos futuros que lean personajes.</p>

            <div v-if="includeCharactersDb" class="mt-4 grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <InputLabel value="Host" />
                    <TextInput v-model="form.characters_database.host" placeholder="127.0.0.1" />
                </div>
                <div>
                    <InputLabel value="Puerto" />
                    <TextInput v-model="form.characters_database.port" type="number" placeholder="3306" />
                </div>
                <div>
                    <InputLabel value="Base de datos" />
                    <TextInput v-model="form.characters_database.database" placeholder="characters" />
                </div>
                <div>
                    <InputLabel value="Usuario" />
                    <TextInput v-model="form.characters_database.username" placeholder="trinity" />
                </div>
                <div>
                    <InputLabel value="Contraseña" />
                    <TextInput v-model="form.characters_database.password" type="password" />
                </div>
            </div>
        </section>

        <!-- SOAP -->
        <section class="rounded-xl border border-ink-700 bg-ink-900 p-6">
            <h3 class="font-display text-base font-semibold text-parchment-100">Consola remota (SOAP)</h3>
            <p class="mt-1 text-xs text-parchment-500">Cuenta GM (rango 3+) del worldserver, usada para "server info", kick y comandos de personaje.</p>
            <div class="mt-4 grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <InputLabel value="Host" />
                    <TextInput v-model="form.remote_console.host" placeholder="127.0.0.1" />
                    <InputError :message="form.errors['remote_console.host']" />
                </div>
                <div>
                    <InputLabel value="Puerto" />
                    <TextInput v-model="form.remote_console.port" type="number" placeholder="7878" />
                    <InputError :message="form.errors['remote_console.port']" />
                </div>
                <div>
                    <InputLabel value="Usuario GM" />
                    <TextInput v-model="form.remote_console.username" />
                    <InputError :message="form.errors['remote_console.username']" />
                </div>
                <div>
                    <InputLabel value="Contraseña" />
                    <TextInput v-model="form.remote_console.password" type="password" />
                    <InputError :message="form.errors['remote_console.password']" />
                </div>
            </div>
        </section>

        <PrimaryButton :disabled="form.processing" class="w-auto px-6">{{ submitLabel }}</PrimaryButton>
    </form>
</template>
