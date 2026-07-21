<script setup>
import { Head } from '@inertiajs/vue3';
import newsHeroImage from '../../../images/hero-2.webp';

const featuredArticle = {
    category: 'Major Patch',
    date: 'Nov 24, 2024',
    title: 'Rise of the Shattered Sun: Patch 4.2 Is Live',
    excerpt: 'Delve into the heart of the void. New raids, legendary weapons, and the final chapter of the Cataclysm saga await those brave enough to face the fire.',
    image: newsHeroImage,
};

const articles = [
    {
        id: 1,
        category: 'Community',
        date: '2 hours ago',
        author: 'Archmage Xan',
        title: 'Season 4 Gladiator Rankings Are Now Official',
        excerpt: 'The arena dust has settled. Discover the elite players who secured their spot in the Aetheris Hall of Fame this season.',
        image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuASWuBkEJagybakfWOUsmH5sl5HkzMdW2N7KqSSmvxLJMw_ije-BMnFGuNlLG0lTLta4qziVnMYdr4cUnn54aJOAl2aWV3pyYIC74av85fOpkFeeoBlANhNIe2GLbOAaY7mZrZDkksHRlqT5hxv_J4RvoVjAilk4QDFIkydSSvG8KIZkK3_Sk3MILJzwpS9e_eUR7WidTSppELqS2BXdoL-D5IbHQzc9mLUDpQAc5DSHvGZgHrM7jDz0lrqsUtzjCpkHdDhcDMoiNE',
        alt: 'A dragon rider soaring through a lightning storm.',
    },
    {
        id: 2,
        category: 'Event',
        date: 'Yesterday',
        author: 'Loremaster Elya',
        title: 'Winter Veil: The Frozen Throne Returns',
        excerpt: 'Celebrate the holidays with limited-time mounts and a special world boss challenge in Northrend. Available until Jan 5th.',
        image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuASSDjg4f6ag_-LtqQ080fZrCnLpHQ7PaLvsJipR8rChaSNR139azB-8a5mwfnPDxr5uBwwkDqBd9KX5LBGNZXeV2J5JidBnowCtlQTLBubO8W_x_wgWmL4lVlxpl5kOpR2TN5IhWdkV60Pm-Lc58Nu88vHb0rDgX8CZm2S65GoPf9ldc9vqs-4FLINbZRl3AO0zCLOAaePuRt3RJ0ps6_95xl2UxjJvc-emhYtq0IT5TGmF2MndnYyVopC7jufx6uFs7cXY8a74Nk',
        alt: 'Armored warriors facing a fiery demon in a cavern.',
    },
    {
        id: 3,
        category: 'Patch',
        date: '3 days ago',
        author: 'Dev Morgan',
        title: 'Class Balance Changes: Hotfix 4.2.1',
        excerpt: 'Detailed breakdown of the latest adjustments for Paladins and Rogues to ensure competitive integrity in PvE environments.',
        image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuCEUBuG9LJBMs0XWOvhQWRv0lF2SEibu-zztreNDM-VHQ5yzkVDqb3Bto9W8DD1yrnKsc3dCjJRryGEiFVZvtD4QvsV_wtZ-b1vvYolaezU1H-_MW7byAdc1mf6ekfEP8kb_caR5szH7tXiX6kQbD1qPCvWb_dggeZirhrd7gdR25BGOH17ENrKbaToSGvudLFInMmal_VEDiOi0qJryGdRq-aoCONXlO1cfJ0WqdH_Pbvh1iRiMGk-kxrG7Z4tAIyNWKQXD7ZnnY',
        alt: 'A fantasy forge glowing with molten metal and runes.',
    },
    {
        id: 4,
        category: 'Community',
        date: 'Nov 18, 2024',
        author: 'Community Team',
        title: 'Fan Art Spotlight: Visions of Azeroth',
        excerpt: "Showcasing this month's most stunning community creations, from 3D models to traditional oil paintings.",
        image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuC6nm7UxBoyv5HSacPM3Z8YU43Ua4dznXx8x77Z5gpwD6I4HsmN3qABZUAFnf81dK5F_jdzAnuV2MA8hzlGqGKGoWMujEwvVnRKYWxV2OthnXMR1ShZP0Z8nUtQzwA8bMrPOQDnKcBH_ckaOBz9CCh4oWOaXRXZPLE2JOVC3nk8GTs5Rlcm1CppuPI3KsVOZJmJhY8ydbaQjgARveE5vuRfy1xM9u22L3l2WbKu7xyKjutVMKJH6sJ-wZ54_uwTmu7PvGTmglRqCAE',
        alt: 'A bioluminescent forest reflected in a lake at night.',
    },
];

const categories = [
    'All News',
    'Patches & Updates',
    'Events',
    'Community Highlights',
    'Technical Support',
];
</script>

