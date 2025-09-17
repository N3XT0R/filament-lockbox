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
        'share' => 'Share',
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
        'share_heading' => 'Share Lockbox Entry',
        'share_description' => 'Select a user or group to share this secret with.',
    ],
    'form' => [
        'crypto_password' => 'Crypto Password',
        'totp' => 'TOTP Code',
        'provider' => 'Key Provider',
        'unlock' => 'Unlock Lockbox',
        'share_type' => 'Share with',
        'share_with_user' => 'User',
        'share_with_group' => 'Group',
        'recipient' => 'Recipient',
    ],

    'decryption' => [
        'status' => [
            'not_supported' => 'This user model does not support Lockbox keys.',
            'input_required' => 'Unlock required to view this field.',
            'decrypt_failed' => 'Unable to decrypt value.',
        ],
        'shared_via_grant' => 'shared',
    ],
];
