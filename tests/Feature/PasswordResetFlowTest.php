<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Modules\Core\Domain\GameAccount\Ports\GameAccountGatewayInterface;
use Modules\Core\Domain\GameAccount\Ports\GameAccountGatewayResolverInterface;
use Modules\Core\Domain\GameAccount\ValueObjects\CoreCredentialPayload;
use Modules\Core\Domain\GameAccount\ValueObjects\GameAccountIdentity;
use Modules\Core\Domain\Realm\Realm;
use Modules\Core\Domain\Realm\ValueObjects\CoreType;
use Modules\Core\Infrastructure\GameCore\PasswordHashStrategy\Srp6PasswordHashStrategy;
use Modules\Core\Infrastructure\Notifications\ResetPasswordNotification;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\GameAccountProvisioningModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\RealmModel;
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
        $this->createRealm();
        $gateway = new PasswordResetGameAccountGateway;
        $gateway->addAccount($user->email, $user->name);
        $this->bindGateway($gateway);

        $this->post('/forgot-password', ['email' => $user->email])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status');

        Notification::assertSentTo($user, ResetPasswordNotification::class);
        $this->assertSame(0, $gateway->emailLookups);
    }

    public function test_game_account_is_imported_when_email_does_not_exist_in_cms(): void
    {
        Notification::fake();
        $realm = $this->createRealm();
        $gateway = new PasswordResetGameAccountGateway;
        $gateway->addAccount('legacy@moonshard.local', 'LEGACYPLAYER');
        $this->bindGateway($gateway);

        $this->post('/forgot-password', ['email' => 'legacy@moonshard.local'])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status');

        $user = UserModel::where('email', 'legacy@moonshard.local')->first();

        $this->assertNotNull($user);
        $this->assertSame('LEGACYPLAYER', $user->name);
        $this->assertSame(1, $gateway->emailLookups);
        $this->assertDatabaseHas('game_account_provisionings', [
            'user_id' => $user->id,
            'realm_id' => $realm->id,
            'game_username' => 'LEGACYPLAYER',
            'status' => 'ready',
        ]);
        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    public function test_imported_game_account_password_is_updated_together_with_cms_password(): void
    {
        Notification::fake();
        $this->createRealm();
        UserModel::create([
            'name' => 'LEGACYPLAYER',
            'email' => 'other@moonshard.local',
            'password' => Hash::make('OtherMoon!2026'),
            'locale' => 'es',
        ]);
        $gateway = new PasswordResetGameAccountGateway;
        $gateway->addAccount('legacy@moonshard.local', 'LEGACYPLAYER');
        $this->bindGateway($gateway);

        $this->post('/forgot-password', ['email' => 'legacy@moonshard.local']);

        $user = UserModel::where('email', 'legacy@moonshard.local')->firstOrFail();
        $this->assertNotSame('LEGACYPLAYER', $user->name);
        $token = Password::broker()->createToken($user);

        $this->post('/reset-password', [
            'email' => $user->email,
            'token' => $token,
            'password' => 'NewMoon!2026',
            'password_confirmation' => 'NewMoon!2026',
        ])->assertRedirectToRoute('login');

        $this->assertTrue(Hash::check('NewMoon!2026', $user->fresh()->password));
        $this->assertSame(['LEGACYPLAYER'], $gateway->updatedUsernames);
        $this->assertNotEmpty($gateway->updatedCredentials[0]['salt'] ?? null);
        $this->assertNotEmpty($gateway->updatedCredentials[0]['verifier'] ?? null);
        $this->assertTrue((new Srp6PasswordHashStrategy)->verify(
            'LEGACYPLAYER',
            'NewMoon!2026',
            $gateway->updatedCredentials[0],
        ));
        $this->assertSame(
            'ready',
            GameAccountProvisioningModel::where('user_id', $user->id)->value('status'),
        );
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

    private function createRealm(): RealmModel
    {
        return RealmModel::create([
            'name' => 'Moon Realm',
            'slug' => 'moon-realm',
            'core_type' => CoreType::TRINITYCORE->value,
            'auth_database' => [
                'host' => '127.0.0.1',
                'port' => 3306,
                'database' => 'auth',
                'username' => 'moon',
                'password' => 'secret',
            ],
            'characters_database' => null,
            'remote_console' => [
                'host' => '127.0.0.1',
                'port' => 7878,
                'username' => 'soap',
                'password' => 'secret',
            ],
            'ssh_tunnel' => null,
            'gm_realm_id' => -1,
            'enabled' => true,
        ]);
    }

    private function bindGateway(PasswordResetGameAccountGateway $gateway): void
    {
        $this->app->instance(
            GameAccountGatewayResolverInterface::class,
            new class($gateway) implements GameAccountGatewayResolverInterface
            {
                public function __construct(
                    private readonly GameAccountGatewayInterface $gateway,
                ) {}

                public function resolve(CoreType $coreType): GameAccountGatewayInterface
                {
                    return $this->gateway;
                }
            },
        );
    }
}

final class PasswordResetGameAccountGateway implements GameAccountGatewayInterface
{
    /** @var array<string, string> */
    private array $accountsByEmail = [];

    public int $emailLookups = 0;

    /** @var array<int, string> */
    public array $updatedUsernames = [];

    /** @var array<int, array<string, string>> */
    public array $updatedCredentials = [];

    public function addAccount(string $email, string $username): void
    {
        $this->accountsByEmail[mb_strtolower($email)] = strtoupper($username);
    }

    public function accountExists(Realm $realm, string $username): bool
    {
        return in_array(strtoupper($username), $this->accountsByEmail, true);
    }

    public function findAccountByEmail(Realm $realm, string $email): ?GameAccountIdentity
    {
        $this->emailLookups++;
        $username = $this->accountsByEmail[mb_strtolower($email)] ?? null;

        return $username === null
            ? null
            : new GameAccountIdentity($username, $email);
    }

    public function createAccount(
        Realm $realm,
        string $username,
        string $email,
        CoreCredentialPayload $credentials,
    ): int {
        $this->addAccount($email, $username);

        return count($this->accountsByEmail);
    }

    public function updatePassword(
        Realm $realm,
        string $username,
        CoreCredentialPayload $credentials,
    ): void {
        $this->updatedUsernames[] = $username;
        $this->updatedCredentials[] = $credentials->columns();
    }

    public function setGmLevel(Realm $realm, string $username, int $gmLevel): void {}

    public function deleteAccount(Realm $realm, string $username): void {}
}
