<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Forms\Components;

use Filament\Forms\Components\TextInput;
use Illuminate\Auth\Authenticatable;
use Illuminate\Foundation\Auth\User;
use N3XT0R\FilamentLockbox\Contracts\HasLockboxKeys;
use N3XT0R\FilamentLockbox\Support\LockboxManager;
use RuntimeException;

/**
 * Text input that stores its value encrypted using a per-user key.
 * It masks hydrated values and encrypts on dehydration.
 *
 * @category Filament Security
 * @package  n3xt0r/filament-lockbox
 * @author   Ilya Beliaev
 * @license  MIT
 * @link     https://github.com/N3XT0R/filament-lockbox
 */
class EncryptedTextInput extends TextInput
{
    /**
     * Optional: allow setting the secret programmatically (e.g., via a custom modal).
     */
    protected ?string $lockboxInput = null;

    /**
     * Set the lockbox secret used for encryption.
     *
     * @param string $input Secret provided by the user
     *
     * @return static
     */
    public function setLockboxInput(string $input): static
    {
        $this->lockboxInput = $input;

        return $this;
    }

    /**
     * Configure component behavior for handling encrypted state.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Mask state when loading form
        $this->afterStateHydrated(function (EncryptedTextInput $component, $state): void {
            if (!empty($state)) {
                $component->state('••••••');
            }
        });

        // Encrypt state before saving to database
        $this->dehydrateStateUsing(function (?string $state): ?string {
            if ($state === null || $state === '') {
                return $state;
            }

            /** * @var Authenticatable&User $user */
            $user = auth()->user();

            if (!$user instanceof HasLockboxKeys) {
                throw new RuntimeException(sprintf(
                    'Model %s must implement %s to use EncryptedTextInput.',
                    $user ? $user::class : 'null',
                    HasLockboxKeys::class,
                ));
            }

            // Prefer a programmatically set value; otherwise use the request payload
            $input = $this->lockboxInput ?? (string)request('lockbox_input', '');

            if ($input === '') {
                // No secret provided – let the UI handle prompting (e.g., via UnlockLockboxAction)
                return null;
            }

            /** @var LockboxManager $manager */
            $manager = app(LockboxManager::class);
            $encrypter = $manager->forUser($user, $input);

            return $encrypter->encryptString($state);
        });
    }
}
