<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Tests\Unit\Jobs;

use Illuminate\Encryption\Encrypter;
use Illuminate\Foundation\Auth\User;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use N3XT0R\FilamentLockbox\Jobs\ReencryptLockboxData;
use N3XT0R\FilamentLockbox\Support\LockboxManager;
use PHPUnit\Framework\TestCase;

class ReencryptLockboxDataTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testHandleInvokesManagerForOldAndNewProviders(): void
    {
        $user = new User();
        $job = new ReencryptLockboxData($user, 'old', 'new');

        $manager = Mockery::mock(LockboxManager::class);
        $encrypter = new Encrypter(str_repeat('a', 32), 'AES-256-CBC');
        $manager->shouldReceive('forUser')->once()->with($user, null, 'old')->andReturn($encrypter);
        $manager->shouldReceive('forUser')->once()->with($user, null, 'new')->andReturn($encrypter);

        $job->handle($manager);
    }
}
