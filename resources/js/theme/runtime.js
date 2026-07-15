export const REQUIRED_COMPONENTS = [
    'ThemeAlert',
    'ThemeBadge',
    'ThemeButton',
    'ThemeCard',
    'ThemeCheckbox',
    'ThemeField',
    'ThemeIconButton',
    'ThemeInput',
    'ThemeLogo',
    'ThemeNavLink',
    'ThemeSelect',
    'ThemeSwitch',
    'ThemeTable',
];

export const REQUIRED_LAYOUTS = [
    'ThemeAppLayout',
    'ThemeGuestLayout',
    'ThemePublicLayout',
];

export function themeIdFromModulePath(path) {
    return path.match(/\/themes\/([^/]+)\/index\.js$/)?.[1] ?? null;
}

export function validateThemeModule(theme, expectedId) {
    if (!theme || theme.id !== expectedId) {
        throw new Error(`[Moon] Theme module must export id "${expectedId}".`);
    }

    for (const name of REQUIRED_COMPONENTS) {
        if (!theme.components?.[name]) {
            throw new Error(`[Moon] Theme "${expectedId}" does not provide ${name}.`);
        }
    }

    for (const name of REQUIRED_LAYOUTS) {
        if (!theme.layouts?.[name]) {
            throw new Error(`[Moon] Theme "${expectedId}" does not provide ${name}.`);
        }
    }

    if (!/^#[0-9a-f]{6}$/i.test(theme.progressColor ?? '')) {
        throw new Error(`[Moon] Theme "${expectedId}" has an invalid progressColor.`);
    }

    return theme;
}

export async function loadTheme(themeLoaders, requestedId, fallbackId = 'aeris') {
    const loadersById = Object.fromEntries(
        Object.entries(themeLoaders)
            .map(([path, loader]) => [themeIdFromModulePath(path), loader])
            .filter(([id]) => id),
    );

    const load = async (id) => {
        const loader = loadersById[id];

        if (!loader) {
            throw new Error(`[Moon] Theme bundle "${id}" was not included in the frontend build.`);
        }

        const module = await loader();
        return validateThemeModule(module.default ?? module, id);
    };

    try {
        return await load(requestedId);
    } catch (error) {
        if (requestedId === fallbackId) {
            throw error;
        }

        console.error(`[Moon] Could not load theme "${requestedId}"; using "${fallbackId}".`, error);
        document.documentElement.dataset.theme = fallbackId;

        return load(fallbackId);
    }
}

export function resolvePageLoader(name, themeId, themePages, modulePages) {
    const themePath = `/resources/themes/${themeId}/Pages/${name}.vue`;
    const themeLoader = themePages[themePath];

    if (themeLoader) {
        return themeLoader;
    }

    const [moduleDir, ...rest] = name.split('/');
    const modulePath = `/Modules/${moduleDir}/resources/js/Pages/${rest.join('/')}.vue`;

    return modulePages[modulePath] ?? null;
}
