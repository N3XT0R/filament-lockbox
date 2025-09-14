<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User;

interface HasLockbox
{
    public function lockbox(): MorphMany;

    public function setLockboxValue(string $name, string $value, User $user): void;

    public function getLockboxValue(string $name, User $user): ?string;
}
