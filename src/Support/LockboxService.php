<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;
use N3XT0R\FilamentLockbox\Contracts\HasLockbox;
use N3XT0R\FilamentLockbox\Contracts\HasLockboxKeys;
use N3XT0R\FilamentLockbox\Models\Lockbox;
use RuntimeException;

/**
 * Service for storing and retrieving lockbox values.
 *
 * @category Filament Security
 * @package  n3xt0r/filament-lockbox
 * @author   Ilya Beliaev
 * @license  MIT
 * @link     https://github.com/N3XT0R/filament-lockbox
 */
class LockboxService
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
    ): void {
        $encrypter = app(LockboxManager::class)->forUser($user, $input);

        $lockboxable->lockbox()->updateOrCreate(
            ['name' => $name, 'user_id' => $user->getKey()],
            ['value' => $encrypter->encrypt($value)],
        );
    }

    /**
     * Determine if a lockbox value exists for the given model and user.
     *
     * @param Model&HasLockbox $lockboxable Model implementing lockbox relation
     * @param string           $name        Lockbox item name
     * @param User|null        $user        Authenticated user
     *
     * @throws RuntimeException If the user model does not support lockbox keys
     *
     * @return bool
     */
    public function exists(
        Model&HasLockbox $lockboxable,
        string           $name,
        ?User            $user,
    ): bool {
        if (!$user instanceof HasLockboxKeys) {
            throw new RuntimeException(sprintf(
                'Model %s must implement %s to use LockboxService.',
                $user ? $user::class : 'null',
                HasLockboxKeys::class,
            ));
        }

        return $lockboxable->lockbox()
            ->where('name', $name)
            ->where('user_id', $user->getKey())
            ->exists();
    }

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
    ): ?string {
        /** @var Lockbox|null $record */
        $record = $lockboxable->lockbox()
            ->where('name', $name)
            ->where('user_id', $user->getKey())
            ->first();

        if ($record === null) {
            return null;
        }

        $encrypter = app(LockboxManager::class)->forUser($user, $input);

        return $encrypter->decrypt($record->getAttribute('value'));
    }
}
