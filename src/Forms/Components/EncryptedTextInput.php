<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Forms\Components;

use Filament\Forms\Components\TextInput;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Auth\User;
use N3XT0R\FilamentLockbox\Contracts\HasLockbox;
use N3XT0R\FilamentLockbox\Contracts\HasLockboxKeys;
use N3XT0R\FilamentLockbox\Services\LockboxService;
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

        $this->dehydrated(false);

        // Load value from lockbox
        $this->afterStateHydrated(function (EncryptedTextInput $component): void {
            $record = $component->getRecord();

            if (!$record instanceof HasLockbox) {
                return;
            }

            /** @var (Authenticatable&User)|null $user */
            $user = auth()->user();

            $service = app(LockboxService::class);

            if (!$service->exists($record, $component->getName(), $user)) {
                return;
            }

            $input = $this->lockboxInput ?? (string)request('lockbox_input', '');

            if ($input === '') {
                $component->state('••••••');

                return;
            }

            try {
                $value = $service->get($record, $component->getName(), $user, $input);

                if ($value !== null) {
                    $component->state($value);
                }
            } catch (\Throwable $e) {
                $component->state('••••••');
            }
        });

        // Persist value to lockbox
        $this->saveRelationshipsUsing(function (EncryptedTextInput $component): void {
            $state = $component->getState();

            if ($state === null || $state === '' || $state === '••••••') {
                return;
            }

            $record = $component->getRecord();

            if (!$record instanceof HasLockbox) {
                return;
            }

            /** @var (Authenticatable&User)|null $user */
            $user = auth()->user();

            if (!$user instanceof HasLockboxKeys) {
                throw new RuntimeException(sprintf(
                    'Model %s must implement %s to use EncryptedTextInput.',
                    $user ? $user::class : 'null',
                    HasLockboxKeys::class,
                ));
            }

            $input = $this->lockboxInput ?? (string)request('lockbox_input', '');

            if ($input === '') {
                return;
            }

            app(LockboxService::class)->set($record, $component->getName(), $state, $user, $input);

            $component->state('••••••');
        });
    }
}
