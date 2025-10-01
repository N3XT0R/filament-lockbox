<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Tests\Stubs\Concerns;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use N3XT0R\FilamentLockbox\Database\Factories\LockboxUserFactory;

trait HasLockboxUserFactory
{
    use HasFactory;

    protected static function newFactory(): Factory
    {
        return LockboxUserFactory::new();
    }
}
