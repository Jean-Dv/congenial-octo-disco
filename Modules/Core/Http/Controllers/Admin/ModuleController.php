<?php

declare(strict_types=1);

namespace Modules\Core\Http\Controllers\Admin;

use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Core\Application\Module\ListModulesUseCase;
use Modules\Core\Application\Module\ToggleModuleUseCase;
use Modules\Core\Http\Controllers\Controller;

final class ModuleController extends Controller
{
    public function index(ListModulesUseCase $useCase): Response
    {
        return Inertia::render('Core/Admin/Modules/Index', [
            'modules' => $useCase->handle(),
        ]);
    }

    public function update(Request $request, string $module, ToggleModuleUseCase $useCase): RedirectResponse
    {
        $request->validate(['enabled' => ['required', 'boolean']]);

        try {
            $useCase->handle($module, $request->boolean('enabled'));
        } catch (DomainException $exception) {
            return back()->withErrors(['module' => $exception->getMessage()]);
        }

        return back()->with('success', __('core::admin.modules.updated'));
    }
}
