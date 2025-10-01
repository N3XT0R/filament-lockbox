<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Resolvers;

use Illuminate\Foundation\Auth\User;
use N3XT0R\FilamentLockbox\Contracts\UserKeyMaterialProviderInterface;
use RuntimeException;

/**
 * Resolves key material using registered providers.
 *
 * @category Filament Security
 * @package  n3xt0r/filament-lockbox
 * @author   Ilya Beliaev
 * @license  MIT
 * @link     https://github.com/N3XT0R/filament-lockbox
 */
class UserKeyMaterialResolver
{
    /**
     * @var UserKeyMaterialProviderInterface[]
     */
    protected array $providers;

    /**
     * @param iterable<UserKeyMaterialProviderInterface> $providers Providers to register
     *
     * @return void
     */
    public function __construct(iterable $providers = [])
    {
        $this->providers = is_array($providers)
            ? $providers
            : iterator_to_array($providers);
    }

    /**
     * Register an additional key material provider.
     *
     * @param UserKeyMaterialProviderInterface $provider Provider instance
     *
     * @return void
     */
    public function registerProvider(UserKeyMaterialProviderInterface $provider): void
    {
        $this->providers[] = $provider;
    }

    /**
     * Resolve key material for a user.
     *
     * @param User        $user          User requiring key material
     * @param string|null $input         Optional user input
     * @param string|null $providerClass Specific provider class to use
     *
     *
     * @throws RuntimeException If no provider can handle the request
     * @return string           Derived key material
     */
    public function resolve(
        User    $user,
        ?string $input,
        ?string $providerClass = null,
    ): string {
        if ($providerClass !== null) {
            return $this->resolveWithSpecificProvider($user, $input, $providerClass);
        }

        return $this->resolveWithAnyProvider($user, $input);
    }

    private function resolveWithSpecificProvider(User $user, ?string $input, string $providerClass): string
    {
        foreach ($this->providers as $provider) {
            if ($provider::class !== $providerClass) {
                continue;
            }

            if (!$provider->supports($user)) {
                throw new RuntimeException(sprintf(
                    'Provider %s does not support model %s.',
                    $providerClass,
                    $user::class,
                ));
            }

            return $provider->provide($user, $input);
        }

        throw new RuntimeException(sprintf(
            'UserKeyMaterial provider %s is not registered.',
            $providerClass,
        ));
    }

    private function resolveWithAnyProvider(User $user, ?string $input): string
    {
        foreach ($this->providers as $provider) {
            if ($provider->supports($user)) {
                return $provider->provide($user, $input);
            }
        }

        throw new RuntimeException(sprintf(
            'No UserKeyMaterial provider could handle model %s.',
            $user::class,
        ));
    }
}
