
# Copilot Instructions for filament-lockbox

## Project Overview

Filament Lockbox is a Laravel package for secure, user-based encryption of model fields in Filament v4. The architecture separates encrypted data from main tables and stores it centrally in a polymorphic `lockbox` table. Key generation uses a split-key approach: one part is stored server-side (encrypted), the other is provided by the user at runtime (password, passkey/WebAuthn, TOTP).

## Key Components & Architecture
- **src/Managers/LockboxManager.php**: Core logic for encryption, key management, and data handling.
- **src/Forms/Components/**: Filament components like `EncryptedTextInput` and `DecryptedTextDisplay` for encrypted input and display.
- **src/Forms/Actions/UnlockLockboxAction.php**: Action for unlocking the lockbox (user input for key material).
- **src/Models/Lockbox.php**: Eloquent model for the central lockbox table.
- **src/Concerns/InteractsWithLockbox(.php|Keys.php)**: Traits for models to integrate lockbox functionality.
- **src/Contracts/**: Interfaces for key material providers and model integration.
- **config/filament-lockbox.php**: Configuration for providers and features.

## Typical Workflows
- **Installation & Setup**:
  - `composer require n3xt0r/filament-lockbox`
  - `php artisan filament-lockbox:install` (assets & migration)
  - Optional: `php artisan vendor:publish --tag="filament-lockbox-config"`
- **Model Integration**:
  - User model: implements `HasLockboxKeys`, uses trait `InteractsWithLockboxKeys`, hides relevant fields.
  - Other models: implement `HasLockbox`, use trait `InteractsWithLockbox`.
- **Form Components**:
  - Replace `TextInput` with `EncryptedTextInput` for encrypted storage.
  - Display with `DecryptedTextDisplay`.
  - Use `UnlockLockboxAction` as extraAction for unlocking.
- **Tests**:
  - PHPUnit tests are under `tests/Unit/` and `tests/Integration/`.
  - Manager and resolver logic tested in `tests/Integration/Managers/` and `tests/Integration/Resolvers/`.

## Conventions & Special Features
- **Encrypted fields are NOT stored in the model**, only in the lockbox table.
- **Polymorphic relations**: Lockbox can be linked to any Eloquent model.
- **Key material providers**: Extendable via custom classes in `src/Managers/KeyMaterial/`.
- **Zero-knowledge**: Admins cannot decrypt data without user input.
- **Configurable providers**: Different key material providers can be enabled via config.

## External Dependencies
- [`spatie/laravel-passkeys`](https://github.com/spatie/laravel-passkeys) for WebAuthn/passkey support.
- Filament v4 as UI framework.

## Build, Test & Debug
- **Run tests**: `vendor/bin/phpunit`
- **Code quality**: PHPStan (`vendor/bin/phpstan analyse`), Pint (`vendor/bin/pint`)
- **Migrations**: `php artisan migrate`
- **Docker**: See `docker-compose.yml` and `app.Dockerfile` for container setup.

## Examples
- **Form component**:
  ```php
  EncryptedTextInput::make('secret_notes')
      ->label('Secret Notes');
  ```
- **Model setup**:
  ```php
  class User extends Authenticatable implements HasLockboxKeys {
      use InteractsWithLockboxKeys;
      // ...
  }
  ```

## Additional Notes
- Changes to key architecture or lockbox data model require migrations and trait/contract updates.
- The central lockbox table is the core for all encrypted data.

---

> Feedback on unclear or missing sections is welcome! Please request details or further examples as needed.
