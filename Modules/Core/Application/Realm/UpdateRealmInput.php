<?php

declare(strict_types=1);

namespace Modules\Core\Application\Realm;

final class UpdateRealmInput
{
    /**
     * Empty passwords mean "keep the currently stored secret".
     *
     * @param  array{host:string,port:int,database:string,username:string,password:?string}  $authDatabase
     * @param  array{host:string,port:int,database:string,username:string,password:?string}|null  $charactersDatabase
     * @param  array{host:string,port:int,username:string,password:?string}  $remoteConsole
     */
    public function __construct(
        public readonly string $name,
        public readonly string $slug,
        public readonly string $coreType,
        public readonly array $authDatabase,
        public readonly ?array $charactersDatabase,
        public readonly array $remoteConsole,
        public readonly int $gmRealmId = -1,
        public readonly bool $enabled = true,
    ) {}
}