<template>
    <Head title="News" />

    <ThemePublicLayout>
        <main class="min-h-screen pt-18" style="background-color: var(--color-theme-public-bg)">
            <section class="relative min-h-135 overflow-hidden sm:min-h-150">
                <img
                    :src="featuredArticle.image"
                    alt="A shattered citadel lit by molten crimson fissures."
                    class="absolute inset-0 h-full w-full object-cover motion-safe:scale-105"
                >
                <div class="news-hero-overlay absolute inset-0" />

                <div class="relative z-10 mx-auto flex min-h-135 max-w-360 items-end px-4 pb-14 sm:min-h-150 sm:px-8 sm:pb-20 lg:px-12">
                    <div class="max-w-3xl space-y-5">
                        <div class="flex flex-wrap items-center gap-3 font-mono-data text-[11px] uppercase tracking-[0.18em]">
                            <span
                                class="rounded-sm px-3 py-1.5 font-bold"
                                style="background-color: var(--color-theme-public-primary-container); color: var(--color-theme-public-on-primary-container)"
                            >
                                {{ featuredArticle.category }}
                            </span>
                            <span style="color: var(--color-theme-public-text-secondary)">{{ featuredArticle.date }}</span>
                        </div>

                        <h1 class="max-w-3xl text-4xl font-bold leading-[1.05] tracking-tight text-white sm:text-5xl lg:text-6xl">
                            {{ featuredArticle.title }}
                        </h1>
                        <p class="max-w-2xl text-base leading-relaxed sm:text-lg" style="color: var(--color-theme-public-text-secondary)">
                            {{ featuredArticle.excerpt }}
                        </p>

                        <button
                            type="button"
                            disabled
                            aria-label="Full patch notes will be available when the News module launches"
                            class="flex cursor-not-allowed items-center gap-2 rounded-sm px-7 py-3 font-bold opacity-70"
                            style="background-color: var(--color-theme-public-text-primary); color: var(--color-theme-public-bg)"
                        >
                            Read full patch notes
                            <span class="material-symbols-outlined text-[20px]" aria-hidden="true">arrow_forward</span>
                        </button>
                    </div>
                </div>
            </section>

            <div class="mx-auto flex max-w-360 flex-col gap-12 px-4 py-16 sm:px-8 lg:flex-row lg:px-12 lg:py-20">
                <section class="min-w-0 flex-1" aria-labelledby="archive-heading">
                    <div class="mb-10 flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="mb-2 font-mono-data text-[11px] uppercase tracking-[0.2em]" style="color: var(--color-theme-public-primary)">
                                Chronicle
                            </p>
                            <h2 id="archive-heading" class="text-3xl font-bold text-white sm:text-4xl">Latest Archive</h2>
                        </div>

                        <label class="flex items-center gap-2 font-mono-data text-[11px] uppercase tracking-wider" style="color: var(--color-theme-public-text-disabled)">
                            Sort by
                            <select
                                disabled
                                aria-label="News sorting will be available when the News module launches"
                                class="cursor-not-allowed border-0 bg-transparent py-1 pr-8 font-bold opacity-70 focus:ring-0"
                                style="color: var(--color-theme-public-primary)"
                            >
                                <option>Newest</option>
                            </select>
                        </label>
                    </div>

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <article
                            v-for="article in articles"
                            :key="article.id"
                            class="news-card group overflow-hidden rounded-lg border transition-colors duration-300"
                        >
                            <div class="relative aspect-video overflow-hidden">
                                <img
                                    :src="article.image"
                                    :alt="article.alt"
                                    loading="lazy"
                                    class="h-full w-full object-cover transition-transform duration-500 motion-safe:group-hover:scale-105"
                                >
                                <span
                                    class="absolute left-4 top-4 rounded-sm border px-3 py-1 font-mono-data text-[10px] font-bold uppercase tracking-[0.16em] backdrop-blur-md"
                                    style="border-color: color-mix(in srgb, var(--color-theme-public-primary) 30%, transparent); background-color: rgba(8, 15, 24, 0.78); color: var(--color-theme-public-primary)"
                                >
                                    {{ article.category }}
                                </span>
                            </div>

                            <div class="space-y-3 p-6">
                                <div class="flex flex-wrap items-center justify-between gap-2 font-mono-data text-[10px] uppercase tracking-wider" style="color: var(--color-theme-public-text-disabled)">
                                    <time>{{ article.date }}</time>
                                    <span>By {{ article.author }}</span>
                                </div>
                                <h3 class="text-xl font-semibold leading-snug text-white transition-colors group-hover:text-(--color-theme-public-primary)">
                                    {{ article.title }}
                                </h3>
                                <p class="line-clamp-2 text-sm leading-relaxed" style="color: var(--color-theme-public-text-secondary)">
                                    {{ article.excerpt }}
                                </p>
                            </div>
                        </article>
                    </div>

                    <nav class="mt-12 flex items-center justify-center gap-2 sm:gap-3" aria-label="Archive pagination unavailable">
                        <button type="button" disabled aria-label="Previous page unavailable" class="pagination-button cursor-not-allowed opacity-40">
                            <span class="material-symbols-outlined" aria-hidden="true">chevron_left</span>
                        </button>
                        <button type="button" disabled aria-current="page" class="pagination-button cursor-not-allowed border-transparent text-white" style="background-color: var(--color-theme-public-primary-container)">1</button>
                        <button type="button" disabled class="pagination-button cursor-not-allowed opacity-40">2</button>
                        <button type="button" disabled class="pagination-button cursor-not-allowed opacity-40">3</button>
                        <button type="button" disabled aria-label="Next page unavailable" class="pagination-button cursor-not-allowed opacity-40">
                            <span class="material-symbols-outlined" aria-hidden="true">chevron_right</span>
                        </button>
                    </nav>
                </section>

                <aside class="w-full shrink-0 space-y-10 lg:w-80" aria-label="News archive information">
                    <section aria-labelledby="categories-heading">
                        <h2 id="categories-heading" class="sidebar-heading">Categories</h2>
                        <div class="mt-5 flex flex-col gap-1">
                            <button
                                v-for="(category, index) in categories"
                                :key="category"
                                type="button"
                                disabled
                                class="relative cursor-not-allowed border-l-2 px-5 py-2.5 text-left text-sm"
                                :style="index === 0
                                    ? 'border-color: var(--color-theme-public-primary); background-color: rgba(230,33,23,0.08); color: var(--color-theme-public-primary)'
                                    : 'border-color: transparent; color: var(--color-theme-public-text-secondary)'"
                            >
                                {{ category }}
                            </button>
                        </div>
                    </section>

                    <section class="discord-card relative overflow-hidden rounded-lg p-7">
                        <span class="material-symbols-outlined absolute -bottom-8 -right-7 text-[150px] opacity-[0.06]" aria-hidden="true">forum</span>
                        <div class="relative z-10 space-y-4">
                            <h2 class="text-2xl font-semibold text-white">Join the Legion</h2>
                            <p class="text-sm leading-relaxed" style="color: var(--color-theme-public-text-secondary)">
                                Connect with fellow players on our Discord server. Community access will be linked when it becomes available.
                            </p>
                            <button
                                type="button"
                                disabled
                                aria-label="Discord invitation is not configured"
                                class="flex w-full cursor-not-allowed items-center justify-center gap-2 rounded-sm bg-[#5865f2] py-3.5 font-bold text-white opacity-65"
                            >
                                <span class="material-symbols-outlined" aria-hidden="true">forum</span>
                                Join Discord
                            </button>
                        </div>
                    </section>

                    <section aria-labelledby="newsletter-heading">
                        <h2 id="newsletter-heading" class="sidebar-heading">Stay Informed</h2>
                        <p class="mt-5 text-sm leading-relaxed" style="color: var(--color-theme-public-text-secondary)">
                            Weekly news digests will be available with the News module.
                        </p>
                        <form class="mt-4 space-y-3" @submit.prevent>
                            <label class="sr-only" for="news-email">Email address</label>
                            <input
                                id="news-email"
                                type="email"
                                disabled
                                placeholder="Email address"
                                class="w-full cursor-not-allowed rounded-sm border px-4 py-3.5 text-sm opacity-60"
                                style="border-color: var(--color-theme-public-outline-variant); background-color: var(--color-theme-public-surface-container-low); color: var(--color-theme-public-on-surface)"
                            >
                            <button
                                type="submit"
                                disabled
                                class="w-full cursor-not-allowed rounded-sm py-3 font-bold opacity-60"
                                style="background-color: var(--color-theme-public-text-primary); color: var(--color-theme-public-bg)"
                            >
                                Subscribe
                            </button>
                        </form>
                    </section>
                </aside>
            </div>
        </main>
    </ThemePublicLayout>
