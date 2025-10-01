<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Tests\Stubs\Lockbox;

use Illuminate\Foundation\Auth\User as BaseUser;
use N3XT0R\FilamentLockbox\Contracts\HasLockboxKeys;
use N3XT0R\FilamentLockbox\Tests\Stubs\Concerns\HasLockboxUserFactory;

class DummyHasLockboxKeys extends BaseUser implements HasLockboxKeys
{
    use HasLockboxUserFactory;

    protected $guarded = [];
    protected $table = 'users';

    public function getEncryptedUserKey(): ?string
    {
        return null;
    }

    public function setEncryptedUserKey(string $value): void
    {
    }

    public function getCryptoPasswordHash(): ?string
    {
        return null;
    }

    public function setCryptoPasswordHash(string $hash): void
    {
    }

    public function getLockboxProvider(): ?string
    {
        return null;
    }

    public function setLockboxProvider(string $provider): void
    {
    }

    public function initializeUserKeyIfMissing(): void
    {
    }

    public function setCryptoPassword(string $plainPassword): void
    {
    }
}
