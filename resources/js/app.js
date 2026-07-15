import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { ZiggyVue } from 'ziggy-js';
import { loadTheme, resolvePageLoader } from './theme/runtime';

const appName = import.meta.env.VITE_APP_NAME || 'Moon';

const requestedThemeId = document.documentElement.dataset.theme || 'aeris';
const themeLoaders = import.meta.glob('/resources/themes/*/index.js');
const themePages = import.meta.glob('/resources/themes/*/Pages/**/*.vue');

// Cada modulo publica sus propias paginas Inertia bajo su propia carpeta:
//   Modules/{NombreModulo}/resources/js/Pages/**/*.vue
// Un controlador del modulo Core hace Inertia::render('Core/Dashboard') y
// eso se resuelve aqui a Modules/Core/resources/js/Pages/Dashboard.vue,
// sin que este archivo necesite conocer que modulos existen.
const modulePages = import.meta.glob('/Modules/*/resources/js/Pages/**/*.vue');

async function bootstrap() {
    const activeTheme = await loadTheme(themeLoaders, requestedThemeId);

    return createInertiaApp({
        title: (title) => (title ? `${title} · ${appName}` : appName),
        resolve: (name) => {
            const loader = resolvePageLoader(name, activeTheme.id, themePages, modulePages);

            if (!loader) {
                throw new Error(
                    `[Moon] No se encontro la pagina Inertia "${name}" en el tema ` +
                    `"${activeTheme.id}" ni en su modulo.`
                );
            }

            return loader().then((module) => module.default ?? module);
        },
        setup({ el, App, props, plugin }) {
            const vueApp = createApp({ render: () => h(App, props) })
                .use(plugin)
                .use(ZiggyVue);

            Object.entries({ ...activeTheme.components, ...activeTheme.layouts }).forEach(([name, component]) => {
                vueApp.component(name, component);
            });

            vueApp.mount(el);
        },
        progress: {
            color: activeTheme.progressColor,
        },
    });
}

bootstrap();
