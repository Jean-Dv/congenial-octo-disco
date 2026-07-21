<script setup>
import { Head } from '@inertiajs/vue3';
import downloadsHeroImage from '../../../images/hero-3.webp';

const downloads = [
    {
        title: 'Full Game Client',
        description: 'Complete game installation including all cinematic assets and core expansion files.',
        version: '10.2.7.5',
        size: '84.2 GB',
        icon: 'download_for_offline',
    },
    {
        title: 'Custom Launcher',
        description: 'Our proprietary tool for automatic updates, real-time news, and account management.',
        version: '2.1.0',
        size: '124 MB',
        icon: 'rocket_launch',
    },
    {
        title: 'HD Texture Patch',
        description: 'Enhance your visual experience with upgraded world and character model textures.',
        version: '4.0.1',
        size: '12.8 GB',
        icon: 'texture',
    },
    {
        title: 'Essential Addons',
        description: 'A curated pack of performance-tuned UI addons specifically for the Aetheris realm.',
        version: '1.5.0',
        size: '450 MB',
        icon: 'extension',
    },
];

const setupSteps = [
    {
        number: '01',
        title: 'Create Account',
        description: 'Register your game account on our web portal. This account will be used for both the game and the forums.',
    },
    {
        number: '02',
        title: 'Install Client',
        description: 'Download and install the game client. We recommend using our Custom Launcher for automated updates.',
    },
    {
        number: '03',
        title: 'Enter Aetheris',
        description: "Launch the game, enter your credentials, and select the 'Aetheris Main' realm to begin your adventure.",
    },
];

const requirements = [
    { label: 'OS', value: 'Windows® 10 / 11 64-bit' },
    { label: 'Processor', value: 'Intel® Core™ i7-6700 / AMD Ryzen™ 5 2600' },
    { label: 'Memory', value: '16 GB RAM' },
    { label: 'Graphics', value: 'NVIDIA® GeForce® GTX 1080 / AMD Radeon™ RX 5700 XT' },
    { label: 'DirectX', value: 'Version 12' },
];
</script>

