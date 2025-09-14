<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Random\RandomException;
use RuntimeException;

/**
 * Provides helper methods for models using lockbox keys.
 *
 * @category Filament Security
 * @package  n3xt0r/filament-lockbox
 * @author   Ilya Beliaev
 * @license  MIT
 * @link     https://github.com/N3XT0R/filament-lockbox
 */
trait InteractsWithLockboxKeys
{
    /**
     * Get the encrypted user key.
     *
     * @return string|null Encrypted key or null when missing
     */
    public function getEncryptedUserKey(): ?string
    {
        $this->ensureModelContext();

        /** @var Model $this */
        return $this->getAttribute('encrypted_user_key');
    }

    /**
     * Store the encrypted user key.
     *
     * @param string $value Encrypted key value
     *
     * @return void
     */
    public function setEncryptedUserKey(string $value): void
    {
        $this->ensureModelContext();
        /** @var Model $this */
        $this->setAttribute('encrypted_user_key', $value);
    }

    /**
     * Get the hash of the user's crypto password.
     *
     * @return string|null Hash value or null when unset
     */
    public function getCryptoPasswordHash(): ?string
    {
        $this->ensureModelContext();

        /** @var Model $this */
        return $this->getAttribute('crypto_password_hash');
    }

    /**
     * Set the hash of the user's crypto password.
     *
     * @param string $hash Hashed password value
     *
     * @return void
     */
    public function setCryptoPasswordHash(string $hash): void
    {
        $this->ensureModelContext();
        /** @var Model $this */
        $this->setAttribute('crypto_password_hash', $hash);
    }

    /**
     * Get the configured lockbox provider class.
     *
     * @return string|null Provider class name
     */
    public function getLockboxProvider(): ?string
    {
        $this->ensureModelContext();

        /** @var Model $this */
        return $this->getAttribute('lockbox_provider');
    }

    /**
     * Set the lockbox provider class.
     *
     * @param string $provider Provider class name
     *
     * @return void
     */
    public function setLockboxProvider(string $provider): void
    {
        $this->ensureModelContext();
        /** @var Model $this */
        $this->setAttribute('lockbox_provider', $provider);
        $this->save();
    }

    /**
     * Generate and store an encrypted user key if one does not exist.
     *
     * @throws RandomException If random bytes cannot be generated
     *
     * @return void
     */
    public function initializeUserKeyIfMissing(): void
    {
        $this->ensureModelContext();

        /** @var Model $this */
        if (null === $this->getAttribute('encrypted_user_key')) {
            $rawKey = base64_encode(random_bytes(32));
            /** @var Model $this */
            $this->setAttribute('encrypted_user_key', Crypt::encryptString($rawKey));
            $this->save();
        }
    }

    /**
     * Hash and store a new crypto password.
     *
     * @param string $plainPassword Plain password input
     *
     * @return void
     */
    public function setCryptoPassword(string $plainPassword): void
    {
        $this->ensureModelContext();

        /** @var Model $this */
        $this->setAttribute('crypto_password_hash', Hash::make($plainPassword));
        $this->save();
    }

    /**
     * Determine if the model already has an encrypted user key.
     *
     * @return bool True if a key exists, false otherwise
     */
    public function hasLockboxKey(): bool
    {
        $this->ensureModelContext();

        /** @var Model $this */
        return !empty($this->getAttribute('encrypted_user_key'));
    }

    /**
     * Ensure this trait is used within an Eloquent model context.
     *
     * @throws RuntimeException If not used on a model
     *
     * @return void
     */
    protected function ensureModelContext(): void
    {
        if (!$this instanceof Model) {
            throw new RuntimeException(static::class . ' must be used on an Eloquent Model.');
        }
    }
}
