<?php

declare(strict_types=1);

namespace Modules\Core\Http\Controllers\Auth;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Core\Http\Controllers\Controller;

final class EmailVerificationPromptController extends Controller
{
    public function __invoke(Request $request): Response|RedirectResponse
    {
        return $request->user()->hasVerifiedEmail()
            ? redirect()->route('dashboard')
            : Inertia::render('Core/Auth/VerifyEmail', [
                'status' => $request->session()->get('status'),
            ]);
    }
}
