<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Tests\Integration;

use N3XT0R\FilamentLockbox\Tests\TestCase;

class FilamentLockboxCommandTest extends TestCase
{
    public function testCommandRunsSuccessfully(): void
    {
        $this->artisan('filament-lockbox')
            ->expectsOutput('All done')
            ->assertExitCode(0);
    }
}
