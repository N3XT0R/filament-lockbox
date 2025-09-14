<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Tests\Unit\Support;

use N3XT0R\FilamentLockbox\Support\Composer\Package;
use PHPUnit\Framework\TestCase;

class PackageTest extends TestCase
{
    public function testIsInstalledReturnsExpectedValues(): void
    {
        $this->assertTrue(Package::isInstalled('phpunit/phpunit'));
        $this->assertFalse(Package::isInstalled('nonexistent/package'));
    }

    public function testGetVersionReturnsStringOrNull(): void
    {
        $this->assertIsString(Package::getVersion('phpunit/phpunit'));
        $this->assertNull(Package::getVersion('nonexistent/package'));
    }
}
