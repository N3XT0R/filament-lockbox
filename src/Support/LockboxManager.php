<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Support;

use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthentication;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use N3XT0R\FilamentLockbox\Contracts\HasLockboxKeys;
use PragmaRX\Google2FA\Google2FA;
use RuntimeException;

class LockboxManager
{
    /**
     * Build an Encrypter instance for the given user model.
     * Combines server-side key (encrypted_user_key) with user-provided input (TOTP or crypto password).
     */
    public function forUser(Model $user, ?string $input = null): Encrypter
    {
        $this->assertValidUserModel($user);

        /** @var Model&HasLockboxKeys $user */
        $encryptedKey = $user->getEncryptedUserKey();

        if (empty($encryptedKey)) {
            throw new RuntimeException('No encrypted user key found for this user.');
        }

        $partA = Crypt::decryptString($encryptedKey);

        $materialResolver = app(UserKeyMaterialResolver::class);
        $partB = $materialResolver->resolve($user, $input);

        // Determine partB based on available authentication method
        $partB = $user instanceof HasAppAuthentication && $user->getAppAuthenticationSecret()
            ? $this->generateFromTotp($user, $input)
            : $this->generateFromCryptoPassword($user, $input);

        // Derive final key from partA and partB
        $finalKey = hash('sha256', $partA . $partB, true);

        return new Encrypter($finalKey, config('app.cipher'));
    }

    /**
     * Generate partB from a TOTP code.
     * Throws exception if verification fails.
     *
     * @param Model&HasAppAuthentication&HasLockboxKeys $user
     */
    protected function generateFromTotp(Model $user, ?string $input): string
    {
        $google2fa = app(Google2FA::class);
        $secret = decrypt($user->getAppAuthenticationSecret());

        if (!$google2fa->verifyKey($secret, (string)$input)) {
            throw new RuntimeException('Invalid TOTP code.');
        }

        return hash('sha256', $input . $user->getKey(), true);
    }

    /**
     * Generate partB from a crypto password.
     * Throws exception if password does not match.
     *
     * @param Model&HasLockboxKeys $user
     */
    protected function generateFromCryptoPassword(Model $user, ?string $input): string
    {
        if (!Hash::check((string)$input, (string)$user->getCryptoPasswordHash())) {
            throw new RuntimeException('Invalid crypto password.');
        }

        return hash_pbkdf2('sha256', (string)$input, (string)$user->getKey(), 100_000, 32, true);
    }

    /**
     * Ensures that the given model implements all required interfaces.
     *
     * @throws RuntimeException
     */
    private function assertValidUserModel(Model $user): void
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
