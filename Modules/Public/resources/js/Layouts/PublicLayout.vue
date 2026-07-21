<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Link } from '@inertiajs/vue3';

// ─────────────────────────────────────────────
// Scroll effect — navbar se opacifica al bajar
// ─────────────────────────────────────────────
const scrolled = ref(false);

function onScroll() {
    scrolled.value = window.scrollY > 50;
}

onMounted(() => window.addEventListener('scroll', onScroll, { passive: true }));
onUnmounted(() => window.removeEventListener('scroll', onScroll));

// ─────────────────────────────────────────────
// Mobile menu
// ─────────────────────────────────────────────
const mobileOpen = ref(false);

// ─────────────────────────────────────────────
// Links de navegación (rutas públicas)
// Cuando la ruta no exista aún, route() falla silenciosamente.
// ─────────────────────────────────────────────
function safeRoute(name) {
    try {
        return route(name);
    } catch {
        return '#';
    }
}

const navLinks = [
    { label: 'Home',      routeName: 'public.home' },
    { label: 'News',      routeName: 'public.news' },
    { label: 'Downloads', routeName: 'public.downloads' },
    { label: 'Ranking',   routeName: 'public.ranking' },
    { label: 'Armory',    routeName: 'public.armory' },
    { label: 'Store',     routeName: 'public.store' },
    { label: 'Support',   routeName: 'public.support' },
    { label: 'Discord',   routeName: 'public.discord' },
];

function isActive(routeName) {
    try {
        return route().current(routeName);
    } catch {
        return false;
    }
}

</script>

