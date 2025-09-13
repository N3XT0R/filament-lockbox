<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Tests\Unit;

use N3XT0R\FilamentLockbox\FilamentLockboxPlugin;
use N3XT0R\FilamentLockbox\Tests\TestCase;

class FilamentLockboxPluginTest extends TestCase
{
    public function testGetIdReturnsExpectedValue(): void
    {
        $plugin = new FilamentLockboxPlugin();

        $this->assertSame('filament-lockbox', $plugin->getId());
    }

    public function testMakeReturnsInstance(): void
    {
        $plugin = FilamentLockboxPlugin::make();

        $this->assertInstanceOf(FilamentLockboxPlugin::class, $plugin);
    }
}
