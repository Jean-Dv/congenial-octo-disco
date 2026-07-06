<?php

use Illuminate\Support\Facades\Route;
use Modules\Core\Http\Controllers\Admin\ModuleController;
use Modules\Core\Http\Controllers\Admin\RealmController;
use Modules\Core\Http\Controllers\Auth\AuthenticatedSessionController;
use Modules\Core\Http\Controllers\Auth\EmailVerificationController;
use Modules\Core\Http\Controllers\Auth\EmailVerificationNotificationController;
use Modules\Core\Http\Controllers\Auth\EmailVerificationPromptController;
use Modules\Core\Http\Controllers\Auth\NewPasswordController;
use Modules\Core\Http\Controllers\Auth\PasswordResetLinkController;
use Modules\Core\Http\Controllers\Auth\RegisteredUserController;
use Modules\Core\Http\Controllers\DashboardController;

/*
|--------------------------------------------------------------------------
| Rutas del modulo Core
|--------------------------------------------------------------------------
| Cargadas automaticamente por Moon\ModuleKit\AbstractModule::boot() ya
| que este archivo vive en Modules/Core/routes/web.php (convencion
| estandar de cualquier modulo, no solo de Core).
*/

// --- Invitados -------------------------------------------------------------
Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.update');
});

// --- Autenticados ------------------------------------------------------------
Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('verify-email', EmailVerificationPromptController::class)->name('verification.notice');
    Route::get('verify-email/{id}/{hash}', EmailVerificationController::class)
        ->middleware('signed')
        ->name('verification.verify');
    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    // El dashboard NO exige "verified": el usuario entra al panel aunque
    // el aprovisionamiento en algun reino siga pendiente (confirmado con
    // el negocio). Solo lo protege "auth".
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    // --- Panel de administracion (reinos y modulos) -------------------
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('realms', [RealmController::class, 'index'])->name('realms.index');
        Route::get('realms/create', [RealmController::class, 'create'])->name('realms.create');
        Route::post('realms', [RealmController::class, 'store'])->name('realms.store');
        Route::get('realms/{realm}/edit', [RealmController::class, 'edit'])->name('realms.edit');
        Route::put('realms/{realm}', [RealmController::class, 'update'])->name('realms.update');
        Route::delete('realms/{realm}', [RealmController::class, 'destroy'])->name('realms.destroy');

        Route::get('modules', [ModuleController::class, 'index'])->name('modules.index');
        Route::patch('modules/{module}', [ModuleController::class, 'update'])->name('modules.update');
    });
});
