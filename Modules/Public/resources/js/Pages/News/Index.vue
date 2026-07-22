<script setup>
import { Head, Link } from '@inertiajs/vue3';
import newsHeroImage from '../../../images/hero-2.webp';

defineProps({
    featuredArticle: { type: Object, default: null },
    articles: { type: Object, default: () => ({ data: [], links: [] }) },
    categories: { type: Array, default: () => [] },
    activeCategory: { type: String, default: null },
});
</script>

<template>
    <Head title="Noticias" />

    <ThemePublicLayout>
        <main class="min-h-screen pt-18" style="background-color: var(--color-theme-public-bg)">
            <section class="relative min-h-135 overflow-hidden sm:min-h-150">
                <img :src="featuredArticle?.coverUrl || newsHeroImage" :alt="featuredArticle?.title || 'Noticias del servidor'" class="absolute inset-0 h-full w-full object-cover motion-safe:scale-105">
                <div class="news-hero-overlay absolute inset-0" />

                <div class="relative z-10 mx-auto flex min-h-135 max-w-360 items-end px-4 pb-14 sm:min-h-150 sm:px-8 sm:pb-20 lg:px-12">
                    <div v-if="featuredArticle" class="max-w-3xl space-y-5">
                        <div class="flex flex-wrap items-center gap-3 font-mono-data text-[11px] uppercase tracking-[0.18em]">
                            <span class="rounded-sm px-3 py-1.5 font-bold" style="background-color: var(--color-theme-public-primary-container); color: var(--color-theme-public-on-primary-container)">{{ featuredArticle.category.name }}</span>
                            <time :datetime="featuredArticle.publishedAt" style="color: var(--color-theme-public-text-secondary)">{{ featuredArticle.publishedAtLabel }}</time>
                        </div>
                        <h1 class="max-w-3xl text-4xl font-bold leading-[1.05] tracking-tight text-white sm:text-5xl lg:text-6xl">{{ featuredArticle.title }}</h1>
                        <p class="max-w-2xl text-base leading-relaxed sm:text-lg" style="color: var(--color-theme-public-text-secondary)">{{ featuredArticle.excerpt }}</p>
                        <Link :href="`/news/${featuredArticle.slug}`" class="inline-flex items-center gap-2 rounded-sm px-7 py-3 font-bold" style="background-color: var(--color-theme-public-text-primary); color: var(--color-theme-public-bg)">
                            Leer noticia completa <span class="material-symbols-outlined text-[20px]">arrow_forward</span>
                        </Link>
                    </div>
                    <div v-else class="max-w-2xl space-y-4">
                        <p class="font-mono-data text-xs uppercase tracking-[0.2em]" style="color: var(--color-theme-public-primary)">Cronicas de Moonshard</p>
                        <h1 class="text-5xl font-bold text-white sm:text-6xl">Noticias del servidor</h1>
                        <p style="color: var(--color-theme-public-text-secondary)">Actualizaciones, eventos y novedades de nuestra comunidad.</p>
                    </div>
                </div>
            </section>

            <div class="mx-auto flex max-w-360 flex-col gap-12 px-4 py-16 sm:px-8 lg:flex-row lg:px-12 lg:py-20">
                <section class="min-w-0 flex-1" aria-labelledby="archive-heading">
                    <div class="mb-10">
                        <p class="mb-2 font-mono-data text-[11px] uppercase tracking-[0.2em]" style="color: var(--color-theme-public-primary)">Archivo</p>
                        <h2 id="archive-heading" class="text-3xl font-bold text-white sm:text-4xl">Ultimas noticias</h2>
                    </div>

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <Link v-for="article in articles.data" :key="article.id" :href="`/news/${article.slug}`" class="news-card group overflow-hidden rounded-lg border transition-colors duration-300">
                            <div class="relative aspect-video overflow-hidden">
                                <img :src="article.coverUrl" :alt="article.title" loading="lazy" class="h-full w-full object-cover transition-transform duration-500 motion-safe:group-hover:scale-105">
                                <span class="absolute left-4 top-4 rounded-sm border px-3 py-1 font-mono-data text-[10px] font-bold uppercase tracking-[0.16em] backdrop-blur-md" style="border-color: color-mix(in srgb, var(--color-theme-public-primary) 30%, transparent); background-color: rgba(8, 15, 24, 0.78); color: var(--color-theme-public-primary)">{{ article.category.name }}</span>
                            </div>
                            <div class="space-y-3 p-6">
                                <div class="flex flex-wrap items-center justify-between gap-2 font-mono-data text-[10px] uppercase tracking-wider" style="color: var(--color-theme-public-text-disabled)">
                                    <time :datetime="article.publishedAt">{{ article.publishedAtLabel }}</time><span>Por {{ article.author }}</span>
                                </div>
                                <h3 class="text-xl font-semibold leading-snug text-white transition-colors group-hover:text-(--color-theme-public-primary)">{{ article.title }}</h3>
                                <p class="line-clamp-2 text-sm leading-relaxed" style="color: var(--color-theme-public-text-secondary)">{{ article.excerpt }}</p>
                            </div>
                        </Link>
                    </div>

                    <div v-if="articles.data.length === 0" class="news-card rounded-lg border p-10 text-center text-sm" style="color: var(--color-theme-public-text-secondary)">No hay noticias publicadas para esta categoria.</div>

                    <nav v-if="articles.last_page > 1" class="mt-12 flex flex-wrap items-center justify-center gap-2" aria-label="Paginacion de noticias">
                        <component :is="link.url ? Link : 'span'" v-for="link in articles.links" :key="link.label" :href="link.url || undefined" class="pagination-button" :class="{ 'border-transparent text-white': link.active, 'cursor-not-allowed opacity-40': !link.url }" :style="link.active ? 'background-color: var(--color-theme-public-primary-container)' : ''" v-html="link.label" />
                    </nav>
                </section>

                <aside class="w-full shrink-0 space-y-10 lg:w-80" aria-label="Categorias de noticias">
                    <section>
                        <h2 class="sidebar-heading">Categorias</h2>
                        <div class="mt-5 flex flex-col gap-1">
                            <Link href="/news" class="category-link" :class="{ active: !activeCategory }">Todas las noticias</Link>
                            <Link v-for="category in categories" :key="category.slug" :href="`/news?category=${category.slug}`" class="category-link" :class="{ active: activeCategory === category.slug }">
                                <span>{{ category.name }}</span><span class="font-mono-data text-xs">{{ category.articles_count }}</span>
                            </Link>
                        </div>
                    </section>
                </aside>
            </div>
        </main>
    </ThemePublicLayout>
