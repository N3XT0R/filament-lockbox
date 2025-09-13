<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Forms\Components;

use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Auth\Authenticatable;
use Illuminate\Foundation\Auth\User;
use Livewire\Attributes\Modelable;
use N3XT0R\FilamentLockbox\Contracts\HasLockboxKeys;
use N3XT0R\FilamentLockbox\Support\LockboxManager;
use RuntimeException;

class EncryptedTextInput extends TextInput
{
    #[Modelable]
    public ?string $lockboxInput = null;

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
            if (empty($state)) {
                return $state;
            }

            /** * @var Authenticatable&User $user */
            $user = auth()->user();

            if (!$user instanceof HasLockboxKeys) {
                throw new RuntimeException(sprintf(
                    'Model %s must implement %s to use EncryptedTextInput.',
                    $user::class,
                    HasLockboxKeys::class,
                ));
            }

            if (empty($this->lockboxInput)) {
                Notification::make()
                    ->title(__('filament-lockbox::lockbox.notifications.input_required'))
                    ->danger()
                    ->send();

                return null;
            }

            /** @var LockboxManager $manager */
            $manager = app(LockboxManager::class);
            $encrypter = $manager->forUser($user, $this->lockboxInput);

            return $encrypter->encryptString($state);
        });

        // Add an action to request lockbox input before saving
        $this->extraAttributes(['x-data' => '{}']); // prepare Alpine context
    }

    /**
     * Optional helper to set lockbox input programmatically (e.g. from a modal).
     */
    public function setLockboxInput(string $input): static
    {
        $this->lockboxInput = $input;

        return $this;
    }
}
