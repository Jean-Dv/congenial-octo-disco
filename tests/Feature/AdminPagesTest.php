<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Tests\TestCase;

final class AdminPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_realm_and_module_pages(): void
    {
        $admin = $this->createUser(isAdmin: true);

        $this->actingAs($admin)->get('/admin/realms')->assertOk();
        $this->actingAs($admin)->get('/admin/modules')->assertOk();
    }

    public function test_regular_user_cannot_open_admin_pages(): void
    {
        $user = $this->createUser(isAdmin: false);

        $this->actingAs($user)->get('/admin/realms')->assertForbidden();
        $this->actingAs($user)->get('/admin/modules')->assertForbidden();
    }

    private function createUser(bool $isAdmin): UserModel
    {
        return UserModel::create([
            'name' => $isAdmin ? 'MoonAdmin' : 'MoonPlayer',
            'email' => $isAdmin ? 'admin@moonshard.local' : 'player@moonshard.local',
            'password' => Hash::make('MoonTest!2026'),
            'locale' => 'es',
            'is_admin' => $isAdmin,
        ]);
    }
}
