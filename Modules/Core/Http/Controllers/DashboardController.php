<?php

declare(strict_types=1);

namespace Modules\Core\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Core\Domain\GameAccount\Ports\GameAccountProvisioningRepositoryInterface;
use Modules\Core\Domain\Realm\Ports\RealmRepositoryInterface;

/**
 * Placeholder minimo, tal como se acordo: solo muestra el estado del
 * aprovisionamiento de la cuenta de juego del usuario en cada reino.
 * Los modulos futuros pueden añadir sus propios widgets aqui.
 */
final class DashboardController extends Controller
{
    public function __construct(
        private readonly GameAccountProvisioningRepositoryInterface $provisionings,
        private readonly RealmRepositoryInterface $realms,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $userId = $request->user()->id;

        $realmsById = collect($this->realms->all())->keyBy(fn ($realm) => $realm->id());

        $realmStatuses = collect($this->provisionings->findByUser($userId))
            ->map(function ($provisioning) use ($realmsById) {
                $realm = $realmsById->get($provisioning->realmId());

                return [
                    'realm_name' => $realm?->name() ?? "Reino #{$provisioning->realmId()}",
                    'status' => $provisioning->status()->value,
                    'status_label' => $provisioning->status()->label(),
                    'last_error' => $provisioning->lastError(),
                ];
            })
            ->values()
            ->all();

        return Inertia::render('Core/Dashboard', [
            'realmStatuses' => $realmStatuses,
        ]);
    }
}
