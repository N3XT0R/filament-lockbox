<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Tests\Unit\Forms\Actions;

use Illuminate\Http\Request;
use N3XT0R\FilamentLockbox\Forms\Actions\UnlockLockboxAction;
use N3XT0R\FilamentLockbox\Tests\TestCase;

class UnlockLockboxActionTest extends TestCase
{
    public function testGetDefaultNameReturnsUnlockLockbox(): void
    {
        $this->assertSame('unlockLockbox', UnlockLockboxAction::getDefaultName());
    }

    public function testActionMergesInputIntoRequest(): void
    {
        $request = Request::create('/');
        $this->app->instance('request', $request);

        $action = UnlockLockboxAction::make();
        $closure = $action->getActionFunction();
        $this->assertNotNull($closure);

        $closure(['lockbox_input' => 'secret']);

        $this->assertSame('secret', request('lockbox_input'));
    }
}
