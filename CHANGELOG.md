# Changelog

All notable changes to `filament-lockbox` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.0.0-alpha]

### Added

- Split-Key Encryption: Added `LockboxManager` with PartA (server key) + PartB (user input)
- Filament Components:
    - `EncryptedTextInput` – encrypts values before saving
    - `DecryptedTextDisplay` – decrypts values for display (requires unlock)
    - `UnlockLockboxAction` – modal for entering crypto password/TOTP
- Crypto Password Support: Per-user password hashing & PBKDF2 key derivation
- Passkey Support: Built-in integration with `spatie/laravel-passkeys`
- TOTP Support: Key material provider that validates Google Authenticator codes
- Model Integration: `HasLockboxKeys` interface & `InteractsWithLockboxKeys` trait
- Filament Plugin: `FilamentLockboxPlugin` with optional status widget
- Translation Support: Language file with customizable strings
- Configurable Providers: Ability to register custom key material resolvers
- LockboxService: Added `exists` helper and support for user-provided secrets
- Encrypted components now rely on `LockboxService` for encryption logic
- `EncryptedTextInput` and `DecryptedTextDisplay` use Laravel's auth contract
- `LockboxStatusWidget` resets input fields after saving settings
- Improve type handling in `Lockbox` model and `LockboxService`
- Correct `phpunit.xml` configuration and widget state tests
