<?php

declare(strict_types=1);

namespace Modules\Core\Providers;

use Moon\ModuleKit\AbstractModule;
use Moon\ModuleKit\Contracts\ModuleRepositoryInterface;
use Moon\ModuleKit\ModuleManifest;
use Moon\RemoteConsole\Contracts\RemoteConsoleGatewayInterface;
use Modules\Core\Application\Auth\Ports\EmailVerificationNotifierInterface;
use Modules\Core\Application\Auth\Ports\PasswordResetNotifierInterface;
use Modules\Core\Application\GameAccount\Ports\GameAccountJobDispatcherInterface;
use Modules\Core\Application\Module\ToggleModuleUseCase;
use Modules\Core\Console\Commands\MakeAdminCommand;
use Modules\Core\Domain\Auth\Ports\PasswordHasherInterface;
use Modules\Core\Domain\Auth\Ports\UserRepositoryInterface;
use Modules\Core\Domain\GameAccount\Ports\GameAccountGatewayResolverInterface;
use Modules\Core\Domain\GameAccount\Ports\GameAccountProvisioningRepositoryInterface;
use Modules\Core\Domain\GameAccount\Ports\PasswordHashStrategyResolverInterface;
use Modules\Core\Domain\Realm\Ports\RealmRepositoryInterface;
use Modules\Core\Infrastructure\GameCore\GameAccountGateway\GameAccountGatewayResolver;
use Modules\Core\Infrastructure\GameCore\PasswordHashStrategy\PasswordHashStrategyResolver;
use Modules\Core\Infrastructure\Notifications\LaravelEmailVerificationNotifier;
use Modules\Core\Infrastructure\Notifications\LaravelPasswordResetNotifier;
use Modules\Core\Infrastructure\Persistence\Connection\RealmConnectionFactory;
use Modules\Core\Infrastructure\Persistence\Eloquent\Repositories\EloquentGameAccountProvisioningRepository;
use Modules\Core\Infrastructure\Persistence\Eloquent\Repositories\EloquentModuleRepository;
use Modules\Core\Infrastructure\Persistence\Eloquent\Repositories\EloquentRealmRepository;
use Modules\Core\Infrastructure\Persistence\Eloquent\Repositories\EloquentUserRepository;
use Modules\Core\Infrastructure\Queue\LaravelGameAccountJobDispatcher;
use Modules\Core\Infrastructure\RemoteConsole\Soap\SoapRemoteConsoleGateway;
use Modules\Core\Infrastructure\Security\LaravelPasswordHasher;

final class CoreServiceProvider extends AbstractModule
{
    public function manifest(): ModuleManifest
    {
        $raw = json_decode(
            file_get_contents($this->moduleBasePath().'/module.json') ?: '{}',
            true,
        );

        return ModuleManifest::fromArray($raw);
    }

    public function register(): void
    {
        // --- Auth ------------------------------------------------------
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
        $this->app->bind(PasswordHasherInterface::class, LaravelPasswordHasher::class);
        $this->app->bind(EmailVerificationNotifierInterface::class, LaravelEmailVerificationNotifier::class);
        $this->app->bind(PasswordResetNotifierInterface::class, LaravelPasswordResetNotifier::class);

        // --- Realm -------------------------------------------------------
        $this->app->bind(RealmRepositoryInterface::class, EloquentRealmRepository::class);
        $this->app->singleton(RealmConnectionFactory::class);

        // --- GameAccount ---------------------------------------------------
        $this->app->bind(GameAccountProvisioningRepositoryInterface::class, EloquentGameAccountProvisioningRepository::class);
        $this->app->bind(PasswordHashStrategyResolverInterface::class, PasswordHashStrategyResolver::class);
        $this->app->bind(GameAccountGatewayResolverInterface::class, GameAccountGatewayResolver::class);
        $this->app->bind(GameAccountJobDispatcherInterface::class, LaravelGameAccountJobDispatcher::class);

        // --- Comunicacion remota -------------------------------------------
        // UNICO binding a cambiar el dia que un core hable gRPC/REST en
        // vez de SOAP: swap SoapRemoteConsoleGateway por la clase nueva,
        // ambas implementando el mismo RemoteConsoleGatewayInterface.
        $this->app->bind(RemoteConsoleGatewayInterface::class, SoapRemoteConsoleGateway::class);

        // --- Sistema de modulos (shared kernel -> adaptador de Core) -----
        $this->app->singleton(ModuleRepositoryInterface::class, EloquentModuleRepository::class);

        $this->app->when(ToggleModuleUseCase::class)
            ->needs('$protectedSlugs')
            ->give(fn () => config('modules.protected', []));
    }

    public function boot(): void
    {
        parent::boot();

        $this->commands([
            MakeAdminCommand::class,
        ]);
    }
}
