<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Contracts;

use Illuminate\Foundation\Auth\User;

interface UserKeyMaterialProviderInterface
{
    /**
     * Whether this provider supports the given user model.
     */
    public function supports(User $user): bool;

    /**
     * Generate user-specific key material.
     *
     * @throws \RuntimeException if verification fails.
     */
    public function provide(User $user, ?string $input): string;
}
