<?php

declare(strict_types=1);

namespace Modules\Core\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Core\Application\Realm\CreateRealmInput;
use Modules\Core\Application\Realm\CreateRealmUseCase;
use Modules\Core\Application\Realm\DeleteRealmUseCase;
use Modules\Core\Application\Realm\ListRealmsUseCase;
use Modules\Core\Application\Realm\UpdateRealmUseCase;
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
        $useCase->handle($this->toInput($request));

        return redirect()->route('admin.realms.index')->with('success', __('core::admin.realms.created'));
    }

    public function edit(int $realm, RealmRepositoryInterface $realms): Response
    {
        $entity = $realms->findById($realm);

        abort_if($entity === null, 404);

        return Inertia::render('Core/Admin/Realms/Edit', [
            'realm' => $this->present($entity, includeSecrets: true),
            'coreTypes' => $this->coreTypeOptions(),
        ]);
    }

    public function update(RealmRequest $request, int $realm, UpdateRealmUseCase $useCase): RedirectResponse
    {
        $useCase->handle($realm, $this->toInput($request));

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
            gmRealmId: (int) ($data['gm_realm_id'] ?? -1),
            enabled: (bool) ($data['enabled'] ?? true),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function present(Realm $realm, bool $includeSecrets = false): array
    {
        return [
            'id' => $realm->id(),
            'name' => $realm->name(),
            'slug' => $realm->slug(),
            'core_type' => $realm->coreType()->value,
            'core_type_label' => $realm->coreType()->label(),
            'has_full_support' => $realm->coreType()->hasFullSupport(),
            'enabled' => $realm->isEnabled(),
            'gm_realm_id' => $realm->gmRealmId(),
            'auth_database' => $includeSecrets
                ? $realm->authDatabase()->toArray()
                : ['host' => $realm->authDatabase()->host, 'database' => $realm->authDatabase()->database],
            'characters_database' => $includeSecrets ? $realm->charactersDatabase()?->toArray() : null,
            'remote_console' => $includeSecrets
                ? $realm->remoteConsole()->toArray()
                : ['host' => $realm->remoteConsole()->host],
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
