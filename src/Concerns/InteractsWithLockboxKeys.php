<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Random\RandomException;
use RuntimeException;

trait InteractsWithLockboxKeys
{
    public function getEncryptedUserKey(): ?string
    {
        $this->ensureModelContext();

        /** @var Model $this */
        return $this->getAttribute('encrypted_user_key');
    }

    public function setEncryptedUserKey(string $value): void
    {
        $this->ensureModelContext();
        /** @var Model $this */
        $this->setAttribute('encrypted_user_key', $value);
    }

    public function getCryptoPasswordHash(): ?string
    {
        $this->ensureModelContext();

        /** @var Model $this */
        return $this->getAttribute('crypto_password_hash');
    }

    public function setCryptoPasswordHash(string $hash): void
    {
        $this->ensureModelContext();
        /** @var Model $this */
        $this->setAttribute('crypto_password_hash', $hash);
    }

    public function getLockboxProvider(): ?string
    {
        $this->ensureModelContext();

        /** @var Model $this */
        return $this->getAttribute('lockbox_provider');
    }

    public function setLockboxProvider(string $provider): void
    {
        $this->ensureModelContext();
        /** @var Model $this */
        $this->setAttribute('lockbox_provider', $provider);
        $this->save();
    }

    /**
     * Generate a new encrypted user key if none exists.
     * @throws RandomException
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
     * Helper to set a new crypto password and hash it securely.
     */
    public function setCryptoPassword(string $plainPassword): void
    {
        $this->ensureModelContext();

        /** @var Model $this */
        $this->setAttribute('crypto_password_hash', Hash::make($plainPassword));
        $this->save();
    }

    /**
     * Check if a user already has a generated encryption key.
     */
    public function hasLockboxKey(): bool
    {
        $this->ensureModelContext();

        /** @var Model $this */
        return !empty($this->getAttribute('encrypted_user_key'));
    }

    /**
     * Ensure this trait is used within an Eloquent Model context.
     * @throws RuntimeException
     */
    protected function ensureModelContext(): void
    {
        if (!$this instanceof Model) {
            throw new RuntimeException(static::class . ' must be used on an Eloquent Model.');
        }
    }
}
