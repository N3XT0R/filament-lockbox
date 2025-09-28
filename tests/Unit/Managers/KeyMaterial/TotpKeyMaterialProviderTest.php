<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Tests\Unit\Managers\KeyMaterial;

use Filament\Auth\MultiFactor\App\AppAuthentication;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthentication;
use Illuminate\Foundation\Auth\User;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use N3XT0R\FilamentLockbox\Managers\KeyMaterial\TotpKeyMaterialProvider;
use N3XT0R\FilamentLockbox\Tests\TestCase;
use RuntimeException;

class TotpKeyMaterialProviderTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private function createUser(?string $secret): User&HasAppAuthentication
    {
        $user = new class () extends User implements HasAppAuthentication {
            public ?string $secret = null;
            protected $guarded = [];

            public function getAppAuthenticationSecret(): ?string
            {
                return $this->secret;
            }

            public function saveAppAuthenticationSecret(?string $secret): void
            {
                $this->secret = $secret;
            }

            public function getAppAuthenticationHolderName(): string
            {
                return 'Holder';
            }
        };
        $user->id = 1;
        $user->secret = $secret;

        return $user;
    }

    public function testSupportsReturnsTrueWhenSecretPresent(): void
    {
        $user = $this->createUser('secret');
        $provider = new TotpKeyMaterialProvider();
        $this->assertTrue($provider->supports($user));
    }

    public function testSupportsReturnsFalseWhenSecretMissing(): void
    {
        $user = $this->createUser(null);
        $provider = new TotpKeyMaterialProvider();
        $this->assertFalse($provider->supports($user));
    }

    public function testProvideReturnsDerivedKeyForValidCode(): void
    {
        $user = $this->createUser('secret');
        $provider = new TotpKeyMaterialProvider();

        $mock = Mockery::mock(AppAuthentication::class);
        $mock->shouldReceive('verifyCode')->with('123456', 'secret')->andReturn(true);
        $this->app->instance(AppAuthentication::class, $mock);

        $key = $provider->provide($user, '123456');

        // expected hash is based on secret + user id (not code)
        $expected = hash('sha256', $user->getAppAuthenticationSecret() . $user->getKey(), true);

        $this->assertSame($expected, $key);
    }

    public function testProvideThrowsWhenUserDoesNotImplementInterface(): void
    {
        $user = new User();
        $user->id = 1;

        $provider = new TotpKeyMaterialProvider();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/must implement/');

        $provider->provide($user, '123456');
    }

    public function testProvideThrowsWhenInputIsEmpty(): void
    {
        $user = $this->createUser('secret');
        $provider = new TotpKeyMaterialProvider();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('TOTP input is required.');

        $provider->provide($user, '');
    }

    public function testProvideThrowsWhenCodeVerificationFails(): void
    {
        $user = $this->createUser('secret');
        $provider = new TotpKeyMaterialProvider();

        $mock = Mockery::mock(AppAuthentication::class);
        $mock->shouldReceive('verifyCode')->with('999999', 'secret')->andReturn(false);
        $this->app->instance(AppAuthentication::class, $mock);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid TOTP code.');

        $provider->provide($user, '999999');
    }
}
