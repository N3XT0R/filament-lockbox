<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Tests\Unit\Support;

use Filament\Auth\MultiFactor\App\AppAuthentication;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthentication;
use Illuminate\Foundation\Auth\User;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use N3XT0R\FilamentLockbox\Support\KeyMaterial\TotpKeyMaterialProvider;
use N3XT0R\FilamentLockbox\Tests\TestCase;

class TotpKeyMaterialProviderTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private function createUser(string $secret): User&HasAppAuthentication
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

    public function testProvideReturnsDerivedKeyForValidCode(): void
    {
        $user = $this->createUser('secret');
        $provider = new TotpKeyMaterialProvider();

        $mock = Mockery::mock(AppAuthentication::class);
        $mock->shouldReceive('verifyCode')->with('123456', 'secret')->andReturn(true);
        $this->app->instance(AppAuthentication::class, $mock);

        $key = $provider->provide($user, '123456');
        $expected = hash('sha256', '123456' . $user->getKey(), true);
        $this->assertSame($expected, $key);
    }
}
