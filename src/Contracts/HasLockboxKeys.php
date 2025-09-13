<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Contracts;

interface HasLockboxKeys
{
    /**
     * Get the server-side encrypted user key.
     */
    public function getEncryptedUserKey(): ?string;

    /**
     * Set a new server-side encrypted user key.
     */
    public function setEncryptedUserKey(string $value): void;

    /**
     * Get the hashed crypto password.
     */
    public function getCryptoPasswordHash(): ?string;

    /**
     * Set the hashed crypto password.
     */
    public function setCryptoPasswordHash(string $hash): void;

    /**
     * Get the selected user key material provider.
     */
    public function getLockboxProvider(): ?string;

    /**
     * Set the selected user key material provider.
     */
    public function setLockboxProvider(string $provider): void;

    /**
     * Generate and set a fresh user key if none exists.
     */
    public function initializeUserKeyIfMissing(): void;

    public function setCryptoPassword(string $plainPassword): void;
}
