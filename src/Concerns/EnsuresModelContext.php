<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Concerns;

use Illuminate\Database\Eloquent\Model;
use RuntimeException;

trait EnsuresModelContext
{
    private function ensureModelContext(): void
    {
        if (!$this instanceof Model) {
            throw new RuntimeException(static::class . ' must be used on an Eloquent Model.');
        }
    }
}
