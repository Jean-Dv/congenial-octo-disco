<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use Modules\Core\Domain\Realm\Exceptions\RealmConnectivityException;
use Modules\Core\Domain\Realm\Ports\RealmConnectivityVerifierInterface;
use Modules\Core\Domain\Realm\Realm;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\RealmModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Tests\TestCase;

final class RealmCredentialsTest extends TestCase
{
    use RefreshDatabase;

    public function test_edit_props_never_expose_secrets_and_blank_passwords_preserve_them(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->post('/admin/realms', $this->realmData())->assertRedirect();
        $realm = RealmModel::query()->firstOrFail();

        $this->actingAs($admin)->get("/admin/realms/{$realm->id}/edit")
            ->assertOk()
            ->assertDontSee('auth-secret')
            ->assertDontSee('characters-secret')
            ->assertDontSee('soap-secret')
            ->assertInertia(fn (Assert $page) => $page
                ->where('realm.auth_database.password', '')
                ->where('realm.characters_database.password', '')
                ->where('realm.remote_console.password', '')
            );

        $update = $this->realmData();
        $update['auth_database']['password'] = '';
        $update['characters_database']['password'] = '';
        $update['remote_console']['password'] = '';

        $this->actingAs($admin)->put("/admin/realms/{$realm->id}", $update)->assertRedirect();

        $realm->refresh();
        $this->assertSame('auth-secret', $realm->auth_database['password']);
        $this->assertSame('characters-secret', $realm->characters_database['password']);
        $this->assertSame('soap-secret', $realm->remote_console['password']);
    }

    public function test_an_explicit_replacement_updates_secrets(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->post('/admin/realms', $this->realmData());
        $realm = RealmModel::query()->firstOrFail();
        $update = $this->realmData();
        $update['auth_database']['password'] = 'new-auth-secret';
        $update['characters_database']['password'] = 'new-characters-secret';
        $update['remote_console']['password'] = 'new-soap-secret';

        $this->actingAs($admin)->put("/admin/realms/{$realm->id}", $update)->assertRedirect();

        $realm->refresh();
        $this->assertSame('new-auth-secret', $realm->auth_database['password']);
        $this->assertSame('new-characters-secret', $realm->characters_database['password']);
        $this->assertSame('new-soap-secret', $realm->remote_console['password']);
    }

    public function test_a_new_characters_connection_requires_a_password(): void
    {
        $admin = $this->admin();
        $create = $this->realmData();
        $create['characters_database'] = null;
        $this->actingAs($admin)->post('/admin/realms', $create);
        $realm = RealmModel::query()->firstOrFail();
        $update = $this->realmData();
        $update['auth_database']['password'] = '';
        $update['characters_database']['password'] = '';
        $update['remote_console']['password'] = '';

        $this->actingAs($admin)->put("/admin/realms/{$realm->id}", $update)
            ->assertSessionHasErrors('characters_database.password');
    }

    public function test_ssh_secrets_are_encrypted_write_only_and_blank_values_preserve_them(): void
    {
        $admin = $this->admin();
        $create = $this->realmData();
        $create['connection_type'] = 'ssh';
        $create['ssh_tunnel'] = [
            'host' => 'bastion.example.test',
            'port' => 22,
            'username' => 'moon-cms',
            'private_key' => '-----BEGIN OPENSSH PRIVATE KEY----- private-key-secret',
            'private_key_passphrase' => 'key-passphrase-secret',
        ];

        $this->actingAs($admin)->post('/admin/realms', $create)->assertRedirect();
        $realm = RealmModel::query()->firstOrFail();

        $this->assertStringNotContainsString(
            'private-key-secret',
            (string) $realm->getRawOriginal('ssh_tunnel'),
        );
        $this->assertStringNotContainsString(
            'key-passphrase-secret',
            (string) $realm->getRawOriginal('ssh_tunnel'),
        );

        $this->actingAs($admin)->get("/admin/realms/{$realm->id}/edit")
            ->assertOk()
            ->assertDontSee('private-key-secret')
            ->assertDontSee('key-passphrase-secret')
            ->assertInertia(fn (Assert $page) => $page
                ->where('realm.connection_type', 'ssh')
                ->where('realm.ssh_tunnel.private_key', '')
                ->where('realm.ssh_tunnel.private_key_passphrase', '')
            );

        $update = $create;
        $update['auth_database']['password'] = '';
        $update['characters_database']['password'] = '';
        $update['remote_console']['password'] = '';
        $update['ssh_tunnel']['private_key'] = '';
        $update['ssh_tunnel']['private_key_passphrase'] = '';

        $this->actingAs($admin)->put("/admin/realms/{$realm->id}", $update)->assertRedirect();

        $realm->refresh();
        $this->assertSame(
            '-----BEGIN OPENSSH PRIVATE KEY----- private-key-secret',
            $realm->ssh_tunnel['private_key'],
        );
        $this->assertSame('key-passphrase-secret', $realm->ssh_tunnel['private_key_passphrase']);
    }

