<script setup>
import { Link } from '@inertiajs/vue3';
import homeHeroImage from '../../../images/hero-1.webp';

function safeRoute(name) {
    try {
        return route(name);
    } catch {
        return '#';
    }
}
const props = defineProps({
    serverStats: {
        type: Object,
        default: () => ({
            online:  0,
            peak:    0,
            uptime:  '–',
            version: '–',
            realm:   '–',
            latency: '–',
        }),
    },
    realmStatus: {
        type: Object,
        default: () => ({
            name:           'Sin reino configurado',
            configured:     false,
            online:         false,
            latencyMs:      null,
            latencyStable:  false,
            alliancePct:    0,
            hordePct:       0,
            latencyHistory: [],
        }),
    },
    latestNews: {
        type: Array,
        default: () => [],
    },
    newsEnabled: {
        type: Boolean,
        default: false,
    },
});

// ─── Hero stats config ───────────────────────────────────────────────
const heroStats = [
    { icon: 'group',      label: 'Online',   value: props.serverStats.online },
    { icon: 'trending_up',label: 'Peak',     value: props.serverStats.peak },
    { icon: 'update',     label: 'Uptime',   value: props.serverStats.uptime },
    { icon: 'layers',     label: 'Version',  value: "4.0.6" },
    { icon: 'dns',        label: 'Realm',    value: props.serverStats.realm },
    { icon: 'bolt',       label: 'Latencia', value: props.serverStats.latency },
];

// ─── Latency bar max for normalization ──────────────────────────────
const maxLatency = Math.max(...(props.realmStatus.latencyHistory ?? [1]), 1);

// ─── Category color helper ───────────────────────────────────────────
function categoryStyle(type) {
    if (type === 'secondary') {
        return {
            badge: 'background: rgba(136,206,255,0.2); color: var(--color-theme-public-secondary)',
            title: 'color: var(--color-theme-public-secondary)',
        };
    }
    return {
        badge: 'background: rgba(230,33,23,0.2); color: var(--color-theme-public-primary)',
        title: 'color: var(--color-theme-public-primary)',
    };
}
</script>

