<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Support;

use Illuminate\Encryption\Encrypter;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Crypt;
use N3XT0R\FilamentLockbox\Contracts\HasLockboxKeys;
use RuntimeException;

class LockboxManager
{
    /**
     * Build an Encrypter instance for the given user model.
     * Combines server-side key (encrypted_user_key) with user-provided input (TOTP or crypto password).
     *
     */
    public function forUser(User $user, ?string $input = null): Encrypter
    {
        $this->assertValidUserModel($user);

        /** @var User&HasLockboxKeys $user */
        $encryptedKey = $user->getEncryptedUserKey();

        if (empty($encryptedKey)) {
            throw new RuntimeException('No encrypted user key found for this user.');
        }

        $partA = Crypt::decryptString($encryptedKey);

        $materialResolver = app(UserKeyMaterialResolver::class);
        $partB = $materialResolver->resolve($user, $input);

        // Derive final key from partA and partB
        $finalKey = hash_hkdf('sha256', $partA . $partB, 32, 'filament-lockbox');

        return new Encrypter($finalKey, config('app.cipher'));
    }

    /**
     * Ensures that the given model implements all required interfaces.
     *
     * @throws RuntimeException
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
