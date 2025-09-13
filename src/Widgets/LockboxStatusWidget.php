<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Widgets;

use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;
use N3XT0R\FilamentLockbox\Contracts\HasLockboxKeys;

class LockboxStatusWidget extends Widget
{
    protected string $view = 'filament-lockbox::widgets.lockbox-status-widget';

    #[Validate('required|min:8')]
    public ?string $cryptoPassword = null;

    public bool $supportsLockbox = false;

    public function mount(): void
    {
        $user = Auth::user();
        $this->supportsLockbox = $user instanceof HasLockboxKeys;
    }

    public function getFormSchema(): array
    {
        if (!$this->supportsLockbox) {
            return [];
        }

        return [
            TextInput::make('cryptoPassword')
                ->password()
                ->label(__('filament-lockbox::lockbox.form.crypto_password'))
                ->revealable()
                ->required()
                ->minLength(8),
        ];
    }

    public function generateKey(): void
    {
        if (!$this->supportsLockbox) {
            Notification::make()
                ->title(__('filament-lockbox::lockbox.notifications.not_supported'))
                ->danger()
                ->send();

            return;
        }

        /**
         * @var User&HasLockboxKeys $user
         */
        $user = Auth::user();
        $user->initializeUserKeyIfMissing();

        Notification::make()
            ->title(__('filament-lockbox::lockbox.notifications.key_generated'))
            ->success()
            ->send();

        $this->dispatch('$refresh');
    }

    public function savePassword(): void
    {
        if (!$this->supportsLockbox) {
            Notification::make()
                ->title(__('filament-lockbox::lockbox.notifications.not_supported'))
                ->danger()
                ->send();

            return;
        }

        $this->validate();

        /**
         * @var User&HasLockboxKeys $user
         */
        $user = Auth::user();
        $user->setCryptoPassword($this->cryptoPassword);

        Notification::make()
            ->title(__('filament-lockbox::lockbox.notifications.password_set'))
            ->success()
            ->send();

        $this->cryptoPassword = null;
    }
}
