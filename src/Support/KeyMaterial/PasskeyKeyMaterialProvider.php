<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Support\KeyMaterial;

use Illuminate\Foundation\Auth\User;
use N3XT0R\FilamentLockbox\Contracts\UserKeyMaterialProviderInterface;
use RuntimeException;
use Spatie\LaravelPasskeys\Models\Concerns\HasPasskeys;
use Spatie\LaravelPasskeys\Models\Passkey;

/**
 * Provides key material based on verified passkey credentials.
 *
 * @category Filament Security
 * @package  n3xt0r/filament-lockbox
 * @author   Ilya Beliaev
 * @license  MIT
 * @link     https://github.com/N3XT0R/filament-lockbox
 */
class PasskeyKeyMaterialProvider implements UserKeyMaterialProviderInterface
{
    /**
     * Check whether the user supports passkey-based key material.
     *
     * @param User $user User to check
     *
     * @return bool True if passkeys are supported, false otherwise
     */
    public function supports(User $user): bool
    {
        return $user instanceof HasPasskeys;
    }

    /**
     * Derive key material from the authenticated passkey.
     *
     * @param User        $user  User providing the passkey
     * @param string|null $input Unused input
     *
     * @throws RuntimeException When passkey data is missing or invalid
     *
     * @return string Derived key material
     */
    public function provide(User $user, ?string $input): string
    {
        if (!$this->supports($user)) {
            throw new RuntimeException('Spatie Passkeys is not installed.');
        }

        /**
         * @var User&HasPasskeys $user
         */
        if (!$user->passkeys()->exists()) {
            throw new RuntimeException('User has no registered passkeys.');
        }

        $sessionData = session()->get(
            config('filament-lockbox.passkeys.session_flag', 'lockbox_passkey_verified'),
        );
        if (empty($sessionData)) {
            throw new RuntimeException('Passkey authentication required.');
        }

        $passkeyId = $sessionData['passkey_id'] ?? null;
        if (!$passkeyId) {
            throw new RuntimeException('No passkey id stored in session.');
        }

        /**
         * @var Passkey|null $passkey
         */
        $passkey = $user->passkeys()->find($passkeyId);
        if (!$passkey) {
            throw new RuntimeException('Passkey from session not found for this user.');
        }

        return hash(
            'sha256',
            hash_hmac('sha256', $passkey->getAttribute('credential_id'), config('app.key'))
                . $user->getKey(),
            true,
        );
    }
}