<template>
    <Head title="Downloads" />

    <ThemePublicLayout>
        <main class="downloads-page min-h-screen overflow-hidden">
            <header class="relative flex min-h-153.5 items-end overflow-hidden pt-18">
                <img
                    :src="downloadsHeroImage"
                    alt="A shattered sky illuminated by molten crimson energy."
                    class="absolute inset-0 h-full w-full object-cover opacity-45"
                >
                <div class="hero-overlay absolute inset-0" />

                <div class="relative z-10 mx-auto w-full max-w-360 px-4 pb-24 sm:px-8 lg:px-12">
                    <div class="max-w-3xl">
                        <p class="mb-4 font-mono-data text-[11px] uppercase tracking-[0.2em]" style="color: var(--color-theme-public-primary)">
                            Version 10.2.7 | Dragonflight
                        </p>
                        <h1 class="mb-6 text-5xl font-bold leading-none tracking-tight text-white sm:text-6xl lg:text-7xl">
                            Forge Your Legend
                        </h1>
                        <p class="max-w-2xl text-base leading-relaxed sm:text-lg" style="color: var(--color-theme-public-text-secondary)">
                            Download the latest Aetheris game client and custom tools to join our high-performance community.
                            Experience the most stable and feature-rich private server environment available.
                        </p>
                    </div>
                </div>
            </header>

            <div class="relative z-20 mx-auto -mt-12 max-w-360 px-4 pb-28 sm:px-8 lg:px-12">
                <section aria-labelledby="downloads-heading">
                    <h2 id="downloads-heading" class="sr-only">Available downloads</h2>

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-4">
                        <article
                            v-for="download in downloads"
                            :key="download.title"
                            class="glass-card group flex h-full flex-col rounded-xl p-7 sm:p-8"
                        >
                            <div
                                class="mb-6 flex h-14 w-14 items-center justify-center rounded-lg transition-colors"
                                style="background-color: color-mix(in srgb, var(--color-theme-public-primary) 10%, transparent); color: var(--color-theme-public-primary)"
                            >
                                <span
                                    class="material-symbols-outlined text-[36px]"
                                    style="font-variation-settings: 'FILL' 1, 'wght' 300, 'GRAD' 0, 'opsz' 36"
                                    aria-hidden="true"
                                >{{ download.icon }}</span>
                            </div>

                            <h3 class="mb-2 text-2xl font-semibold text-white">{{ download.title }}</h3>
                            <p class="mb-8 flex-1 text-sm leading-relaxed" style="color: var(--color-theme-public-text-secondary)">
                                {{ download.description }}
                            </p>

                            <div class="space-y-4">
                                <div class="flex items-center justify-between gap-3 font-mono-data text-[10px] uppercase tracking-wide" style="color: var(--color-theme-public-outline)">
                                    <span>Version: {{ download.version }}</span>
                                    <span>Size: {{ download.size }}</span>
                                </div>
                                <button
                                    type="button"
                                    disabled
                                    :aria-label="`${download.title} download is not available yet`"
                                    class="download-button flex w-full cursor-not-allowed items-center justify-center gap-2 rounded-lg py-4 font-bold text-white opacity-60"
                                    style="background-color: var(--color-theme-public-primary-container)"
                                >
                                    <span>Download Now</span>
                                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">download</span>
                                </button>
                            </div>
                        </article>
                    </div>
                </section>

                <section class="mt-28 sm:mt-32" aria-labelledby="getting-started-heading">
                    <div class="mb-16 text-center">
                        <h2 id="getting-started-heading" class="mb-4 text-3xl font-bold text-white sm:text-4xl">Getting Started</h2>
                        <p class="mx-auto max-w-2xl leading-relaxed" style="color: var(--color-theme-public-text-secondary)">
                            Follow these steps to set up your account and begin your journey through the realm of Aetheris.
                        </p>
                    </div>

                    <div class="relative grid grid-cols-1 gap-14 md:grid-cols-3 md:gap-12">
                        <article v-for="step in setupSteps" :key="step.number" class="group relative">
                            <span class="step-number absolute -left-1 -top-9 text-7xl font-bold opacity-25 transition-opacity group-hover:opacity-40 sm:-left-4 sm:text-8xl" aria-hidden="true">
                                {{ step.number }}
                            </span>
                            <div class="relative z-10 pt-8">
                                <h3 class="mb-4 text-xl font-semibold" style="color: var(--color-theme-public-primary)">{{ step.title }}</h3>
                                <p class="text-sm leading-relaxed" style="color: var(--color-theme-public-text-secondary)">{{ step.description }}</p>
                            </div>
                        </article>

                        <div class="absolute left-0 top-1/2 -z-10 hidden h-px w-full bg-linear-to-r from-transparent via-white/10 to-transparent md:block" aria-hidden="true" />
                    </div>
                </section>

                <section class="glass-card relative mt-28 overflow-hidden rounded-2xl p-6 sm:mt-32 sm:p-10 lg:p-12" aria-labelledby="requirements-heading">
                    <div class="crimson-glow -bottom-48 -right-48" aria-hidden="true" />

                    <div class="relative z-10 grid grid-cols-1 gap-12 lg:grid-cols-2 lg:gap-16">
                        <div>
                            <h2 id="requirements-heading" class="mb-8 text-2xl font-semibold text-white">System Requirements</h2>
                            <dl class="space-y-5">
                                <div
                                    v-for="requirement in requirements"
                                    :key="requirement.label"
                                    class="flex flex-col gap-2 border-b border-white/5 pb-4 sm:flex-row sm:items-start sm:justify-between sm:gap-8"
                                >
                                    <dt class="shrink-0 text-sm" style="color: var(--color-theme-public-outline)">{{ requirement.label }}</dt>
                                    <dd class="text-sm text-white sm:text-right">{{ requirement.value }}</dd>
                                </div>
                            </dl>
                        </div>

                        <div class="flex flex-col justify-center">
                            <div class="rounded-xl border p-6 sm:p-8" style="border-color: color-mix(in srgb, var(--color-theme-public-primary) 20%, transparent); background-color: color-mix(in srgb, var(--color-theme-public-primary) 8%, transparent)">
                                <div class="mb-7 flex items-start gap-4">
                                    <span class="material-symbols-outlined" style="color: var(--color-theme-public-primary)" aria-hidden="true">info</span>
                                    <p class="text-sm leading-relaxed text-white">
                                        For the best experience, ensure your GPU drivers are updated to the latest version before launching the client for the first time.
                                    </p>
                                </div>

                                <div class="space-y-3">
                                    <button
                                        type="button"
                                        disabled
                                        aria-label="Detailed troubleshooting is not available yet"
                                        class="utility-link flex w-full cursor-not-allowed items-center justify-between gap-4 py-1 text-left opacity-55"
                                    >
                                        <span class="font-mono-data text-[10px] uppercase tracking-wider">View detailed troubleshooting</span>
                                        <span class="material-symbols-outlined text-[20px]" aria-hidden="true">arrow_forward</span>
                                    </button>
                                    <button
                                        type="button"
                                        disabled
                                        aria-label="Driver utility download is not available yet"
                                        class="utility-link flex w-full cursor-not-allowed items-center justify-between gap-4 py-1 text-left opacity-55"
                                    >
                                        <span class="font-mono-data text-[10px] uppercase tracking-wider">Download driver utility</span>
                                        <span class="material-symbols-outlined text-[20px]" aria-hidden="true">arrow_forward</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </main>
    </ThemePublicLayout>
</template>

<style scoped>
.downloads-page {
    background: #0a0a0a;
    color: var(--color-theme-public-on-surface);
}

.hero-overlay {
    background:
        linear-gradient(0deg, #0a0a0a 0%, rgba(10, 10, 10, 0.68) 42%, rgba(10, 10, 10, 0.08) 100%),
        linear-gradient(90deg, rgba(10, 10, 10, 0.48), transparent 70%);
}

.glass-card {
    border: 1px solid rgba(255, 255, 255, 0.06);
    background: rgba(18, 18, 18, 0.76);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    transition: border-color 300ms ease, box-shadow 300ms ease;
}

.glass-card:hover {
    border-color: color-mix(in srgb, var(--color-theme-public-primary) 30%, transparent);
    box-shadow: 0 0 40px color-mix(in srgb, var(--color-theme-public-primary) 10%, transparent);
}

.crimson-glow {
    position: absolute;
    width: 25rem;
    height: 25rem;
    background: radial-gradient(circle, color-mix(in srgb, var(--color-theme-public-primary) 15%, transparent) 0%, transparent 70%);
    pointer-events: none;
}

.step-number {
    color: transparent;
    font-family: var(--font-mono);
    -webkit-text-stroke: 1px rgba(255, 255, 255, 0.18);
}

.utility-link {
    color: var(--color-theme-public-outline);
}

@media (prefers-reduced-motion: reduce) {
    .glass-card,
    .step-number {
        transition: none;
    }
}
</style>
