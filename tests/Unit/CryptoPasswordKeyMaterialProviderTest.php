<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Tests\Unit\Support;

use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Hash;
use N3XT0R\FilamentLockbox\Concerns\InteractsWithLockboxKeys;
use N3XT0R\FilamentLockbox\Contracts\HasLockboxKeys;
use N3XT0R\FilamentLockbox\Managers\KeyMaterial\CryptoPasswordKeyMaterialProvider;
use N3XT0R\FilamentLockbox\Tests\TestCase;
use RuntimeException;

class CryptoPasswordKeyMaterialProviderTest extends TestCase
{
    private function createUser(string $password): User&HasLockboxKeys
    {
        $user = new class () extends User implements HasLockboxKeys {
            use InteractsWithLockboxKeys;

            protected $guarded = [];
        };

        $user->id = 1;
        $user->setAttribute('crypto_password_hash', Hash::make($password));

        return $user;
    }

    public function testSupportsReturnsTrueWhenHashExists(): void
    {
        $user = $this->createUser('secret');
        $provider = new CryptoPasswordKeyMaterialProvider();

        $this->assertTrue($provider->supports($user));
    }

    public function testProvideReturnsDerivedKeyForValidPassword(): void
    {
        $password = 'secret';
        $user = $this->createUser($password);
        $provider = new CryptoPasswordKeyMaterialProvider();

        $key = $provider->provide($user, $password);

        $this->assertSame(32, strlen($key));
    }

    public function testSupportsReturnsFalseWithoutHash(): void
    {
        $user = new class () extends User implements HasLockboxKeys {
            use InteractsWithLockboxKeys;

            protected $guarded = [];
        };
        $user->id = 1;
        $provider = new CryptoPasswordKeyMaterialProvider();
        $this->assertFalse($provider->supports($user));
    }

    public function testProvideThrowsExceptionForInvalidPassword(): void
    {
        $user = $this->createUser('secret');
        $provider = new CryptoPasswordKeyMaterialProvider();
        $this->expectException(RuntimeException::class);
        $provider->provide($user, 'wrong');
    }
}
