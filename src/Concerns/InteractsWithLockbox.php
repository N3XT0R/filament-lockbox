<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User;
use N3XT0R\FilamentLockbox\Models\Lockbox;
use N3XT0R\FilamentLockbox\Service\LockboxService;
use RuntimeException;

/** @phpstan-ignore-next-line */

/**
 * Adds lockbox relation and helpers to models.
 *
 * @category Filament Security
 * @package  n3xt0r/filament-lockbox
 * @author   Ilya Beliaev
 * @license  MIT
 * @link     https://github.com/N3XT0R/filament-lockbox
 */
trait InteractsWithLockbox
{
    /**
     * Define the lockbox relationship.
     *
     * @return MorphMany
     */
    public function lockbox(): MorphMany
    {
        $this->ensureModelContext();

        /** @var Model $this */
        return $this->morphMany(Lockbox::class, 'lockboxable');
    }

    /**
     * Store an encrypted value for the model.
     *
     * @param string $name  Lockbox item name
     * @param string $value Plain text value to encrypt
     * @param User   $user  User owning the data
     *
     * @return void
     */
    public function setLockboxValue(string $name, string $value, User $user): void
    {
        $this->ensureModelContext();

        app(LockboxService::class)->set($this, $name, $value, $user);
    }

    /**
     * Retrieve and decrypt an item from the model's lockbox.
     *
     * @param string $name Lockbox item name
     * @param User   $user User owning the data
     *
     * @return string|null Decrypted value or null when missing
     */
    public function getLockboxValue(string $name, User $user): ?string
    {
        $this->ensureModelContext();

        return app(LockboxService::class)->get($this, $name, $user);
    }

    /**
     * Ensure this trait is used within an Eloquent model.
     *
     * @throws RuntimeException If not used on a model
     * @return void
     *
     */
    protected function ensureModelContext(): void
    {
        if (!$this instanceof Model) {
            throw new RuntimeException(static::class . ' must be used on an Eloquent Model.');
        }
    }
}
