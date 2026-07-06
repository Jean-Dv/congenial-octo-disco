<?php

declare(strict_types=1);

namespace Modules\Core\Http\Controllers\Auth;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Core\Application\Auth\RequestPasswordResetUseCase;
use Modules\Core\Http\Controllers\Controller;

final class PasswordResetLinkController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Core/Auth/ForgotPassword', [
            'status' => session('status'),
        ]);
    }

    public function store(Request $request, RequestPasswordResetUseCase $useCase): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $status = $useCase->handle($request->string('email')->toString());

        return $status === Password::RESET_LINK_SENT || $status === 'moon.passwords.sent'
            ? back()->with('status', __('core::auth.forgot_password.sent'))
            : back()->withErrors(['email' => __($status)]);
    }
}
