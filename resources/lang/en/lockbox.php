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
        'revoke' => 'Revoke',
    ],
    'notifications' => [
        'not_supported' => 'This user model does not implement HasLockboxKeys.',
        'key_generated' => 'Lockbox key generated successfully.',
        'password_set' => 'Crypto password set successfully.',
        'settings_saved' => 'Lockbox settings updated.',
        'grant_revoked' => 'Lockbox access revoked.',
    ],
    'modal' => [
        'unlock_heading' => 'Unlock your Lockbox',
        'unlock_description' => 'Enter your crypto password or TOTP code to proceed.',
        'share_heading' => 'Share Lockbox Entry',
        'share_description' => 'Select a user or group to share this secret with.',
        'revoke_heading' => 'Revoke Lockbox Access',
        'revoke_description' => 'Remove this grant to immediately revoke access. Optionally rotate the Data Encryption Key for remaining recipients.',
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
        'rotate_dek' => 'Rotate data encryption key',
        'rotate_dek_helper' => 'Rotating re-wraps the DEK for remaining grants to mitigate leaked access.',
    ],

    'decryption' => [
        'status' => [
            'not_supported' => 'This user model does not support Lockbox keys.',
            'input_required' => 'Unlock required to view this field.',
            'decrypt_failed' => 'Unable to decrypt value.',
        ],
        'shared_via_grant' => 'shared',
        'group_prefix' => 'Group: ',
        'unknown_user' => 'Unknown user',
    ],

    'widgets' => [
        'grants_heading' => 'Shared Lockbox Access',
        'audit_heading' => 'Lockbox Audit Log',
    ],

    'table' => [
        'lockbox' => 'Lockbox Item',
        'grantee' => 'Shared With',
        'dek_version' => 'DEK Version',
        'created_at' => 'Shared At',
        'no_grants' => 'No active grants yet.',
        'audit_event' => 'Event',
        'audit_actor' => 'Actor',
        'audit_time' => 'Timestamp',
        'audit_details' => 'Details',
        'audit_empty' => 'No audit entries recorded yet.',
        'grantee_user' => 'User: :name',
        'grantee_group' => 'Group: :name',
        'unknown_grantee' => 'Unknown recipient',
    ],

    'audit' => [
        'events' => [
            'share_user' => 'Shared with user',
            'share_group' => 'Shared with group',
            'revoke' => 'Grant revoked',
            'access' => 'Grant accessed',
        ],
        'unknown_actor' => 'System',
    ],
];
