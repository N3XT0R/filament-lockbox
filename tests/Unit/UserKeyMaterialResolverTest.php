<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Tests\Unit;

use Illuminate\Foundation\Auth\User;
use N3XT0R\FilamentLockbox\Contracts\UserKeyMaterialProviderInterface;
use N3XT0R\FilamentLockbox\Support\UserKeyMaterialResolver;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class UserKeyMaterialResolverTest extends TestCase
{
    public function testResolveReturnsValueFromSupportingProvider(): void
    {
        $provider = new class implements UserKeyMaterialProviderInterface {
            public function supports(User $user): bool
            {
                return true;
            }

            public function provide(User $user, ?string $input): string
            {
                return 'secret';
            }
        };

        $resolver = new UserKeyMaterialResolver([$provider]);
        $user = new User();

        $this->assertSame('secret', $resolver->resolve($user, null));
    }

    public function testRegisterProviderAddsProvider(): void
    {
        $resolver = new UserKeyMaterialResolver();

        $resolver->registerProvider(new class implements UserKeyMaterialProviderInterface {
            public function supports(User $user): bool
            {
                return true;
            }

            public function provide(User $user, ?string $input): string
            {
                return 'abc';
            }
        });

        $user = new User();

        $this->assertSame('abc', $resolver->resolve($user, null));
    }

    public function testResolveThrowsExceptionWhenNoProviderSupportsUser(): void
    {
        $resolver = new UserKeyMaterialResolver();
        $user = new User();

        $this->expectException(RuntimeException::class);
        $resolver->resolve($user, null);
    }
}
