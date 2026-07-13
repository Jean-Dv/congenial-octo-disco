<script setup>
import { usePage, router } from '@inertiajs/vue3';
import { LayoutDashboard, Server, Blocks, LogOut, Menu } from 'lucide-vue-next';
import { ref, computed } from 'vue';

defineProps({
    title: { type: String, default: '' },
});

const page = usePage();
const user = computed(() => page.props.auth?.user);
const flashSuccess = computed(() => page.props.flash?.success);
const flashError = computed(() => page.props.flash?.error);
const currentUrl = computed(() => page.url ?? '');

const mobileNavOpen = ref(false);

function logout() {
    router.post('/logout');
}

function isActive(href) {
    return currentUrl.value === href || currentUrl.value.startsWith(`${href}/`);
}
</script>

<template>
    <div class="aeris-grid-shell flex min-h-screen">
        <!-- Sidebar -->
        <aside
            class="aeris-glass fixed inset-y-0 left-0 z-30 w-64 -translate-x-full border-r border-aeris-border transition-transform lg:translate-x-0"
            :class="{ 'translate-x-0': mobileNavOpen }"
        >
            <div class="flex h-16 items-center border-b border-aeris-border px-5">
                <AerisLogo />
            </div>

            <nav class="flex flex-col gap-1 p-3">
                <AerisNavLink
                    href="/dashboard"
                    :active="isActive('/dashboard')"
                >
                    <template #icon><LayoutDashboard class="h-4 w-4" /></template>
                    Dashboard
                </AerisNavLink>

                <template v-if="user?.is_admin">
                    <p class="mt-4 mb-1 px-3 font-mono-data text-xs font-bold uppercase text-aeris-on-surface-muted">Administracion</p>

                    <AerisNavLink
                        href="/admin/realms"
                        :active="isActive('/admin/realms')"
                    >
                        <template #icon><Server class="h-4 w-4" /></template>
                        Reinos
                    </AerisNavLink>

                    <AerisNavLink
                        href="/admin/modules"
                        :active="isActive('/admin/modules')"
                    >
                        <template #icon><Blocks class="h-4 w-4" /></template>
                        Modulos
                    </AerisNavLink>
                </template>
            </nav>

            <div class="absolute inset-x-0 bottom-0 border-t border-aeris-border p-3">
                <div class="flex items-center justify-between rounded bg-aeris-surface-container-low/70 px-3 py-2">
                    <div class="min-w-0">
                        <p class="truncate text-sm font-medium text-aeris-on-surface">{{ user?.name }}</p>
                        <p class="truncate text-xs text-aeris-on-surface-muted">{{ user?.email }}</p>
                    </div>
                    <AerisIconButton title="Cerrar sesion" variant="danger" @click="logout">
                        <LogOut class="h-4 w-4" />
                    </AerisIconButton>
                </div>
            </div>
        </aside>

        <!-- Overlay movil -->
        <div v-if="mobileNavOpen" class="fixed inset-0 z-20 bg-black/50 lg:hidden" @click="mobileNavOpen = false" />

        <!-- Contenido -->
        <div class="flex-1 lg:pl-64">
            <header class="aeris-glass sticky top-0 z-10 flex h-16 items-center gap-4 border-b border-aeris-border px-5 lg:px-8">
                <AerisIconButton title="Abrir menu" class="lg:hidden" @click="mobileNavOpen = true">
                    <Menu class="h-5 w-5" />
                </AerisIconButton>
                <h1 class="text-lg font-semibold text-aeris-on-surface">{{ title }}</h1>
            </header>

            <main class="mx-auto w-full max-w-[1440px] px-5 py-8 lg:px-8">
                <AerisAlert v-if="flashSuccess" tone="success" class="mb-6">
                    {{ flashSuccess }}
                </AerisAlert>
                <AerisAlert v-if="flashError" tone="error" class="mb-6">
                    {{ flashError }}
                </AerisAlert>

                <slot />
            </main>
        </div>
    </div>
</template>
