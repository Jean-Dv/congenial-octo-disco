<?php

declare(strict_types=1);

namespace Modules\Core\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Core\Application\Realm\CreateRealmInput;
use Modules\Core\Application\Realm\CreateRealmUseCase;
use Modules\Core\Application\Realm\DeleteRealmUseCase;
use Modules\Core\Application\Realm\ListRealmsUseCase;
use Modules\Core\Application\Realm\UpdateRealmInput;
use Modules\Core\Application\Realm\UpdateRealmUseCase;
use Modules\Core\Domain\Realm\Exceptions\RealmConnectivityException;
use Modules\Core\Domain\Realm\Ports\RealmRepositoryInterface;
use Modules\Core\Domain\Realm\Realm;
use Modules\Core\Domain\Realm\ValueObjects\CoreType;
use Modules\Core\Http\Controllers\Controller;
use Modules\Core\Http\Requests\Admin\RealmRequest;

final class RealmController extends Controller
{
    public function index(ListRealmsUseCase $useCase): Response
    {
        $realms = $useCase->handle();

        return Inertia::render('Core/Admin/Realms/Index', [
            'realms' => array_map(fn (Realm $realm) => $this->present($realm), $realms),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Core/Admin/Realms/Create', [
            'coreTypes' => $this->coreTypeOptions(),
        ]);
    }

    public function store(RealmRequest $request, CreateRealmUseCase $useCase): RedirectResponse
    {
        try {
            $useCase->handle($this->toInput($request));
        } catch (RealmConnectivityException $exception) {
            throw ValidationException::withMessages([
                $exception->field => $exception->getMessage(),
            ]);
        }

        return redirect()->route('admin.realms.index')->with('success', __('core::admin.realms.created'));
    }

    public function edit(int $realm, RealmRepositoryInterface $realms): Response
    {
        $entity = $realms->findById($realm);

        abort_if($entity === null, 404);

        return Inertia::render('Core/Admin/Realms/Edit', [
            'realm' => $this->present($entity, forEdit: true),
            'coreTypes' => $this->coreTypeOptions(),
        ]);
    }

    public function update(RealmRequest $request, int $realm, UpdateRealmUseCase $useCase): RedirectResponse
    {
        try {
            $useCase->handle($realm, $this->toUpdateInput($request));
        } catch (RealmConnectivityException $exception) {
            throw ValidationException::withMessages([
                $exception->field => $exception->getMessage(),
            ]);
        }

        return redirect()->route('admin.realms.index')->with('success', __('core::admin.realms.updated'));
    }

    public function destroy(int $realm, DeleteRealmUseCase $useCase): RedirectResponse
    {
        $useCase->handle($realm);

        return redirect()->route('admin.realms.index')->with('success', __('core::admin.realms.deleted'));
    }

    private function toInput(RealmRequest $request): CreateRealmInput
    {
        $data = $request->validated();

        return new CreateRealmInput(
            name: $data['name'],
            slug: $data['slug'],
            coreType: $data['core_type'],
            authDatabase: $data['auth_database'],
            charactersDatabase: $data['characters_database'] ?? null,
            remoteConsole: $data['remote_console'],
            sshTunnel: $data['connection_type'] === 'ssh'
                ? $data['ssh_tunnel']
                : null,
            gmRealmId: (int) ($data['gm_realm_id'] ?? -1),
            enabled: (bool) ($data['enabled'] ?? true),
        );
    }

    private function toUpdateInput(RealmRequest $request): UpdateRealmInput
    {
        $data = $request->validated();

        return new UpdateRealmInput(
            name: $data['name'],
            slug: $data['slug'],
            coreType: $data['core_type'],
            authDatabase: $data['auth_database'],
            charactersDatabase: $data['characters_database'] ?? null,
            remoteConsole: $data['remote_console'],
            sshTunnel: $data['connection_type'] === 'ssh'
                ? $data['ssh_tunnel']
                : null,
            gmRealmId: (int) ($data['gm_realm_id'] ?? -1),
            enabled: (bool) ($data['enabled'] ?? true),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function present(Realm $realm, bool $forEdit = false): array
    {
        $authDatabase = $forEdit
            ? [
                'host' => $realm->authDatabase()->host,
                'port' => $realm->authDatabase()->port,
                'database' => $realm->authDatabase()->database,
                'username' => $realm->authDatabase()->username,
                'password' => '',
            ]
            : ['host' => $realm->authDatabase()->host, 'database' => $realm->authDatabase()->database];
        $charactersDatabase = $realm->charactersDatabase();
        $remoteConsole = $forEdit
            ? [
                'host' => $realm->remoteConsole()->host,
                'port' => $realm->remoteConsole()->port,
                'username' => $realm->remoteConsole()->username,
                'password' => '',
            ]
            : ['host' => $realm->remoteConsole()->host];

        return [
            'id' => $realm->id(),
            'name' => $realm->name(),
            'slug' => $realm->slug(),
            'core_type' => $realm->coreType()->value,
            'core_type_label' => $realm->coreType()->label(),
            'has_full_support' => $realm->coreType()->hasFullSupport(),
            'enabled' => $realm->isEnabled(),
            'gm_realm_id' => $realm->gmRealmId(),
            'auth_database' => $authDatabase,
            'characters_database' => $forEdit && $charactersDatabase !== null
                ? [
                    'host' => $charactersDatabase->host,
                    'port' => $charactersDatabase->port,
                    'database' => $charactersDatabase->database,
                    'username' => $charactersDatabase->username,
                    'password' => '',
                ]
                : null,
            'remote_console' => $remoteConsole,
            'connection_type' => $realm->usesSshTunnel() ? 'ssh' : 'direct',
            'ssh_tunnel' => $forEdit && $realm->sshTunnel() !== null
                ? [
                    'host' => $realm->sshTunnel()->host,
                    'port' => $realm->sshTunnel()->port,
                    'username' => $realm->sshTunnel()->username,
                    'private_key' => '',
                    'private_key_passphrase' => '',
                ]
                : null,
        ];
    }

    /**
     * @return array<int, array{value: string, label: string, has_full_support: bool}>
     */
    private function coreTypeOptions(): array
    {
        return array_map(
            fn (CoreType $coreType) => [
                'value' => $coreType->value,
                'label' => $coreType->label(),
                'has_full_support' => $coreType->hasFullSupport(),
            ],
            CoreType::cases(),
        );
    }
}