<template>
    <ThemePublicLayout>
    <!-- ═══════════════════════════════════════════════════════════ -->
    <!-- HERO SECTION                                               -->
    <!-- ═══════════════════════════════════════════════════════════ -->
    <section class="relative min-h-screen flex items-center pt-18 overflow-hidden">

        <!-- Background image + overlay -->
        <div class="absolute inset-0 z-0">
            <!-- Gradient overlay -->
            <div
                class="absolute inset-0 z-10"
                style="background: linear-gradient(180deg, rgba(13,20,30,0.4) 0%, rgba(13,20,30,0.85) 100%)"
            />
            <!-- Image -->
            <div
                class="w-full h-full bg-cover bg-center"
                :style="{ backgroundImage: `url(${homeHeroImage})` }"
                aria-hidden="true"
            />
        </div>

        <!-- Hero content -->
        <div class="relative z-20 max-w-360 mx-auto px-8 w-full py-20">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <div class="lg:col-span-8 flex flex-col justify-center space-y-8">

                    <!-- Badge + headline + description -->
                    <div class="space-y-4">
                        <span
                            class="inline-block px-3 py-1 rounded-full text-[11px] font-bold uppercase tracking-widest"
                            style="background: rgba(230,33,23,0.1); border: 1px solid rgba(230,33,23,0.2); color: var(--color-theme-public-primary)"
                        >
                            TEMPORADA 1: ASCENSO DE ELUNE
                        </span>

                        <h1
                            class="text-5xl lg:text-6xl font-bold leading-tight tracking-tight text-white max-w-3xl"
                            style="letter-spacing: -0.02em"
                        >
                            UN MUNDO DE AVENTURAS SIN LÍMITES
                        </h1>

                        <p class="text-base leading-relaxed max-w-xl" style="color: var(--color-theme-public-text-secondary)">
                            Únete a la comunidad de rol y combate más avanzada. Experimenta el WoW
                            como nunca antes con tecnología de latencia cero y contenido exclusivo.
                        </p>
                    </div>

                    <!-- CTAs -->
                    <div class="flex flex-wrap gap-4">
                        <Link
                            :href="safeRoute('register')"
                            class="flex items-center gap-2 px-8 py-4 rounded-lg font-bold text-base hover:brightness-110 active:scale-95 transition-all"
                            style="background-color: var(--color-theme-public-primary-container); color: var(--color-theme-public-on-primary-container); box-shadow: 0 8px 30px rgba(230,33,23,0.2)"
                        >
                            <span class="material-symbols-outlined">play_arrow</span>
                            Jugar Ahora
                        </Link>

                        <Link
                            :href="safeRoute('public.downloads.client_complete')"
                            class="flex items-center gap-2 px-8 py-4 rounded-lg font-bold text-base hover:bg-white/5 active:scale-95 transition-all text-white"
                            style="border: 1px solid var(--color-theme-public-outline-variant)"
                        >
                            <span class="material-symbols-outlined">download</span>
                            Descargar el cliente
                        </Link>
                    </div>

                    <!-- Hero stats grid -->
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 pt-4">
                        <div
                            v-for="stat in heroStats"
                            :key="stat.label"
                            class="landing-glass p-4 rounded-lg"
                        >
                            <div class="flex items-center gap-2 mb-1" style="color: var(--color-theme-public-primary)">
                                <span class="material-symbols-outlined text-[18px]">{{ stat.icon }}</span>
                                <span class="text-[11px] font-semibold uppercase tracking-wider" style="color: var(--color-theme-public-text-disabled)">
                                    {{ stat.label }}
                                </span>
                            </div>
                            <div class="text-xl font-semibold text-white">{{ stat.value }}</div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <!-- ═══════════════════════════════════════════════════════════ -->
    <!-- MAIN CONTENT                                               -->
    <!-- ═══════════════════════════════════════════════════════════ -->
    <main
        class="max-w-360 mx-auto px-8 py-12"
    >
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            <!-- ─────────────────────────────────────────── -->
            <!-- LEFT: News Section (8 cols)                 -->
            <!-- ─────────────────────────────────────────── -->
            <section v-if="newsEnabled" class="lg:col-span-8 space-y-4">

                <!-- Section header -->
                <div class="flex justify-between items-end mb-8">
                    <div>
                        <h2 class="text-4xl font-bold text-white" style="letter-spacing: -0.01em">Últimas Noticias</h2>
                        <div class="h-1 w-12 mt-2 rounded-full" style="background-color: var(--color-theme-public-primary)" />
                    </div>
                    <Link href="/news" class="text-sm font-medium transition-colors hover:underline" style="color: var(--color-theme-public-primary)">
                        Ver todas las noticias
                    </Link>
                </div>

                <!-- News cards -->
                <div class="space-y-6">
                    <Link
                        v-for="news in latestNews"
                        :key="news.id"
                        :href="`/news/${news.slug}`"
                        class="landing-glass rounded-xl overflow-hidden flex flex-col md:flex-row group cursor-pointer transition-all duration-300 hover:border-[rgba(230,33,23,0.3)]"
                        style="border: 1px solid rgba(255,255,255,0.05)"
                    >
                        <!-- Thumbnail -->
                        <div class="w-full md:w-64 h-48 md:h-auto overflow-hidden shrink-0">
                            <div
                                class="w-full h-full bg-cover bg-center transition-transform duration-500 group-hover:scale-105"
                                :style="`background-image: url('${news.coverUrl}')`"
                                :aria-label="news.title"
                            />
                        </div>

                        <!-- Content -->
                        <div class="p-6 flex flex-col justify-between grow">
                            <div>
                                <!-- Category + date -->
                                <div class="flex items-center gap-3 mb-3">
                                    <span
                                        class="text-[10px] font-bold px-2 py-0.5 rounded uppercase tracking-wider"
                                        :style="categoryStyle('primary').badge"
                                    >
                                        {{ news.category.name }}
                                    </span>
                                    <span class="text-xs font-medium" style="color: var(--color-theme-public-text-disabled)">
                                        {{ news.publishedAtLabel }}
                                    </span>
                                </div>

                                <!-- Title -->
                                <h3
                                    class="text-xl font-semibold text-white mb-2 transition-colors group-hover:brightness-125"
                                    :style="{ '--hover-color': categoryStyle('primary').title }"
                                >
                                    {{ news.title }}
                                </h3>

                                <!-- Excerpt -->
                                <p class="text-sm leading-relaxed line-clamp-2" style="color: var(--color-theme-public-text-secondary)">
                                    {{ news.excerpt }}
                                </p>
                            </div>

                            <!-- Footer row -->
                            <div class="mt-4 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div
                                        class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-bold text-white"
                                        style="background-color: var(--color-theme-public-surface-container-highest)"
                                    >
                                        {{ news.authorInitials }}
                                    </div>
                                    <span class="text-xs" style="color: var(--color-theme-public-text-disabled)">{{ news.author }}</span>
                                </div>
                                <span
                                    class="font-bold text-xs flex items-center gap-1"
                                    style="color: var(--color-theme-public-primary)"
                                >
                                    Leer Más
                                    <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                                </span>
                            </div>
                        </div>
                    </Link>

                    <div v-if="latestNews.length === 0" class="landing-glass rounded-xl p-8 text-center text-sm" style="color: var(--color-theme-public-text-secondary)">
                        Todavia no hay noticias publicadas.
                    </div>
                </div>
            </section>

            <!-- ─────────────────────────────────────────── -->
            <!-- RIGHT: Server Status (4 cols)               -->
            <!-- ─────────────────────────────────────────── -->
            <aside class="lg:col-span-4 space-y-6">

                <!-- Section header -->
                <div>
                    <h2 class="text-xl font-semibold text-white">Estado del Reino</h2>
                    <div class="h-1 w-12 mt-2 rounded-full" style="background-color: var(--color-theme-public-primary)" />
                </div>

                <!-- Status card -->
                <div class="landing-glass p-6 rounded-xl space-y-6">

                    <!-- Online indicator -->
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-sm" style="color: var(--color-theme-public-text-disabled)">{{ realmStatus.name }}</div>
                            <div class="text-xl font-semibold text-white">
                                {{ !realmStatus.configured ? 'NO CONFIGURADO' : (realmStatus.online ? 'ONLINE' : 'OFFLINE') }}
                            </div>
                        </div>
                        <div
                            class="w-12 h-12 rounded-full flex items-center justify-center relative"
                            :style="`border: 4px solid ${realmStatus.online ? 'rgba(61,220,151,0.2)' : 'rgba(255,93,115,0.2)'}`"
                        >
                            <div
                                class="w-4 h-4 rounded-full animate-pulse"
                                :style="`background-color: ${realmStatus.online ? 'var(--color-theme-public-status-success)' : 'var(--color-theme-public-status-error)'}`"
                            />
                            <div
                                class="absolute inset-0 rounded-full border-t-2 animate-spin"
                                :style="`border-color: ${realmStatus.online ? 'var(--color-theme-public-status-success)' : 'var(--color-theme-public-status-error)'}; animation-duration: 3s`"
                            />
                        </div>
                    </div>

                    <!-- Latency graph -->
                    <div class="space-y-2">
                        <div class="flex justify-between text-xs" style="color: var(--color-theme-public-text-disabled)">
                            <span>Latencia Realmlist</span>
                            <span :style="`color: ${realmStatus.latencyStable ? 'var(--color-theme-public-status-success)' : 'var(--color-theme-public-status-warning)'}`">
                                <template v-if="realmStatus.latencyMs !== null">
                                    {{ realmStatus.latencyStable ? 'Estable' : 'Inestable' }} ({{ realmStatus.latencyMs }}ms)
                                </template>
                                <template v-else>No disponible</template>
                            </span>
                        </div>
                        <div class="h-16 w-full flex items-end gap-1">
                            <div
                                v-for="(bar, idx) in realmStatus.latencyHistory"
                                :key="idx"
                                class="flex-1 rounded-t transition-all duration-500"
                                :style="`height: ${Math.round((bar / maxLatency) * 100)}%; background-color: rgba(230,33,23,${0.2 + (bar / maxLatency) * 0.6})`"
                            />
                        </div>
                    </div>

                    <!-- Faction balance -->
                    <div class="border-t pt-6 space-y-4" style="border-color: rgba(255,255,255,0.05)">
                        <!-- Alliance -->
                        <div class="flex justify-between items-center">
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-[20px]" style="color: var(--color-theme-public-text-disabled)">person</span>
                                <span class="text-sm" style="color: var(--color-theme-public-text-secondary)">Alianza</span>
                            </div>
                            <span class="font-bold text-sm" style="color: var(--color-theme-public-primary)">{{ realmStatus.alliancePct }}%</span>
                        </div>
                        <div class="w-full h-1 rounded-full overflow-hidden" style="background-color: var(--color-theme-public-surface-container-highest)">
                            <div
                                class="h-full rounded-full transition-all duration-700"
                                :style="`width: ${realmStatus.alliancePct}%; background-color: var(--color-theme-public-primary)`"
                            />
                        </div>

                        <!-- Horde -->
                        <div class="flex justify-between items-center">
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-[20px]" style="color: var(--color-theme-public-text-disabled)">person</span>
                                <span class="text-sm" style="color: var(--color-theme-public-text-secondary)">Horda</span>
                            </div>
                            <span class="font-bold text-sm" style="color: var(--color-theme-public-status-error)">{{ realmStatus.hordePct }}%</span>
                        </div>
                        <div class="w-full h-1 rounded-full overflow-hidden" style="background-color: var(--color-theme-public-surface-container-highest)">
                            <div
                                class="h-full rounded-full transition-all duration-700"
                                :style="`width: ${realmStatus.hordePct}%; background-color: var(--color-theme-public-status-error)`"
                            />
                        </div>
                    </div>

                    <!-- Detail stats button -->
                    <button
                        class="w-full py-3 font-bold rounded-lg text-sm flex items-center justify-center gap-2 text-white transition-colors hover:brightness-125"
                        style="background-color: var(--color-theme-public-surface-container-highest)"
                    >
                        <span class="material-symbols-outlined">insert_chart</span>
                        Estadísticas Detalladas
                    </button>
                </div>

                <!-- Discord CTA card -->
                <div
                    class="p-6 rounded-xl"
                    style="background: linear-gradient(135deg, rgba(230,33,23,0.15), rgba(136,206,255,0.15)); border: 1px solid rgba(230,33,23,0.2)"
                >
                    <h3 class="text-lg font-semibold text-white mb-2">Únete a Discord</h3>
                    <p class="text-sm mb-4" style="color: var(--color-theme-public-text-secondary)">
                        Recibe notificaciones instantáneas de eventos y actualizaciones.
                    </p>
                    <button
                        class="w-full py-3 text-white font-bold rounded-lg text-sm flex items-center justify-center gap-2 hover:brightness-110 transition-all"
                        style="background-color: #5865F2"
                    >
                        <!-- Discord SVG icon -->
                        <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                            <path d="M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0 12.64 12.64 0 0 0-.617-1.25.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 0 0 .031.057 19.9 19.9 0 0 0 5.993 3.03.078.078 0 0 0 .084-.028 14.09 14.09 0 0 0 1.226-1.994.076.076 0 0 0-.041-.106 13.107 13.107 0 0 1-1.872-.892.077.077 0 0 1-.008-.128 10.2 10.2 0 0 0 .372-.292.074.074 0 0 1 .077-.01c3.928 1.793 8.18 1.793 12.062 0a.074.074 0 0 1 .078.01c.12.098.246.198.373.292a.077.077 0 0 1-.006.127 12.299 12.299 0 0 1-1.873.892.077.077 0 0 0-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028 19.839 19.839 0 0 0 6.002-3.03.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.03zM8.02 15.33c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.956-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.956 2.418-2.157 2.418zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.955-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.946 2.418-2.157 2.418z"/>
                        </svg>
                        Conectar ahora
                    </button>
                </div>

            </aside>
        </div>
    </main>
    </ThemePublicLayout>
</template>

<style scoped>
/* Pulse animation for the status dot */
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50%       { opacity: 0.6; }
}
.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

/* Clamp excerpt text */
.line-clamp-2 {
    display: -webkit-box;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