<template>
    <!-- ───────────────────────── NAVBAR ───────────────────────── -->
    <nav
        class="fixed top-0 w-full h-18 border-b border-white/5 shadow-[0_20px_40px_rgba(0,0,0,0.4)] z-50 transition-all duration-300"
        :class="scrolled
            ? 'bg-[#0d141e]/95 backdrop-blur-2xl'
            : 'bg-[#0d141e]/80 backdrop-blur-xl'"
    >
        <div class="flex justify-between items-center px-8 h-full max-w-360 mx-auto">
            <!-- Logo -->
            <Link
                :href="safeRoute('public.home')"
                class="font-display text-2xl font-bold tracking-tighter select-none"
                style="color: var(--color-theme-public-primary)"
            >
                MOONSHARD
            </Link>

            <!-- Desktop nav links -->
            <div class="hidden md:flex items-center gap-8">
                <Link
                    v-for="link in navLinks"
                    :key="link.routeName"
                    :href="safeRoute(link.routeName)"
                    class="text-sm font-medium transition-colors"
                    :class="isActive(link.routeName)
                        ? 'font-bold border-b-2 pb-1'
                        : 'hover:text-white'"
                    :style="isActive(link.routeName)
                        ? 'color: var(--color-theme-public-primary); border-color: var(--color-theme-public-primary)'
                        : 'color: var(--color-theme-public-text-secondary)'"
                >
                    {{ link.label }}
                </Link>
            </div>

            <!-- Auth buttons -->
            <div class="hidden md:flex items-center gap-4">
                <Link
                    :href="safeRoute('login')"
                    class="text-sm font-medium transition-colors px-4 py-2"
                    style="color: var(--color-theme-public-text-secondary)"
                    onmouseenter="this.style.color='white'"
                    onmouseleave="this.style.color='var(--color-theme-public-text-secondary)'"
                >
                    Login
                </Link>
                <Link
                    :href="safeRoute('register')"
                    class="text-sm font-bold px-6 py-2 rounded-lg hover:brightness-110 active:scale-95 transition-all"
                    style="background-color: var(--color-theme-public-primary-container); color: var(--color-theme-public-on-primary-container)"
                >
                    Register
                </Link>
            </div>

            <!-- Hamburger mobile -->
            <button
                class="md:hidden flex flex-col gap-1.5 p-2 rounded transition-colors"
                style="color: var(--color-theme-public-text-secondary)"
                aria-label="Toggle menu"
                @click="mobileOpen = !mobileOpen"
            >
                <span
                    class="block w-6 h-0.5 bg-current transition-all duration-300"
                    :class="mobileOpen ? 'rotate-45 translate-y-2' : ''"
                />
                <span
                    class="block w-6 h-0.5 bg-current transition-all duration-300"
                    :class="mobileOpen ? 'opacity-0' : ''"
                />
                <span
                    class="block w-6 h-0.5 bg-current transition-all duration-300"
                    :class="mobileOpen ? '-rotate-45 -translate-y-2' : ''"
                />
            </button>
        </div>

        <!-- Mobile dropdown -->
        <Transition name="slide-down">
            <div
                v-if="mobileOpen"
                class="md:hidden absolute top-18 inset-x-0 border-b border-white/5 p-4 flex flex-col gap-2"
                style="background-color: rgba(21, 28, 38, 0.97); backdrop-filter: blur(20px)"
            >
                <Link
                    v-for="link in navLinks"
                    :key="link.routeName"
                    :href="safeRoute(link.routeName)"
                    class="px-4 py-2 rounded-lg text-sm font-medium transition-colors"
                    :style="isActive(link.routeName)
                        ? 'background-color: rgba(230,33,23,0.1); color: var(--color-theme-public-primary)'
                        : 'color: var(--color-theme-public-text-secondary)'"
                    @click="mobileOpen = false"
                >
                    {{ link.label }}
                </Link>
                <div class="mt-2 pt-2 flex gap-3" style="border-top: 1px solid rgba(255,255,255,0.05)">
                    <Link
                        :href="route('login')"
                        class="flex-1 text-center py-2 rounded-lg text-sm font-medium transition-colors"
                        style="border: 1px solid var(--color-theme-public-outline-variant); color: var(--color-theme-public-text-secondary)"
                    >
                        Login
                    </Link>
                    <Link
                        :href="route('register')"
                        class="flex-1 text-center py-2 rounded-lg text-sm font-bold hover:brightness-110 transition-all"
                        style="background-color: var(--color-theme-public-primary-container); color: var(--color-theme-public-on-primary-container)"
                    >
                        Register
                    </Link>
                </div>
            </div>
        </Transition>
    </nav>

    <!-- ───────────────────────── SLOT ───────────────────────── -->
    <slot />

    <!-- ───────────────────────── FOOTER ───────────────────────── -->
    <footer class="w-full py-8" style="background-color: var(--color-theme-public-surface-container-lowest); border-top: 1px solid var(--color-theme-public-outline-variant)">
        <div class="flex flex-col md:flex-row justify-between items-start px-8 max-w-360 mx-auto gap-6">
            <!-- Brand -->
            <div class="space-y-4">
                <div class="font-display text-xl font-bold" style="color: var(--color-theme-public-text-primary)">AETHERIS</div>
                <p class="text-xs max-w-xs" style="color: var(--color-theme-public-text-disabled)">
                    © 2024 Aetheris WoW. All rights reserved.<br>
                    Not affiliated with Blizzard Entertainment.
                </p>
            </div>

            <!-- Legal links -->
            <div class="grid grid-cols-2 gap-x-12 gap-y-4">
                <a href="#" class="text-xs transition-colors" style="color: var(--color-theme-public-text-disabled)">Terms of Service</a>
                <a href="#" class="text-xs transition-colors" style="color: var(--color-theme-public-text-disabled)">Privacy Policy</a>
                <a href="#" class="text-xs transition-colors" style="color: var(--color-theme-public-text-disabled)">Refund Policy</a>
                <a href="#" class="text-xs transition-colors" style="color: var(--color-theme-public-text-disabled)">Contact Us</a>
            </div>

            <!-- Social icons -->
            <div class="flex gap-4">
                <div
                    class="w-10 h-10 rounded-lg flex items-center justify-center cursor-pointer transition-colors"
                    style="background-color: var(--color-theme-public-surface-container); color: var(--color-theme-public-text-disabled)"
                    onmouseenter="this.style.color='var(--color-theme-public-primary)'"
                    onmouseleave="this.style.color='var(--color-theme-public-text-disabled)'"
                >
                    <span class="material-symbols-outlined text-[20px]">share</span>
                </div>
                <div
                    class="w-10 h-10 rounded-lg flex items-center justify-center cursor-pointer transition-colors"
                    style="background-color: var(--color-theme-public-surface-container); color: var(--color-theme-public-text-disabled)"
                    onmouseenter="this.style.color='var(--color-theme-public-primary)'"
                    onmouseleave="this.style.color='var(--color-theme-public-text-disabled)'"
                >
                    <span class="material-symbols-outlined text-[20px]">language</span>
                </div>
            </div>
        </div>
    </footer>
</template>

<style scoped>
.slide-down-enter-active,
.slide-down-leave-active {
    transition: all 0.2s ease;
}
.slide-down-enter-from,
.slide-down-leave-to {
    opacity: 0;
    transform: translateY(-8px);
}
</style>
