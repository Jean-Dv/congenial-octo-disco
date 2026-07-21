<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Modules\Core\Infrastructure\Notifications\ResetPasswordNotification;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Tests\TestCase;

final class PasswordResetFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_notification_targets_the_registered_reset_route(): void
    {
        $user = $this->createUser();
        $notification = new ResetPasswordNotification('test-token');

        $message = $notification->toMail($user);

        $this->assertSame(
            route('password.reset', ['token' => 'test-token', 'email' => $user->email]),
            $message->actionUrl,
        );
    }

    public function test_known_user_can_request_a_reset_notification(): void
    {
        Notification::fake();
        $user = $this->createUser();

        $this->post('/forgot-password', ['email' => $user->email])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status');

        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    public function test_valid_token_updates_the_panel_password(): void
    {
        $user = $this->createUser();
        $token = Password::broker()->createToken($user);

        $this->post('/reset-password', [
            'email' => $user->email,
            'token' => $token,
            'password' => 'NewMoon!2026',
            'password_confirmation' => 'NewMoon!2026',
        ])->assertRedirectToRoute('login');

        $this->assertTrue(Hash::check('NewMoon!2026', $user->fresh()->password));
    }

    private function createUser(): UserModel
    {
        return UserModel::create([
            'name' => 'MoonTester',
            'email' => 'tester@moonshard.local',
            'password' => Hash::make('OldMoon!2026'),
            'locale' => 'es',
        ]);
    }
}
