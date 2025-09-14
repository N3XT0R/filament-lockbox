<?php


declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Listeners;

use Illuminate\Support\Facades\Session;
use Spatie\LaravelPasskeys\Events\PasskeyUsedToAuthenticateEvent;

/**
 * Sets a session flag after passkey authentication.
 *
 * @category Filament Security
 * @package  n3xt0r/filament-lockbox
 * @author   Ilya Beliaev
 * @license  MIT
 * @link     https://github.com/N3XT0R/filament-lockbox
 */
class SetLockboxPasskeyFlag
{
    /**
     * Store a flag indicating the session was authenticated via passkey.
     *
     * @param PasskeyUsedToAuthenticateEvent $event Authentication event
     *
     * @return void
     */
    public function handle(PasskeyUsedToAuthenticateEvent $event): void
    {
        $flag = config('filament-lockbox.passkeys.session_flag', 'lockbox_passkey_verified');

        Session::put($flag, [
            'timestamp' => now()->getTimestamp(),
            'passkey_id' => $event->passkey->getKey(),
        ]);
    }
}
