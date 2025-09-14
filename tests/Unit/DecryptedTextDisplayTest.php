<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Tests\Unit\Forms;

use N3XT0R\FilamentLockbox\Forms\Components\DecryptedTextDisplay;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

class DecryptedTextDisplayTest extends TestCase
{
    public function testSetLockboxInputStoresValue(): void
    {
        $component = new DecryptedTextDisplay('field');
        $component->setLockboxInput('secret');

        $prop = new ReflectionProperty(DecryptedTextDisplay::class, 'lockboxInput');
        $prop->setAccessible(true);

        $this->assertSame('secret', $prop->getValue($component));
    }
}
