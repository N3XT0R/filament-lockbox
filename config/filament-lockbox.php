<?php

declare(strict_types=1);

use N3XT0R\FilamentLockbox\Managers\KeyMaterial\PasskeyKeyMaterialProvider;
use N3XT0R\FilamentLockbox\Managers\KeyMaterial\TotpKeyMaterialProvider;

return [
    'show_widget' => true, // set to false if you don't want the status widget auto-added

    /*
    |--------------------------------------------------------------------------
    | User Key Material Providers
    |--------------------------------------------------------------------------
    |
    | These classes are responsible for deriving the user-specific key material
    | that will be combined with the server-side key.
    |
    | You can add, remove or replace providers here to customize the
    | authentication/key-derivation process.
    |
    */

    'providers' => [
        TotpKeyMaterialProvider::class,
        PasskeyKeyMaterialProvider::class,
    ],

    'passkeys' => [
        'session_flag' => 'lockbox_passkey_verified',
        // optional TTL in seconds for requiring re-unlock after some time, implement as needed:
        'ttl' => 900,
    ],

    /*
    |--------------------------------------------------------------------------
    | User Model
    |--------------------------------------------------------------------------
    |
    | Here you may define which User model should be used with the Lockbox.
    | By default we use the application's configured authentication provider.
    |
    */

    'user_model' => config('auth.providers.users.model'),
];
