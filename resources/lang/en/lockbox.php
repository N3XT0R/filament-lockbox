<?php

declare(strict_types=1);

// translations for N3XT0R/FilamentLockbox
return [
    'status' => [
        'not_supported' => 'This user model does not support Lockbox keys.',
        'initialized' => 'Your Lockbox is initialized.',
        'missing' => 'No Lockbox key found for your account.',
    ],
    'buttons' => [
        'generate_key' => 'Generate Lockbox Key',
        'set_password' => 'Set Crypto Password',
    ],
    'notifications' => [
        'not_supported' => 'This user model does not implement HasLockboxKeys.',
        'key_generated' => 'Lockbox key generated successfully.',
        'password_set' => 'Crypto password set successfully.',
    ],
    'modal' => [
        'unlock_heading' => 'Unlock your Lockbox',
        'unlock_description' => 'Enter your crypto password or TOTP code to proceed.',
    ],
    'form' => [
        'crypto_password' => 'Crypto Password',
        'unlock' => 'Unlock Lockbox',
    ],
];
