<?php

declare(strict_types=1);

namespace Modules\Core\Http\Controllers\Auth;

use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Core\Application\Auth\RegisterUserInput;
use Modules\Core\Application\Auth\RegisterUserUseCase;
use Modules\Core\Domain\Auth\Exceptions\UserAlreadyExistsException;
use Modules\Core\Http\Controllers\Controller;
use Modules\Core\Http\Requests\Auth\RegisterUserRequest;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\UserModel;

final class RegisteredUserController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Core/Auth/Register');
    }

    public function store(RegisterUserRequest $request, RegisterUserUseCase $useCase): RedirectResponse
    {
        $data = $request->validated();

        try {
            $user = $useCase->handle(new RegisterUserInput(
                username: $data['username'],
                email: $data['email'],
                password: $data['password'],
                locale: $data['locale'] ?? app()->getLocale(),
            ));
        } catch (UserAlreadyExistsException $exception) {
            return back()->withErrors(['email' => $exception->getMessage()])->withInput();
        }

        $model = UserModel::findOrFail($user->id());

        // Se dispara este evento nativo de Laravel para que OTROS modulos
        // puedan reaccionar al registro (estadisticas, webhooks, etc.) sin
        // tocar este controlador. El correo de verificacion NO depende de
        // este evento: ya se envio explicitamente dentro de
        // RegisterUserUseCase (para evitar un doble envio).
        event(new Registered($model));

        Auth::login($model);

        return redirect()->route('dashboard')
            ->with('success', __('core::auth.register.success'));
    }
}
