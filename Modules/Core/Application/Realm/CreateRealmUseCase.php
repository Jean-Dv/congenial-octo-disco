<?php

declare(strict_types=1);

namespace Modules\Core\Application\Realm;

use Modules\Core\Domain\Realm\Ports\RealmRepositoryInterface;
use Modules\Core\Domain\Realm\Realm;
use Modules\Core\Domain\Realm\ValueObjects\CoreType;
use Modules\Core\Domain\Realm\ValueObjects\DatabaseConnectionConfig;
use Modules\Core\Domain\Realm\ValueObjects\RemoteConsoleConfig;

final class CreateRealmUseCase
{
    public function __construct(
        private readonly RealmRepositoryInterface $realms,
    ) {
    }

    public function handle(CreateRealmInput $input): Realm
    {
        $realm = Realm::create(
            name: $input->name,
            slug: $input->slug,
            coreType: CoreType::from($input->coreType),
            authDatabase: DatabaseConnectionConfig::fromArray($input->authDatabase),
            charactersDatabase: $input->charactersDatabase !== null
                ? DatabaseConnectionConfig::fromArray($input->charactersDatabase)
                : null,
            remoteConsole: RemoteConsoleConfig::fromArray($input->remoteConsole),
            gmRealmId: $input->gmRealmId,
            enabled: $input->enabled,
        );

        return $this->realms->save($realm);
    }
}
