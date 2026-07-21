import test from 'node:test';
import assert from 'node:assert/strict';

import {
    REQUIRED_COMPONENTS,
    REQUIRED_LAYOUTS,
    loadTheme,
    resolvePageLoader,
    validateThemeModule,
} from './runtime.js';

function validTheme(id = 'aeris') {
    return {
        id,
        progressColor: '#e62117',
        components: Object.fromEntries(REQUIRED_COMPONENTS.map((name) => [name, {}])),
        layouts: Object.fromEntries(REQUIRED_LAYOUTS.map((name) => [name, {}])),
    };
}

test('validates the complete theme contract', () => {
    assert.equal(validateThemeModule(validTheme(), 'aeris').id, 'aeris');
    assert.throws(
        () => validateThemeModule({ ...validTheme(), components: {} }, 'aeris'),
        /does not provide ThemeAlert/,
    );
});

test('loads the requested theme bundle', async () => {
    const theme = await loadTheme({
        '/resources/themes/aeris/index.js': async () => ({ default: validTheme() }),
    }, 'aeris');

    assert.equal(theme.id, 'aeris');
});

test('falls back to Aeris when a frontend bundle is missing', async () => {
    globalThis.document = { documentElement: { dataset: {} } };
    const originalError = console.error;
    console.error = () => {};

    try {
        const theme = await loadTheme({
            '/resources/themes/aeris/index.js': async () => ({ default: validTheme() }),
        }, 'missing');

        assert.equal(theme.id, 'aeris');
        assert.equal(document.documentElement.dataset.theme, 'aeris');
    } finally {
        console.error = originalError;
        delete globalThis.document;
    }
});

test('prefers a selected theme page and falls back to its module page', () => {
    const themeLoader = () => 'theme';
    const moduleLoader = () => 'module';
    const modulePages = {
        '/Modules/Core/resources/js/Pages/Dashboard.vue': moduleLoader,
    };

    assert.equal(resolvePageLoader('Core/Dashboard', 'aeris', {
        '/resources/themes/aeris/Pages/Core/Dashboard.vue': themeLoader,
    }, modulePages), themeLoader);

    assert.equal(resolvePageLoader('Core/Dashboard', 'aeris', {}, modulePages), moduleLoader);
    assert.equal(resolvePageLoader('Missing/Page', 'aeris', {}, modulePages), null);
});
