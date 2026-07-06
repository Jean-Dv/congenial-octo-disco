<script setup>
import { Link, usePage, router } from '@inertiajs/vue3';
import { LayoutDashboard, Server, Blocks, LogOut, Menu } from 'lucide-vue-next';
import { ref, computed } from 'vue';

defineProps({
    title: { type: String, default: '' },
});

const page = usePage();
const user = computed(() => page.props.auth?.user);
const flashSuccess = computed(() => page.props.flash?.success);
const flashError = computed(() => page.props.flash?.error);

const mobileNavOpen = ref(false);

function logout() {
    router.post('/logout');
}
</script>

<template>
    <div class="flex min-h-screen bg-ink-950">
        <!-- Sidebar -->
        <aside
            class="fixed inset-y-0 left-0 z-30 w-64 -translate-x-full border-r border-ink-700 bg-ink-900 transition-transform lg:translate-x-0"
            :class="{ 'translate-x-0': mobileNavOpen }"
        >
            <div class="flex h-16 items-center gap-2 border-b border-ink-700 px-5">
                <svg width="22" height="22" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M14 2C8 8 8 20 14 26C20 20 20 8 14 2Z" fill="var(--color-rune-500)" opacity="0.9" />
                    <circle cx="14" cy="14" r="3.2" fill="var(--color-ink-950)" />
                </svg>
                <span class="font-display text-base font-semibold text-parchment-100">Moon</span>
            </div>

            <nav class="flex flex-col gap-1 p-3">
                <Link
                    href="/dashboard"
                    class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-parchment-300 transition hover:bg-ink-800 hover:text-parchment-100"
                >
                    <LayoutDashboard class="h-4 w-4" />
                    Dashboard
                </Link>

                <template v-if="user?.is_admin">
                    <p class="mt-4 mb-1 px-3 text-xs font-semibold uppercase tracking-wider text-parchment-500">Administracion</p>

                    <Link
                        href="/admin/realms"
                        class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-parchment-300 transition hover:bg-ink-800 hover:text-parchment-100"
                    >
                        <Server class="h-4 w-4" />
                        Reinos
                    </Link>

                    <Link
                        href="/admin/modules"
                        class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-parchment-300 transition hover:bg-ink-800 hover:text-parchment-100"
                    >
                        <Blocks class="h-4 w-4" />
                        Modulos
                    </Link>
                </template>
            </nav>

            <div class="absolute inset-x-0 bottom-0 border-t border-ink-700 p-3">
                <div class="flex items-center justify-between rounded-lg px-3 py-2">
                    <div class="min-w-0">
                        <p class="truncate text-sm font-medium text-parchment-100">{{ user?.name }}</p>
                        <p class="truncate text-xs text-parchment-500">{{ user?.email }}</p>
                    </div>
                    <button @click="logout" class="rounded-md p-2 text-parchment-500 transition hover:bg-ink-800 hover:text-garnet-400" title="Cerrar sesion">
                        <LogOut class="h-4 w-4" />
                    </button>
                </div>
            </div>
        </aside>

        <!-- Overlay movil -->
        <div v-if="mobileNavOpen" class="fixed inset-0 z-20 bg-black/50 lg:hidden" @click="mobileNavOpen = false" />

        <!-- Contenido -->
        <div class="flex-1 lg:pl-64">
            <header class="flex h-16 items-center gap-4 border-b border-ink-700 px-5 lg:px-8">
                <button class="text-parchment-300 lg:hidden" @click="mobileNavOpen = true">
                    <Menu class="h-5 w-5" />
                </button>
                <h1 class="font-display text-lg font-semibold text-parchment-100">{{ title }}</h1>
            </header>

            <main class="px-5 py-8 lg:px-8">
                <div
                    v-if="flashSuccess"
                    class="mb-6 rounded-lg border border-spectral-500/30 bg-spectral-500/10 px-4 py-3 text-sm text-spectral-400"
                >
                    {{ flashSuccess }}
                </div>
                <div
                    v-if="flashError"
                    class="mb-6 rounded-lg border border-garnet-500/30 bg-garnet-500/10 px-4 py-3 text-sm text-garnet-400"
                >
                    {{ flashError }}
                </div>

                <slot />
            </main>
        </div>
    </div>
</template>
