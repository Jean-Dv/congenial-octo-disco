<?php

declare(strict_types=1);

namespace Modules\Core\Http\Controllers\Auth;

use Illuminate\Cache\RateLimiter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Core\Application\Auth\AuthenticateUserInput;
use Modules\Core\Application\Auth\AuthenticateUserUseCase;
use Modules\Core\Domain\Auth\Exceptions\InvalidCredentialsException;
use Modules\Core\Http\Controllers\Controller;
use Modules\Core\Http\Requests\Auth\LoginRequest;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\UserModel;

final class AuthenticatedSessionController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Core/Auth/Login');
    }

    public function store(
        LoginRequest $request,
        AuthenticateUserUseCase $useCase,
        RateLimiter $limiter,
    ): RedirectResponse {
        $data = $request->validated();
        $throttleKey = $request->throttleKey();

        if ($limiter->tooManyAttempts($throttleKey, 5)) {
            throw ValidationException::withMessages([
                'email' => __('core::auth.login.throttle', [
                    'seconds' => $limiter->availableIn($throttleKey),
                ]),
            ]);
        }

        try {
            $user = $useCase->handle(new AuthenticateUserInput(
                email: $data['email'],
                password: $data['password'],
            ));
        } catch (InvalidCredentialsException $exception) {
            $limiter->hit($throttleKey, 60);

            return back()->withErrors(['email' => $exception->getMessage()])->onlyInput('email');
        }

        $limiter->clear($throttleKey);

        $model = UserModel::findOrFail($user->id());

        Auth::login($model, (bool) ($data['remember'] ?? false));

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
