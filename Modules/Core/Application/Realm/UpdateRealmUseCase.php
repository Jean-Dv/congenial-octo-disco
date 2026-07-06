<?php

declare(strict_types=1);

namespace Modules\Core\Application\Realm;

use Modules\Core\Domain\Realm\Ports\RealmRepositoryInterface;
use Modules\Core\Domain\Realm\Realm;
use Modules\Core\Domain\Realm\ValueObjects\DatabaseConnectionConfig;
use Modules\Core\Domain\Realm\ValueObjects\RemoteConsoleConfig;
use RuntimeException;

final class UpdateRealmUseCase
{
    public function __construct(
        private readonly RealmRepositoryInterface $realms,
    ) {
    }

    public function handle(int $realmId, CreateRealmInput $input): Realm
    {
        $realm = $this->realms->findById($realmId);

        if ($realm === null) {
            throw new RuntimeException("No existe el reino #{$realmId}.");
        }

        $realm->rename($input->name);
        $realm->updateAuthDatabase(DatabaseConnectionConfig::fromArray($input->authDatabase));
        $realm->updateCharactersDatabase(
            $input->charactersDatabase !== null ? DatabaseConnectionConfig::fromArray($input->charactersDatabase) : null
        );
        $realm->updateRemoteConsole(RemoteConsoleConfig::fromArray($input->remoteConsole));

        $input->enabled ? $realm->enable() : $realm->disable();

        return $this->realms->save($realm);
    }
}
