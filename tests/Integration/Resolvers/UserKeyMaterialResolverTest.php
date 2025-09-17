<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Tests\Integration\Resolvers;

use Filament\Auth\MultiFactor\App\AppAuthentication;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthentication;
use Illuminate\Foundation\Auth\User as BaseUser;
use N3XT0R\FilamentLockbox\Managers\KeyMaterial\TotpKeyMaterialProvider;
use N3XT0R\FilamentLockbox\Resolvers\UserKeyMaterialResolver;
use N3XT0R\FilamentLockbox\Tests\TestCase;

class TotpUser extends BaseUser implements HasAppAuthentication
{
    public ?string $appAuthenticationSecret = 'totp-secret';

    public function getAppAuthenticationSecret(): ?string
    {
        return $this->appAuthenticationSecret;
    }

    public function saveAppAuthenticationSecret(?string $secret): void
    {
        $this->appAuthenticationSecret = $secret;
    }

    public function getAppAuthenticationHolderName(): string
    {
        return 'Test User';
    }
}

class UserKeyMaterialResolverTest extends TestCase
{
    public function testResolveUsesTotpProviderWithVerifiedCode(): void
    {
        config(['app.key' => 'base64:' . base64_encode(random_bytes(32))]);

        $user = new TotpUser();
        $user->id = 1;

        $this->mock(AppAuthentication::class)
            ->shouldReceive('verifyCode')
            ->once()
            ->with('123456', $user->getAppAuthenticationSecret())
            ->andReturn(true);

        /** @var UserKeyMaterialResolver $resolver */
        $resolver = app(UserKeyMaterialResolver::class);
        $resolver->registerProvider(new TotpKeyMaterialProvider());

        $material = $resolver->resolve($user, '123456');
        $this->assertSame(
            hash('sha256', '123456' . $user->getKey(), true),
            $material,
        );
    }
}
