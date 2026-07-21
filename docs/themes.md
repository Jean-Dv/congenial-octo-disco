# Moon CMS theme guide

Moon uses one global theme for the public website, authentication, user dashboard, and admin panel. `aeris` is bundled as the safe default.

## Select a theme

Set the deployment default in `.env`:

```dotenv
APP_THEME=aeris
```

The value is read through `config('themes.active')`. After changing it on a config-cached deployment, refresh Laravel's cache and reload the page:

```bash
php artisan config:clear
# or rebuild the production cache
php artisan config:cache
```

All bundled themes are included by the normal `pnpm run build`; changing between already deployed themes does not require a separate frontend build. An unknown or invalid selection is logged and safely falls back to Aeris.

## Create a bundled theme

Create `resources/themes/my-theme/` with this minimum structure:

```text
resources/themes/my-theme/
├── theme.json
├── index.js
├── theme.css
├── Components/
├── Layouts/
└── Pages/             # optional complete page overrides
```

The directory and manifest IDs must match and use lowercase letters, numbers, and hyphens:

```json
{
    "id": "my-theme",
    "name": "My Theme",
    "version": "1.0.0",
    "progress_color": "#3366ff"
}
```

`index.js` must import the theme CSS and manifest and default-export the following shape:

```js
export default {
    id: manifest.id,
    progressColor: manifest.progress_color,
    components: {
        ThemeAlert,
        ThemeBadge,
        ThemeButton,
        ThemeCard,
        ThemeCheckbox,
        ThemeField,
        ThemeIconButton,
        ThemeInput,
        ThemeLogo,
        ThemeNavLink,
        ThemeSelect,
        ThemeSwitch,
        ThemeTable,
    },
    layouts: {
        ThemeAppLayout,
        ThemeGuestLayout,
        ThemePublicLayout,
    },
};
```

These names are the stable interface used by module pages. Implementations may use completely different markup, but must retain the props, events, slots, disabled behavior, link behavior, form binding, focus visibility, labels, and ARIA semantics exposed by Aeris. Run the frontend contract tests and exercise every component state before deployment.

Shared pages use semantic `theme-*` Tailwind tokens. A theme should provide the complete token set demonstrated by Aeris, even when its custom components do not use every token. Scope extra global rules through `html[data-theme="my-theme"]` and keep assets inside the theme where practical.

## Override a complete page

A theme can replace any Inertia page without changing its route or controller. Mirror the Inertia page name below the theme's `Pages` directory:

```text
resources/themes/my-theme/Pages/Public/Home/Index.vue
resources/themes/my-theme/Pages/Core/Dashboard.vue
```

The runtime checks the selected theme first and then falls back to `Modules/{Module}/resources/js/Pages`. An override receives exactly the same Inertia props as the module page; controllers, requests, URLs, and response data remain the functional contract. Do not duplicate backend business logic in a theme.

Use the globally registered `ThemeAppLayout`, `ThemeGuestLayout`, or `ThemePublicLayout` as normal template components. A full page reload is required when the active global theme changes so its CSS and registry are installed before Vue mounts.

## Validation checklist

- Run `vendor/bin/pest` and `pnpm test`.
- Run `pnpm run build` to confirm Vite discovers the package and any page overrides.
- Check public, guest, user, and admin screens at mobile and desktop widths.
- Check every component variant, validation error, loading/disabled state, keyboard focus, and screen-reader label.
- Temporarily set `APP_THEME` to an invalid ID and confirm Aeris remains accessible.

## Future admin selector

The current `ThemeSelectionProviderInterface` reads Laravel configuration. A future settings implementation should store only a validated theme ID and compose providers in this order: database admin choice, `APP_THEME` default, Aeris fallback. After saving, return a full external/Inertia location redirect rather than a partial visit so the new theme boots cleanly. Theme installation or archive upload is intentionally outside this contract; deploy reviewed theme source with the application.