</template>

<style scoped>
.news-hero-overlay {
    background:
        linear-gradient(90deg, rgba(8, 15, 24, 0.92) 0%, rgba(8, 15, 24, 0.55) 52%, rgba(8, 15, 24, 0.15) 100%),
        linear-gradient(0deg, var(--color-theme-public-bg) 0%, rgba(13, 20, 30, 0.18) 62%, rgba(13, 20, 30, 0.36) 100%);
}

.news-card {
    border-color: rgba(255, 255, 255, 0.06);
    background: rgba(21, 28, 38, 0.86);
}

.news-card:hover {
    border-color: rgba(230, 33, 23, 0.3);
}

.pagination-button {
    display: flex;
    width: 2.75rem;
    height: 2.75rem;
    align-items: center;
    justify-content: center;
    border: 1px solid var(--color-theme-public-outline-variant);
    border-radius: 0.125rem;
    color: var(--color-theme-public-text-secondary);
    font-weight: 700;
}

.sidebar-heading {
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    padding-bottom: 1rem;
    color: var(--color-theme-public-text-secondary);
    font-family: var(--font-mono);
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.16em;
    text-transform: uppercase;
}

.discord-card {
    border: 1px solid rgba(230, 33, 23, 0.2);
    background:
        linear-gradient(145deg, rgba(230, 33, 23, 0.13), rgba(136, 206, 255, 0.08)),
        var(--color-theme-public-surface-container-high);
    box-shadow: 0 0 32px rgba(230, 33, 23, 0.1);
}

@media (max-width: 639px) {
    .news-hero-overlay {
        background:
            linear-gradient(0deg, var(--color-theme-public-bg) 0%, rgba(8, 15, 24, 0.62) 68%, rgba(8, 15, 24, 0.48) 100%);
    }
}
</style>
