<?php

/**
 * Key material provider that derives material from a user's crypto password.
 */

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Support\KeyMaterial;

use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Hash;
use N3XT0R\FilamentLockbox\Contracts\HasLockboxKeys;
use N3XT0R\FilamentLockbox\Contracts\UserKeyMaterialProviderInterface;
use RuntimeException;

/**
 * Generates key material from a user-provided crypto password.
 */
class CryptoPasswordKeyMaterialProvider implements UserKeyMaterialProviderInterface
{
    /**
     * Determine if the user has a crypto password configured.
     */
    public function supports(User $user): bool
    {
        return $user instanceof HasLockboxKeys && !empty($user->getCryptoPasswordHash());
    }

    /**
     * Return key material derived from the validated crypto password.
     *
     * @throws RuntimeException when the input is missing or invalid
     */
    public function provide(User $user, ?string $input): string
    {
        if (!$user instanceof HasLockboxKeys) {
            throw new RuntimeException(sprintf(
                'Model %s must implement %s to use CryptoPasswordKeyMaterialProvider.',
                $user::class,
                HasLockboxKeys::class,
            ));
        }

        if ($input === null || $input === '') {
            throw new RuntimeException('Crypto password input is required.');
        }

        $storedHash = $user->getCryptoPasswordHash();

        if (empty($storedHash) || !Hash::check($input, $storedHash)) {
            throw new RuntimeException('Invalid crypto password.');
        }

        // Derive deterministic 32-byte key material from user input
        return hash_pbkdf2('sha256', $input, (string)$user->getKey(), 100_000, 32, true);
    }
}
