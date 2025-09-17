<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Tests\Unit\Forms\Components;

use N3XT0R\FilamentLockbox\Forms\Components\EncryptedTextInput;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

class EncryptedTextInputTest extends TestCase
{
    public function testSetLockboxInputStoresValue(): void
    {
        $component = new EncryptedTextInput('secret');
        $component->setLockboxInput('code');

        $prop = new ReflectionProperty(EncryptedTextInput::class, 'lockboxInput');
        $prop->setAccessible(true);

        $this->assertSame('code', $prop->getValue($component));
    }
}
