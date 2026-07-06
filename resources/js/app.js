import '../css/app.css';
import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';

const appName = import.meta.env.VITE_APP_NAME || 'Moon';

// Cada modulo publica sus propias paginas Inertia bajo su propia carpeta:
//   Modules/{NombreModulo}/resources/js/Pages/**/*.vue
// Un controlador del modulo Core hace Inertia::render('Core/Dashboard') y
// eso se resuelve aqui a Modules/Core/resources/js/Pages/Dashboard.vue,
// sin que este archivo necesite conocer que modulos existen.
const modulePages = import.meta.glob('/Modules/*/resources/js/Pages/**/*.vue');

createInertiaApp({
    title: (title) => (title ? `${title} · ${appName}` : appName),
    resolve: (name) => {
        const [moduleDir, ...rest] = name.split('/');
        const path = `/Modules/${moduleDir}/resources/js/Pages/${rest.join('/')}.vue`;
        const loader = modulePages[path];

        if (!loader) {
            throw new Error(
                `[Moon] No se encontro la pagina Inertia "${name}" (se esperaba en ${path}). ` +
                `Revisa que el modulo "${moduleDir}" exista y registre esa vista.`
            );
        }

        return loader().then((module) => module.default ?? module);
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
    progress: {
        color: '#c9873f',
    },
});
