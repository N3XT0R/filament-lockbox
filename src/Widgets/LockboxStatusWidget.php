<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Widgets;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Auth;
use N3XT0R\FilamentLockbox\Contracts\HasLockboxKeys;
use N3XT0R\FilamentLockbox\Jobs\ReencryptLockboxData;
use N3XT0R\FilamentLockbox\Support\KeyMaterial\CryptoPasswordKeyMaterialProvider;
use N3XT0R\FilamentLockbox\Support\KeyMaterial\TotpKeyMaterialProvider;

/** @property view-string $view */
class LockboxStatusWidget extends Widget
{
    /**
     * @var view-string
     */
    protected string $view = 'filament-lockbox::widgets.lockbox-status-widget';

    public ?string $cryptoPassword = null;
    public ?string $totpCode = null;
    public ?string $provider = null;

    public bool $supportsLockbox = false;

    public function mount(): void
    {
        $user = Auth::user();
        $this->supportsLockbox = $user instanceof HasLockboxKeys;
        if ($this->supportsLockbox) {
            /** @var User&HasLockboxKeys $user */
            $this->provider = $user->getLockboxProvider();
        }
    }

    public function getFormSchema(): array
    {
        if (!$this->supportsLockbox) {
            return [];
        }

        return [
            Select::make('provider')
                ->label(__('filament-lockbox::lockbox.form.provider'))
                ->options($this->getProviderOptions())
                ->required(),
            TextInput::make('cryptoPassword')
                ->password()
                ->label(__('filament-lockbox::lockbox.form.crypto_password'))
                ->revealable()
                ->visible(fn () => $this->provider === CryptoPasswordKeyMaterialProvider::class)
                ->minLength(8),
            TextInput::make('totpCode')
                ->label(__('filament-lockbox::lockbox.form.totp'))
                ->visible(fn () => $this->provider === TotpKeyMaterialProvider::class),
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

    public function saveSettings(): void
    {
        if (!$this->supportsLockbox) {
            Notification::make()
                ->title(__('filament-lockbox::lockbox.notifications.not_supported'))
                ->danger()
                ->send();

            return;
        }

        $rules = ['provider' => 'required'];
        if ($this->provider === CryptoPasswordKeyMaterialProvider::class) {
            $rules['cryptoPassword'] = ['required', 'min:8'];
        }
        if ($this->provider === TotpKeyMaterialProvider::class) {
            $rules['totpCode'] = ['required'];
        }
        $this->validate($rules);

        /** @var User&HasLockboxKeys $user */
        $user = Auth::user();
        $oldProvider = $user->getLockboxProvider();

        $user->setLockboxProvider($this->provider);
        if ($this->provider === CryptoPasswordKeyMaterialProvider::class) {
            $user->setCryptoPassword($this->cryptoPassword);
        }

        if ($oldProvider !== null && $oldProvider !== $this->provider) {
            $this->dispatch(new ReencryptLockboxData($user, $oldProvider, $this->provider));
        }

        Notification::make()
            ->title(__('filament-lockbox::lockbox.notifications.settings_saved'))
            ->success()
            ->send();

        $this->cryptoPassword = null;
        $this->totpCode = null;
    }

    protected function getProviderOptions(): array
    {
        $providers = config('filament-lockbox.providers', []);
        $options = [];
        foreach ($providers as $class) {
            $options[$class] = class_basename($class);
        }

        return $options;
    }
}
