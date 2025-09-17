<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Contracts\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;
use N3XT0R\FilamentLockbox\Contracts\HasLockbox;
use RuntimeException;

interface LockboxServiceInterface
{
    /**
     * Store an encrypted value for a lockboxable model.
     *
     * @param Model&HasLockbox $lockboxable Model implementing lockbox relation
     * @param string           $name        Lockbox item name
     * @param string           $value       Plain text value to encrypt
     * @param User             $user        User owning the data
     * @param string|null      $input       Optional user-provided secret
     *
     * @return void
     */
    public function set(
        Model&HasLockbox $lockboxable,
        string           $name,
        string           $value,
        User             $user,
        ?string          $input = null,
    ): void;

    /**
     * Determine if a lockbox value exists for the given model and user.
     *
     * @param Model&HasLockbox $lockboxable Model implementing lockbox relation
     * @param string           $name        Lockbox item name
     * @param User|null        $user        Authenticated user
     *
     *
     * @throws RuntimeException If the user model does not support lockbox keys
     * @return bool
     */
    public function exists(
        Model&HasLockbox $lockboxable,
        string           $name,
        ?User            $user,
    ): bool;

    /**
     * Retrieve and decrypt a value from the lockbox.
     *
     * @param Model&HasLockbox $lockboxable Model implementing lockbox relation
     * @param string           $name        Lockbox item name
     * @param User             $user        User owning the data
     * @param string|null      $input       Optional user-provided secret
     *
     * @return string|null Decrypted value or null when missing
     */
    public function get(
        Model&HasLockbox $lockboxable,
        string           $name,
        User             $user,
        ?string          $input = null,
    ): ?string;
}
