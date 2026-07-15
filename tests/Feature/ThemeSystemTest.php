<?php

declare(strict_types=1);

use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Http\Request;
use Moon\ThemeKit\ActiveThemeResolver;
use Moon\ThemeKit\ConfigThemeSelectionProvider;
use Moon\ThemeKit\ThemeDefinition;
use Moon\ThemeKit\ThemeRegistry;
use Tests\TestCase;

uses(TestCase::class);

it('discovers and resolves the bundled Aeris theme', function () {
    $theme = app(ActiveThemeResolver::class)->resolve();

    expect($theme)
        ->toBeInstanceOf(ThemeDefinition::class)
        ->and($theme->id)->toBe('aeris')
        ->and($theme->progressColor)->toBe('#e62117');
});

it('falls back to Aeris when the configured theme is unavailable', function () {
    config()->set('themes.active', 'missing-theme');

    $resolver = new ActiveThemeResolver(
        app(ThemeRegistry::class),
        new ConfigThemeSelectionProvider(config()),
        config(),
        app('log'),
    );

    expect($resolver->resolve()->id)->toBe('aeris');
});

it('ignores malformed theme manifests', function () {
    $path = sys_get_temp_dir().'/moon-theme-test-'.bin2hex(random_bytes(4));
    mkdir($path.'/broken', 0777, true);
    file_put_contents($path.'/broken/theme.json', '{not-json');

    try {
        $registry = new ThemeRegistry(app('files'), app('log'), $path);

        expect($registry->all())->toBe([]);
    } finally {
        app('files')->deleteDirectory($path);
    }
});

it('shares the resolved theme with every Inertia response', function () {
    $shared = app(HandleInertiaRequests::class)->share(Request::create('/'));

    expect($shared['theme'])->toMatchArray([
        'id' => 'aeris',
        'name' => 'Aeris',
        'version' => '1.0.0',
        'progress_color' => '#e62117',
    ]);
});

it('renders the active theme on the document root', function () {
    $this->get('/login')
        ->assertOk()
        ->assertSee('data-theme="aeris"', escape: false);
});
