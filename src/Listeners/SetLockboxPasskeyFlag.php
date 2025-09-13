<?php


declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Listeners;

use Illuminate\Support\Facades\Session;
use Spatie\LaravelPasskeys\Events\PasskeyUsedToAuthenticateEvent;

/**
 * Marks the current session as passkey-verified after a successful Passkey login.
 * This does not log in the user – Spatie handles that – it only adds a session flag
 * that Lockbox can later read to derive key material (or to allow unlocking).
 */
class SetLockboxPasskeyFlag
{
    public function handle(PasskeyUsedToAuthenticateEvent $event): void
    {
        $flag = config('filament-lockbox.passkeys.session_flag', 'lockbox_passkey_verified');

        Session::put($flag, [
            'timestamp' => now()->getTimestamp(),
            'passkey_id' => $event->passkey->getKey(),
        ]);
    }
}
