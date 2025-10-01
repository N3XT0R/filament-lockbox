# Changelog

All notable changes to `filament-lockbox` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- **Integration Tests**:  
  Added full coverage for `DecryptedTextDisplay` including cases for:
    - showing decrypted value when input provided
    - warning message when input is missing
    - dash display when record does not implement `HasLockbox`
    - error when user does not implement `HasLockboxKeys`
    - dash display when decrypted value is empty or missing

- **Model Factories**:  
  Introduced factories for `Lockbox`, `LockboxUser`, and stub user models to simplify test setup.  
  Added a reusable trait to declare `newFactory` for all test user model variations.

### Fixed

- **Config Overriding**:  
  `filament-lockbox.user_model` can now be overridden in `config/filament-lockbox.php` instead of being hardcoded.  
  This allows custom user model configuration in consuming applications.

## [1.0.0-alpha.1] - 2025-09-28

### Fixed

- **TOTP Key Material**:  
  Updated `TotpKeyMaterialProvider` to derive key material from the stored app authentication secret concatenated with
  the user ID, instead of using the raw TOTP code.  
  This ensures stronger and stable key material while still verifying the user’s TOTP input.

### Added

- **Unit & Integration Tests**:
    - Added unit tests for `TotpKeyMaterialProvider` to verify support detection and correct key derivation.
    - Added integration tests for `UserKeyMaterialResolver` with TOTP verification.
    - Added unit tests for `PasskeyKeyMaterialProvider` with session-based passkey resolution.

## [1.0.0-alpha] - 2025-09-20

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
