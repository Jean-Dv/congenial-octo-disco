<?php

declare(strict_types=1);

namespace Modules\Core\Http\Controllers\Auth;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Core\Application\Auth\ResetPasswordUseCase;
use Modules\Core\Http\Controllers\Controller;

final class NewPasswordController extends Controller
{
    public function create(Request $request): Response
    {
        return Inertia::render('Core/Auth/ResetPassword', [
            'email' => $request->string('email')->toString(),
            'token' => $request->route('token'),
        ]);
    }

    public function store(Request $request, ResetPasswordUseCase $useCase): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:6', 'max:16', 'regex:/^[\x21-\x7E]+$/', 'confirmed'],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request, $useCase): void {
                // Aqui es donde se actualiza el password del panel Y se
                // empuja el cambio a cada reino habilitado (ver
                // ResetPasswordUseCase).
                $useCase->handle($user->id, $request->string('password')->toString());
            },
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('success', __('core::auth.reset_password.success'))
            : back()->withErrors(['email' => __($status)]);
    }
}
