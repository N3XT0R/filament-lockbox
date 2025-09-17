<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Managers;

use Illuminate\Encryption\Encrypter;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Crypt;
use N3XT0R\FilamentLockbox\Contracts\HasLockboxKeys;
use N3XT0R\FilamentLockbox\Resolvers\UserKeyMaterialResolver;
use RuntimeException;

/**
 * Creates encrypter instances for users using lockbox keys.
 *
 * @category Filament Security
 * @package  n3xt0r/filament-lockbox
 * @author   Ilya Beliaev
 * @license  MIT
 * @link     https://github.com/N3XT0R/filament-lockbox
 */
class LockboxManager
{
    /**
     * Build an encrypter for the given user model.
     * Combines the stored encrypted key with user-provided material.
     *
     * @param User        $user          User to build the encrypter for
     * @param string|null $input         Optional user-provided secret
     * @param string|null $providerClass Provider class override
     *
     * @throws RuntimeException If required data is missing
     * @return Encrypter        Encrypter instance for the user
     *
     */
    public function forUser(
        User    $user,
        ?string $input = null,
        ?string $providerClass = null,
    ): Encrypter {
        $this->assertValidUserModel($user);

        /** @var User&HasLockboxKeys $user */
        $encryptedKey = $user->getEncryptedUserKey();

        if (empty($encryptedKey)) {
            throw new RuntimeException('No encrypted user key found for this user.');
        }

        $partA = Crypt::decryptString($encryptedKey);

        $materialResolver = app(UserKeyMaterialResolver::class);
        $providerClass ??= $user->getLockboxProvider();
        $partB = $materialResolver->resolve($user, $input, $providerClass);

        // Derive final key from partA and partB
        $finalKey = hash_hkdf('sha256', $partA . $partB, 32, 'filament-lockbox');

        return new Encrypter($finalKey, config('app.cipher'));
    }

    /**
     * Ensure the given model implements required interfaces.
     *
     * @param User $user User model to validate
     *
     * @throws RuntimeException If the model is invalid
     * @return void
     *
     */
    private function assertValidUserModel(User $user): void
    {
        if (!$user instanceof HasLockboxKeys) {
            throw new RuntimeException(sprintf(
                'The model %s must implement %s to be used with LockboxManager.',
                $user::class,
                HasLockboxKeys::class,
            ));
        }
    }
}
