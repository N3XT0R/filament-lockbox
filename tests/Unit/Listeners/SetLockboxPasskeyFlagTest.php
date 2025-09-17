<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Tests\Unit\Listeners;

use Illuminate\Support\Facades\Session;
use N3XT0R\FilamentLockbox\Listeners\SetLockboxPasskeyFlag;
use N3XT0R\FilamentLockbox\Tests\TestCase;
use Spatie\LaravelPasskeys\Events\PasskeyUsedToAuthenticateEvent;
use Spatie\LaravelPasskeys\Http\Requests\AuthenticateUsingPasskeysRequest;
use Spatie\LaravelPasskeys\Models\Passkey;

class SetLockboxPasskeyFlagTest extends TestCase
{
    public function testHandleStoresSessionFlag(): void
    {
        $passkey = new Passkey();
        $passkey->id = 1;

        $request = AuthenticateUsingPasskeysRequest::create('/', 'POST');
        $event = new PasskeyUsedToAuthenticateEvent($passkey, $request);

        $listener = new SetLockboxPasskeyFlag();
        $listener->handle($event);

        $flag = config('filament-lockbox.passkeys.session_flag', 'lockbox_passkey_verified');
        $data = Session::get($flag);

        $this->assertSame(1, $data['passkey_id']);
        $this->assertArrayHasKey('timestamp', $data);
    }
}
