<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Tests\TestCase;

final class LoginRateLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_is_limited_after_five_failed_attempts(): void
    {
        $this->createUser();

        foreach (range(1, 5) as $_attempt) {
            $this->post('/login', $this->credentials('wrong-password'))
                ->assertSessionHasErrors(['email' => 'El correo o la contraseña no son correctos.']);
        }

        $this->post('/login', $this->credentials('MoonTest!2026'))
            ->assertSessionHasErrors('email')
            ->assertRedirect();
        $this->assertStringContainsString(
            'Demasiados intentos de acceso.',
            session('errors')->first('email'),
        );

        $this->assertGuest();
    }

    public function test_successful_login_clears_previous_failures(): void
    {
        $this->createUser();

        foreach (range(1, 4) as $_attempt) {
            $this->post('/login', $this->credentials('wrong-password'));
        }

        $this->post('/login', $this->credentials('MoonTest!2026'))
            ->assertRedirectToRoute('dashboard');
        $this->post('/logout');

        $this->post('/login', $this->credentials('wrong-password'))
            ->assertSessionHasErrors(['email' => 'El correo o la contraseña no son correctos.']);
    }

    public function test_the_limit_is_scoped_by_normalized_email_and_ip(): void
    {
        foreach (range(1, 5) as $_attempt) {
            $this->post('/login', [
                'email' => 'first@moonshard.local',
                'password' => 'wrong-password',
            ]);
        }

        $this->post('/login', [
            'email' => 'second@moonshard.local',
            'password' => 'wrong-password',
        ])->assertSessionHasErrors(['email' => 'El correo o la contraseña no son correctos.']);

        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.10'])
            ->post('/login', [
                'email' => 'first@moonshard.local',
                'password' => 'wrong-password',
            ])->assertSessionHasErrors(['email' => 'El correo o la contraseña no son correctos.']);
    }

    private function createUser(): void
    {
        UserModel::create([
            'name' => 'MoonPlayer',
            'email' => 'player@moonshard.local',
            'password' => Hash::make('MoonTest!2026'),
            'locale' => 'es',
            'is_admin' => false,
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function credentials(string $password): array
    {
        return [
            'email' => 'PLAYER@moonshard.local',
            'password' => $password,
        ];
    }
}
