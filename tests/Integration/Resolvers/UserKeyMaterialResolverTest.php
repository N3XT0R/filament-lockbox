<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Tests\Integration\Resolvers;

use Filament\Auth\MultiFactor\App\AppAuthentication;
use Illuminate\Foundation\Auth\User as BaseUser;
use N3XT0R\FilamentLockbox\Contracts\UserKeyMaterialProviderInterface;
use N3XT0R\FilamentLockbox\Managers\KeyMaterial\TotpKeyMaterialProvider;
use N3XT0R\FilamentLockbox\Resolvers\UserKeyMaterialResolver;
use N3XT0R\FilamentLockbox\Tests\Stubs\Auth\TotpUser;
use N3XT0R\FilamentLockbox\Tests\TestCase;
use RuntimeException;

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
            hash('sha256', $user->getAppAuthenticationSecret() . $user->getKey(), true),
            $material,
        );
    }

    public function testResolveThrowsOnInvalidCode(): void
    {
        $user = new TotpUser();
        $user->id = 1;

        $this->mock(AppAuthentication::class)
            ->shouldReceive('verifyCode')
            ->once()
            ->with('000000', $user->getAppAuthenticationSecret())
            ->andReturn(false);

        $resolver = app(UserKeyMaterialResolver::class);
        $resolver->registerProvider(new TotpKeyMaterialProvider());

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid TOTP code.');

        $resolver->resolve($user, '000000');
    }

    public function testResolveThrowsWhenInputIsMissing(): void
    {
        $user = new TotpUser();
        $user->id = 1;

        $resolver = app(UserKeyMaterialResolver::class);
        $resolver->registerProvider(new TotpKeyMaterialProvider());

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('TOTP input is required.');

        $resolver->resolve($user, null);
    }

    public function testResolveThrowsWhenUserDoesNotImplementInterface(): void
    {
        $user = new class () extends BaseUser {
            // no HasAppAuthentication
        };
        $user->id = 1;

        $resolver = app(UserKeyMaterialResolver::class);
        $resolver->registerProvider(new TotpKeyMaterialProvider());

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/No UserKeyMaterial provider could handle model/');

        $resolver->resolve($user, '123456');
    }

    public function testResolveWithSpecificProviderSucceeds(): void
    {
        $user = new TotpUser();
        $user->id = 1;

        $this->mock(AppAuthentication::class)
            ->shouldReceive('verifyCode')
            ->once()
            ->with('123456', $user->getAppAuthenticationSecret())
            ->andReturn(true);

        $resolver = app(UserKeyMaterialResolver::class);
        $resolver->registerProvider(new TotpKeyMaterialProvider());

        $material = $resolver->resolve($user, '123456', TotpKeyMaterialProvider::class);

        $this->assertSame(
            hash('sha256', $user->getAppAuthenticationSecret() . $user->getKey(), true),
            $material,
        );
    }

    public function testResolveWithSpecificProviderThrowsIfNotSupporting(): void
    {
        $user = new TotpUser();
        $user->id = 1;

        // Dummy provider that never supports
        $dummyProvider = new class () implements UserKeyMaterialProviderInterface {
            public function supports($user): bool
            {
                return false;
            }

            public function provide($user, ?string $input): string
            {
                return 'dummy';
            }
        };

        $resolver = app(UserKeyMaterialResolver::class);
        $resolver->registerProvider($dummyProvider);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/does not support model/');

        $resolver->resolve($user, 'anything', $dummyProvider::class);
    }

    public function testResolveWithSpecificProviderThrowsIfNotRegistered(): void
    {
        $user = new TotpUser();
        $user->id = 1;

        $resolver = app(UserKeyMaterialResolver::class);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/is not registered/');

        $resolver->resolve($user, 'whatever', 'Unregistered\\Provider\\Class');
    }
}
