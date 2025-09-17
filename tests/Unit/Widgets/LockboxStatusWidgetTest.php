<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Tests\Unit\Widgets;

use Illuminate\Foundation\Auth\User;
use Livewire\Livewire;
use N3XT0R\FilamentLockbox\Contracts\HasLockboxKeys;
use N3XT0R\FilamentLockbox\Managers\KeyMaterial\CryptoPasswordKeyMaterialProvider;
use N3XT0R\FilamentLockbox\Tests\TestCase;
use N3XT0R\FilamentLockbox\Widgets\LockboxStatusWidget;

class LockboxStatusWidgetTest extends TestCase
{
    public function testMountWithoutSupportDisablesWidget(): void
    {
        $user = new User();
        $this->be($user);

        Livewire::actingAs($user)
            ->test(LockboxStatusWidget::class)
            ->assertSet('supportsLockbox', false);
    }

    public function testMountWithSupportingUserSetsProvider(): void
    {
        $user = new class () extends User implements HasLockboxKeys {
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
                return CryptoPasswordKeyMaterialProvider::class;
            }

            public function setLockboxProvider(string $provider): void
            {
            }

            public function setCryptoPassword(string $plainPassword): void
            {
            }

            public function initializeUserKeyIfMissing(): void
            {
            }

            public function hasLockboxKey(): bool
            {
                return false;
            }
        };

        Livewire::actingAs($user)
            ->test(LockboxStatusWidget::class)
            ->assertSet('supportsLockbox', true)
            ->assertSet('provider', CryptoPasswordKeyMaterialProvider::class);
    }
}
