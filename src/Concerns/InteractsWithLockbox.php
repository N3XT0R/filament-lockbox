<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User;
use N3XT0R\FilamentLockbox\Models\Lockbox;
use N3XT0R\FilamentLockbox\Support\LockboxService;
use RuntimeException;

/** @phpstan-ignore-next-line */
trait InteractsWithLockbox
{
    public function lockbox(): MorphMany
    {
        $this->ensureModelContext();

        /** @var Model $this */
        return $this->morphMany(Lockbox::class, 'lockboxable');
    }

    public function setLockboxValue(string $name, string $value, User $user): void
    {
        $this->ensureModelContext();

        app(LockboxService::class)->set($this, $name, $value, $user);
    }

    public function getLockboxValue(string $name, User $user): ?string
    {
        $this->ensureModelContext();

        return app(LockboxService::class)->get($this, $name, $user);
    }

    protected function ensureModelContext(): void
    {
        if (!$this instanceof Model) {
            throw new RuntimeException(static::class . ' must be used on an Eloquent Model.');
        }
    }
}
