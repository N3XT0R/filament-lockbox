<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Tests\Unit\Managers\KeyMaterial;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use N3XT0R\FilamentLockbox\Managers\KeyMaterial\PasskeyKeyMaterialProvider;
use N3XT0R\FilamentLockbox\Tests\TestCase;
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
        $passkey->id = 10;
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

        $expected = hash('sha256', PasskeyKeyMaterialProviderTest . phphash_hmac('sha256', 'cred', 'test-app-key') . 1, true);
        $this->assertSame($expected, $key);
    }
}
