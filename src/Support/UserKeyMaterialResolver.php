<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Support;

use Illuminate\Foundation\Auth\User;
use N3XT0R\FilamentLockbox\Contracts\UserKeyMaterialProviderInterface;
use RuntimeException;

class UserKeyMaterialResolver
{
    /**
     * @var UserKeyMaterialProviderInterface[]
     */
    protected array $providers;

    public function __construct(iterable $providers = [])
    {
        $this->providers = is_array($providers) ? $providers : iterator_to_array($providers);
    }

    public function registerProvider(UserKeyMaterialProviderInterface $provider): void
    {
        $this->providers[] = $provider;
    }

    public function resolve(User $user, ?string $input, ?string $providerClass = null): string
    {
        if ($providerClass !== null) {
            foreach ($this->providers as $provider) {
                if ($provider::class === $providerClass) {
                    if (!$provider->supports($user)) {
                        throw new RuntimeException(sprintf(
                            'Provider %s does not support model %s.',
                            $providerClass,
                            $user::class,
                        ));
                    }

                    return $provider->provide($user, $input);
                }
            }

            throw new RuntimeException(sprintf(
                'UserKeyMaterial provider %s is not registered.',
                $providerClass,
            ));
        }

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
