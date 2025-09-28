<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Tests\Unit\Managers\KeyMaterial;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use N3XT0R\FilamentLockbox\Managers\KeyMaterial\PasskeyKeyMaterialProvider;
use N3XT0R\FilamentLockbox\Tests\TestCase;
use RuntimeException;
use Spatie\LaravelPasskeys\Models\Concerns\HasPasskeys;
use Spatie\LaravelPasskeys\Models\Passkey;

class PasskeyKeyMaterialProviderTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testSupportsReturnsTrueForHasPasskeysUser(): void
    {
        $user = Mockery::mock(User::class, HasPasskeys::class);
        $provider = new PasskeyKeyMaterialProvider();
        $this->assertTrue($provider->supports($user));
    }

    public function testProvideReturnsDerivedKeyUsingSessionPasskey(): void
    {
        config(['app.key' => 'test-app-key']);

        $user = Mockery::mock(User::class, HasPasskeys::class);
        $user->shouldReceive('getKey')->andReturn(1);

        $passkey = new Passkey();
        $passkey->setAttribute($passkey->getKeyName(), 10);
        $passkey->setAttribute('credential_id', 'cred');

        $relation = Mockery::mock(HasMany::class);
        $relation->shouldReceive('exists')->andReturn(true);
        $relation->shouldReceive('find')->with(10)->andReturn($passkey);
        $user->shouldReceive('passkeys')->andReturn($relation);

        session()->put('lockbox_passkey_verified', [
            'timestamp' => now()->getTimestamp(),
            'passkey_id' => 10,
        ]);

        $provider = new PasskeyKeyMaterialProvider();
        $key = $provider->provide($user, null);

        $expected = hash('sha256', hash_hmac('sha256', 'cred', 'test-app-key') . 1, true);
        $this->assertSame($expected, $key);
    }

    public function testProvideThrowsWhenNoPasskeysRegistered(): void
    {
        $user = Mockery::mock(User::class, HasPasskeys::class);
        $user->shouldReceive('passkeys->exists')->andReturn(false);

        $provider = new PasskeyKeyMaterialProvider();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('User has no registered passkeys.');

        $provider->provide($user, null);
    }

    public function testProvideThrowsWhenSessionIsEmpty(): void
    {
        $user = Mockery::mock(User::class, HasPasskeys::class);
        $relation = Mockery::mock(HasMany::class);
        $relation->shouldReceive('exists')->andReturn(true);
        $user->shouldReceive('passkeys')->andReturn($relation);

        session()->forget('lockbox_passkey_verified');

        $provider = new PasskeyKeyMaterialProvider();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Passkey authentication required.');

        $provider->provide($user, null);
    }

    public function testProvideThrowsWhenSessionHasNoPasskeyId(): void
    {
        $user = Mockery::mock(User::class, HasPasskeys::class);
        $relation = Mockery::mock(HasMany::class);
        $relation->shouldReceive('exists')->andReturn(true);
        $user->shouldReceive('passkeys')->andReturn($relation);

        session()->put('lockbox_passkey_verified', [
            'timestamp' => now()->getTimestamp(),
            // deliberately missing passkey_id
        ]);

        $provider = new PasskeyKeyMaterialProvider();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No passkey id stored in session.');

        $provider->provide($user, null);
    }

    public function testProvideThrowsWhenPasskeyNotFound(): void
    {
        $user = Mockery::mock(User::class, HasPasskeys::class);
        $relation = Mockery::mock(HasMany::class);
        $relation->shouldReceive('exists')->andReturn(true);
        $relation->shouldReceive('find')->with(99)->andReturn(null);
        $user->shouldReceive('passkeys')->andReturn($relation);

        session()->put('lockbox_passkey_verified', [
            'timestamp' => now()->getTimestamp(),
            'passkey_id' => 99,
        ]);

        $provider = new PasskeyKeyMaterialProvider();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Passkey from session not found for this user.');

        $provider->provide($user, null);
    }
}
