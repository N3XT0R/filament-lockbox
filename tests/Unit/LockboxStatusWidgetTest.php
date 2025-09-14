<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Tests\Unit\Widgets;

use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Auth;
use N3XT0R\FilamentLockbox\Concerns\InteractsWithLockboxKeys;
use N3XT0R\FilamentLockbox\Contracts\HasLockboxKeys;
use N3XT0R\FilamentLockbox\Support\KeyMaterial\CryptoPasswordKeyMaterialProvider;
use N3XT0R\FilamentLockbox\Tests\TestCase;
use N3XT0R\FilamentLockbox\Widgets\LockboxStatusWidget;

class LockboxStatusWidgetTest extends TestCase
{
    public function testMountWithoutSupportDisablesWidget(): void
    {
        $user = new User();
        Auth::login($user);

        $widget = new LockboxStatusWidget();
        $widget->mount();

        $this->assertFalse($widget->supportsLockbox);
    }

    public function testMountWithSupportingUserSetsProvider(): void
    {
        $user = new class () extends User implements HasLockboxKeys {
            use InteractsWithLockboxKeys;
            protected $guarded = [];
        };
        $user->id = 1;
        $user->setAttribute('lockbox_provider', CryptoPasswordKeyMaterialProvider::class);
        Auth::login($user);

        $widget = new LockboxStatusWidget();
        $widget->mount();

        $this->assertTrue($widget->supportsLockbox);
        $this->assertSame(CryptoPasswordKeyMaterialProvider::class, $widget->provider);
    }
}
