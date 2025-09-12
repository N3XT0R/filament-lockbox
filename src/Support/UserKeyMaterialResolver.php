<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Support;

use Illuminate\Database\Eloquent\Model;
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

    public function resolve(Model $user, ?string $input): string
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
