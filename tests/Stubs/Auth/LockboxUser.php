<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Tests\Stubs\Auth;

use Illuminate\Foundation\Auth\User as BaseUser;
use Illuminate\Support\Facades\Crypt;
use N3XT0R\FilamentLockbox\Concerns\InteractsWithLockbox;
use N3XT0R\FilamentLockbox\Contracts\HasLockbox;
use N3XT0R\FilamentLockbox\Contracts\HasLockboxKeys;

class LockboxUser extends BaseUser implements HasLockboxKeys, HasLockbox
{
    use InteractsWithLockbox;

    protected $guarded = [];
    protected $table = 'users';

    public ?string $encryptedUserKey = null;
    public ?string $providerClass = null;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->encryptedUserKey = Crypt::encryptString('server-key');
    }

    public function getEncryptedUserKey(): ?string
    {
        return $this->encryptedUserKey;
    }

    public function setEncryptedUserKey(string $value): void
    {
        $this->encryptedUserKey = $value;
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
        return $this->providerClass;
    }

    public function setLockboxProvider(string $provider): void
    {
        $this->providerClass = $provider;
    }

    public function initializeUserKeyIfMissing(): void
    {
    }

    public function setCryptoPassword(string $plainPassword): void
    {
    }
}
