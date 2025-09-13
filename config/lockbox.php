<?php

declare(strict_types=1);

use N3XT0R\FilamentLockbox\Support\KeyMaterial\TotpKeyMaterialProvider;

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
    ],
];
