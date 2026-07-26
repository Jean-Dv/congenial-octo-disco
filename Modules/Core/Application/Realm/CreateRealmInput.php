<?php

declare(strict_types=1);

namespace Modules\Core\Application\Realm;

final class CreateRealmInput
{
    /**
     * @param  array{host:string,port:int,database:string,username:string,password:string}  $authDatabase
     * @param  array{host:string,port:int,database:string,username:string,password:string}|null  $charactersDatabase
     * @param  array{host:string,port:int,username:string,password:string}  $remoteConsole
     * @param  array{host:string,port:int,username:string,private_key:string,private_key_passphrase:?string}|null  $sshTunnel
     */
    public function __construct(
        public readonly string $name,
        public readonly string $slug,
        public readonly string $coreType,
        public readonly array $authDatabase,
        public readonly ?array $charactersDatabase,
        public readonly array $remoteConsole,
        public readonly ?array $sshTunnel,
        public readonly int $gmRealmId = -1,
        public readonly bool $enabled = true,
    ) {}
}
