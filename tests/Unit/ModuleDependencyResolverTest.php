<?php

declare(strict_types=1);

namespace Tests\Unit;

use DomainException;
use Moon\ModuleKit\ModuleDependencyResolver;
use Moon\ModuleKit\ModuleManifest;
use PHPUnit\Framework\TestCase;

final class ModuleDependencyResolverTest extends TestCase
{
    public function test_it_orders_dependencies_before_dependants(): void
    {
        $resolver = new ModuleDependencyResolver;
        $manifests = [
            'news' => $this->manifest('news', ['public']),
            'core' => $this->manifest('core'),
            'public' => $this->manifest('public', ['core']),
        ];

        $this->assertSame(['core', 'public', 'news'], array_keys($resolver->ordered($manifests)));
    }

    public function test_it_rejects_missing_dependencies(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('requiere el modulo ausente "core"');

        (new ModuleDependencyResolver)->ordered([
            'news' => $this->manifest('news', ['core']),
        ]);
    }

    public function test_it_rejects_dependency_cycles(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('dependencia circular');

        (new ModuleDependencyResolver)->ordered([
            'public' => $this->manifest('public', ['news']),
            'news' => $this->manifest('news', ['public']),
        ]);
    }

    public function test_it_only_exposes_modules_with_an_enabled_dependency_chain(): void
    {
        $resolver = new ModuleDependencyResolver;
        $manifests = [
            'core' => $this->manifest('core'),
            'public' => $this->manifest('public', ['core']),
            'news' => $this->manifest('news', ['public']),
        ];

        $enabled = $resolver->enabled($manifests, [
            'core' => true,
            'public' => false,
            'news' => true,
        ]);

        $this->assertSame(['core'], array_keys($enabled));
    }

    /**
     * @param  array<int, string>  $dependencies
     */
    private function manifest(string $slug, array $dependencies = []): ModuleManifest
    {
        return new ModuleManifest(
            slug: $slug,
            name: ucfirst($slug),
            description: '',
            version: '1.0.0',
            provider: "Modules\\{$slug}\\Provider",
            isCore: $slug === 'core',
            dependencies: $dependencies,
        );
    }
}
