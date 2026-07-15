import '../css/app.css';
import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { ZiggyVue } from 'ziggy-js';
import AerisAlert from './Components/AerisAlert.vue';
import AerisBadge from './Components/AerisBadge.vue';
import AerisButton from './Components/AerisButton.vue';
import AerisCard from './Components/AerisCard.vue';
import AerisCheckbox from './Components/AerisCheckbox.vue';
import AerisField from './Components/AerisField.vue';
import AerisIconButton from './Components/AerisIconButton.vue';
import AerisInput from './Components/AerisInput.vue';
import AerisLogo from './Components/AerisLogo.vue';
import AerisNavLink from './Components/AerisNavLink.vue';
import AerisSelect from './Components/AerisSelect.vue';
import AerisSwitch from './Components/AerisSwitch.vue';
import AerisTable from './Components/AerisTable.vue';

const appName = import.meta.env.VITE_APP_NAME || 'Moon';

const aerisComponents = {
    AerisAlert,
    AerisBadge,
    AerisButton,
    AerisCard,
    AerisCheckbox,
    AerisField,
    AerisIconButton,
    AerisInput,
    AerisLogo,
    AerisNavLink,
    AerisSelect,
    AerisSwitch,
    AerisTable,
};

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
        const vueApp = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue);

        Object.entries(aerisComponents).forEach(([name, component]) => {
            vueApp.component(name, component);
        });

        vueApp.mount(el);
    },
    progress: {
        color: '#e62117',
    },
});
