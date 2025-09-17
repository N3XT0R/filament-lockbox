<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Tests\Integration\Managers;

use Illuminate\Foundation\Auth\User as BaseUser;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use N3XT0R\FilamentLockbox\Contracts\HasLockboxKeys;
use N3XT0R\FilamentLockbox\Managers\KeyMaterial\CryptoPasswordKeyMaterialProvider;
use N3XT0R\FilamentLockbox\Managers\LockboxManager;
use N3XT0R\FilamentLockbox\Resolvers\UserKeyMaterialResolver;
use N3XT0R\FilamentLockbox\Tests\TestCase;

class LockboxUser extends BaseUser implements HasLockboxKeys
{
    public ?string $encryptedUserKey = null;
    public ?string $cryptoPasswordHash = null;
    public ?string $providerClass = null;

    public function __construct()
    {
        parent::__construct();
        $this->encryptedUserKey = Crypt::encryptString('server-part');
        $this->cryptoPasswordHash = Hash::make('top-secret');
        $this->providerClass = CryptoPasswordKeyMaterialProvider::class;
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
        return $this->cryptoPasswordHash;
    }

    public function setCryptoPasswordHash(string $hash): void
    {
        $this->cryptoPasswordHash = $hash;
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
        // no-op for tests
    }

    public function setCryptoPassword(string $plainPassword): void
    {
        $this->cryptoPasswordHash = Hash::make($plainPassword);
    }
}

class LockboxManagerTest extends TestCase
{
    public function testManagerEncryptsAndDecryptsUsingCryptoPassword(): void
    {
        config(['app.key' => 'base64:' . base64_encode(random_bytes(32))]);

        $user = new LockboxUser();
        $user->id = 1;

        /** @var UserKeyMaterialResolver $resolver */
        $resolver = app(UserKeyMaterialResolver::class);
        $resolver->registerProvider(new CryptoPasswordKeyMaterialProvider());

        $manager = app(LockboxManager::class);
        $encrypter = $manager->forUser($user, 'top-secret');

        $cipher = $encrypter->encryptString('plain-data');
        $this->assertSame('plain-data', $encrypter->decryptString($cipher));
    }
}
