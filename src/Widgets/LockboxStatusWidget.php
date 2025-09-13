<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Widgets;

use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;
use N3XT0R\FilamentLockbox\Contracts\HasLockboxKeys;

class LockboxStatusWidget extends Widget
{
    protected string $view = 'filament-lockbox::widgets.lockbox-status-widget';

    #[Validate('required|min:12')]
    public ?string $cryptoPassword = null;

    public function getFormSchema(): array
    {
        return [
            TextInput::make('cryptoPassword')
                ->password()
                ->label('Crypto Password')
                ->revealable()
                ->required()
                ->minLength(12),
        ];
    }

    public function generateKey(): void
    {
        $user = Auth::user();
        if (!$user instanceof HasLockboxKeys) {
            return;
        }
        $user->initializeUserKeyIfMissing();

        Notification::make()
            ->title('Lockbox key generated')
            ->success()
            ->send();

        $this->dispatch('$refresh');

    }

    public function savePassword(): void
    {
        $this->validate();

        /** @var Model&HasLockboxKeys $user */
        $user = Auth::user();

        if (!$user instanceof HasLockboxKeys) {
            Notification::make()
                ->title('User model does not support lockbox keys.')
                ->danger()
                ->send();

            return;
        }

        $user->setCryptoPassword($this->cryptoPassword);

        Notification::make()
            ->title('Crypto password set')
            ->success()
            ->send();

        $this->cryptoPassword = null;
    }
}
