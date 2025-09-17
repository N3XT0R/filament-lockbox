<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Tests\Unit\Services;

use Illuminate\Encryption\Encrypter;
use Illuminate\Foundation\Auth\User;
use N3XT0R\FilamentLockbox\Concerns\InteractsWithLockbox;
use N3XT0R\FilamentLockbox\Contracts\HasLockbox;
use N3XT0R\FilamentLockbox\Managers\LockboxManager;
use N3XT0R\FilamentLockbox\Services\LockboxService;
use N3XT0R\FilamentLockbox\Tests\TestCase;

class LockboxServiceTest extends TestCase
{
    public function testSetAndGetStoresEncryptedValue(): void
    {
        $user = new class () extends User implements HasLockbox {
            use InteractsWithLockbox;

            protected $guarded = [];
            protected $table = 'users';
        };
        $user->forceFill([
            'name' => 'Test',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ])->save();

        $key = random_bytes(32);
        $encrypter = new Encrypter($key, config('app.cipher'));

        $manager = new class ($encrypter) extends LockboxManager {
            public function __construct(private Encrypter $encrypter)
            {
            }

            public function forUser(User $user, ?string $input = null, ?string $providerClass = null): Encrypter
            {
                return $this->encrypter;
            }
        };
        $this->app->instance(LockboxManager::class, $manager);

        $service = new LockboxService();

        $service->set($user, 'api', 'value123', $user);

        $this->assertDatabaseHas('lockbox', ['name' => 'api', 'user_id' => $user->id]);

        $retrieved = $service->get($user, 'api', $user);
        $this->assertSame('value123', $retrieved);
    }
}
