<?php

declare(strict_types=1);

namespace Modules\Core\Http\Controllers\Auth;

use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Modules\Core\Application\Auth\VerifyEmailUseCase;
use Modules\Core\Http\Controllers\Controller;

final class EmailVerificationController extends Controller
{
    /**
     * EmailVerificationRequest ya valida la firma y el hash del correo
     * (mecanismo nativo de Laravel): aqui solo aplicamos el cambio de
     * estado del dominio.
     */
    public function __invoke(EmailVerificationRequest $request, VerifyEmailUseCase $useCase): RedirectResponse
    {
        $wasAlreadyVerified = $request->user()->hasVerifiedEmail();

        $useCase->handle($request->user()->id);

        if (! $wasAlreadyVerified) {
            event(new Verified($request->user()));
        }

        return redirect()->route('dashboard')->with('success', __('core::auth.verify_email.verified'));
    }
}