</template>

<style scoped>
.news-hero-overlay { background: linear-gradient(90deg, rgba(8,15,24,.92), rgba(8,15,24,.52) 55%, rgba(8,15,24,.15)), linear-gradient(0deg, var(--color-theme-public-bg), rgba(13,20,30,.18) 62%); }
.news-card { border-color: rgba(255,255,255,.06); background: rgba(21,28,38,.86); }
.news-card:hover { border-color: rgba(230,33,23,.3); }
.pagination-button { display:flex; min-width:2.75rem; height:2.75rem; padding:0 .75rem; align-items:center; justify-content:center; border:1px solid var(--color-theme-public-outline-variant); border-radius:.125rem; color:var(--color-theme-public-text-secondary); font-weight:700; }
.sidebar-heading { border-bottom:1px solid rgba(255,255,255,.08); padding-bottom:1rem; color:var(--color-theme-public-text-secondary); font-family:var(--font-mono); font-size:.75rem; font-weight:700; letter-spacing:.16em; text-transform:uppercase; }
.category-link { display:flex; justify-content:space-between; border-left:2px solid transparent; padding:.7rem 1.25rem; color:var(--color-theme-public-text-secondary); }
.category-link.active { border-color:var(--color-theme-public-primary); background:rgba(230,33,23,.08); color:var(--color-theme-public-primary); }
@media (max-width:639px) { .news-hero-overlay { background:linear-gradient(0deg, var(--color-theme-public-bg), rgba(8,15,24,.62) 68%, rgba(8,15,24,.48)); } }
</style>
