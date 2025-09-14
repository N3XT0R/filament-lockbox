<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Tests\Unit\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use N3XT0R\FilamentLockbox\Models\Lockbox;
use N3XT0R\FilamentLockbox\Tests\TestCase;

class LockboxTest extends TestCase
{
    public function testRelationshipsReturnExpectedTypes(): void
    {
        $lockbox = new Lockbox();

        $this->assertInstanceOf(BelongsTo::class, $lockbox->user());
        $this->assertInstanceOf(MorphTo::class, $lockbox->lockboxable());
    }
}
