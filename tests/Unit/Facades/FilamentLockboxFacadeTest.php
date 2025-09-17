<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Tests\Unit\Facades;

use N3XT0R\FilamentLockbox\Facades\FilamentLockbox;
use N3XT0R\FilamentLockbox\FilamentLockbox as FilamentLockboxRoot;
use N3XT0R\FilamentLockbox\Tests\TestCase;

class FilamentLockboxFacadeTest extends TestCase
{
    public function testFacadeResolvesFilamentLockbox(): void
    {
        $this->assertInstanceOf(FilamentLockboxRoot::class, FilamentLockbox::getFacadeRoot());
    }
}