    public function test_replacing_an_ssh_key_replaces_its_passphrase_and_direct_mode_removes_both(): void
    {
        $admin = $this->admin();
        $create = $this->realmData();
        $create['connection_type'] = 'ssh';
        $create['ssh_tunnel'] = [
            'host' => 'bastion.example.test',
            'port' => 22,
            'username' => 'moon-cms',
            'private_key' => 'old-private-key',
            'private_key_passphrase' => 'old-passphrase',
        ];
        $this->actingAs($admin)->post('/admin/realms', $create);
        $realm = RealmModel::query()->firstOrFail();

        $update = $create;
        $update['auth_database']['password'] = '';
        $update['characters_database']['password'] = '';
        $update['remote_console']['password'] = '';
        $update['ssh_tunnel']['private_key'] = 'new-private-key';
        $update['ssh_tunnel']['private_key_passphrase'] = '';

        $this->actingAs($admin)->put("/admin/realms/{$realm->id}", $update)->assertRedirect();
        $realm->refresh();
        $this->assertSame('new-private-key', $realm->ssh_tunnel['private_key']);
        $this->assertNull($realm->ssh_tunnel['private_key_passphrase']);

        $update['connection_type'] = 'direct';
        $update['ssh_tunnel'] = null;
        $this->actingAs($admin)->put("/admin/realms/{$realm->id}", $update)->assertRedirect();

        $this->assertNull($realm->fresh()->ssh_tunnel);
    }

    public function test_connectivity_failure_is_returned_as_a_field_error_without_saving(): void
    {
        $this->app->instance(
            RealmConnectivityVerifierInterface::class,
            new class implements RealmConnectivityVerifierInterface
            {
                public function verify(Realm $realm): void
                {
                    throw new RealmConnectivityException(
                        'ssh_tunnel',
                        'No fue posible iniciar el túnel SSH.',
                    );
                }
            },
        );

        $this->actingAs($this->admin())
            ->post('/admin/realms', $this->realmData())
            ->assertSessionHasErrors('ssh_tunnel');

        $this->assertDatabaseCount('realms', 0);
    }

    /**
     * @return array<string, mixed>
     */
    private function realmData(): array
    {
        return [
            'name' => 'Moon Realm',
            'slug' => 'moon-realm',
            'core_type' => 'trinitycore',
            'gm_realm_id' => -1,
            'enabled' => true,
            'connection_type' => 'direct',
            'ssh_tunnel' => null,
            'auth_database' => [
                'host' => 'mysql',
                'port' => 3306,
                'database' => 'auth',
                'username' => 'trinity',
                'password' => 'auth-secret',
            ],
            'characters_database' => [
                'host' => 'mysql',
                'port' => 3306,
                'database' => 'characters',
                'username' => 'trinity',
                'password' => 'characters-secret',
            ],
            'remote_console' => [
                'host' => 'worldserver',
                'port' => 7878,
                'username' => 'admin',
                'password' => 'soap-secret',
            ],
        ];
    }

    private function admin(): UserModel
    {
        return UserModel::create([
            'name' => 'MoonAdmin',
            'email' => 'admin@moonshard.local',
            'password' => Hash::make('MoonTest!2026'),
            'locale' => 'es',
            'is_admin' => true,
        ]);
    }
}
