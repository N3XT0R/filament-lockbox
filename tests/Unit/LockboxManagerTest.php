<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Tests\Unit\Support;

use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;
use N3XT0R\FilamentLockbox\Concerns\InteractsWithLockboxKeys;
use N3XT0R\FilamentLockbox\Contracts\HasLockboxKeys;
use N3XT0R\FilamentLockbox\Contracts\UserKeyMaterialProviderInterface;
use N3XT0R\FilamentLockbox\Support\LockboxManager;
use N3XT0R\FilamentLockbox\Support\UserKeyMaterialResolver;
use N3XT0R\FilamentLockbox\Tests\TestCase;

class LockboxManagerTest extends TestCase
{
    public function testForUserReturnsWorkingEncrypter(): void
    {
        Config::set('app.key', 'base64:' . base64_encode(random_bytes(32)));
        $partA = 'server-part';
        $encryptedKey = Crypt::encryptString($partA);

        $user = new class () extends User implements HasLockboxKeys {
            use InteractsWithLockboxKeys;
            protected $guarded = [];
        };
        $user->id = 1;
        $user->setAttribute('encrypted_user_key', $encryptedKey);

        $provider = new class () implements UserKeyMaterialProviderInterface {
            public function supports(User $user): bool
            {
                return true;
            }

            public function provide(User $user, ?string $input): string
            {
                return 'user-part';
            }
        };

        $resolver = new UserKeyMaterialResolver([$provider]);
        $this->app->instance(UserKeyMaterialResolver::class, $resolver);

        $manager = new LockboxManager();

        $encrypter = $manager->forUser($user, null, $provider::class);

        $cipher = $encrypter->encryptString('secret');
        $this->assertSame('secret', $encrypter->decryptString($cipher));
    }
}
