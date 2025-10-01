<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Tests\Stubs\Providers;

use Illuminate\Foundation\Auth\User as BaseUser;
use N3XT0R\FilamentLockbox\Contracts\UserKeyMaterialProviderInterface;

class EchoProvider implements UserKeyMaterialProviderInterface
{
    public function supports(BaseUser $user): bool
    {
        return true;
    }

    public function provide(BaseUser $user, ?string $input): string
    {
        return $input ?? '';
    }
}
