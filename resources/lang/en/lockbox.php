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
        'save_settings' => 'Save Lockbox Settings',
    ],
    'notifications' => [
        'not_supported' => 'This user model does not implement HasLockboxKeys.',
        'key_generated' => 'Lockbox key generated successfully.',
        'password_set' => 'Crypto password set successfully.',
        'settings_saved' => 'Lockbox settings updated.',
    ],
    'modal' => [
        'unlock_heading' => 'Unlock your Lockbox',
        'unlock_description' => 'Enter your crypto password or TOTP code to proceed.',
    ],
    'form' => [
        'crypto_password' => 'Crypto Password',
        'totp' => 'TOTP Code',
        'provider' => 'Key Provider',
        'unlock' => 'Unlock Lockbox',
    ],

    'decryption' => [
        'status' => [
            'not_supported' => 'This user model does not support Lockbox keys.',
            'input_required' => 'Unlock required to view this field.',
            'decrypt_failed' => 'Unable to decrypt value.',
        ],
    ],
];
