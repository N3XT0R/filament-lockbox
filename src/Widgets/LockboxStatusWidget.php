<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Widgets;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Filament\Widgets\Widget;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Auth;
use N3XT0R\FilamentLockbox\Contracts\HasLockboxKeys;
use N3XT0R\FilamentLockbox\Jobs\ReencryptLockboxData;
use N3XT0R\FilamentLockbox\Managers\KeyMaterial\CryptoPasswordKeyMaterialProvider;
use N3XT0R\FilamentLockbox\Managers\KeyMaterial\TotpKeyMaterialProvider;

class LockboxStatusWidget extends Widget implements HasForms
{
    use InteractsWithForms;

    /**
     * The Blade view that renders this widget.
     *
     * @var view-string
     */
    protected string $view = 'filament-lockbox::widgets.lockbox-status-widget';

    // Livewire form properties
    public ?string $cryptoPassword = null;
    public ?string $totpCode = null;
    public ?string $provider = null;

    // Determines if the current user supports lockbox functionality
    public bool $supportsLockbox = false;

    public function mount(): void
    {
        $user = Auth::user();
        $this->supportsLockbox = $user instanceof HasLockboxKeys;

        if ($this->supportsLockbox) {
            /** @var User&HasLockboxKeys $user */
            $this->provider = $user->getLockboxProvider();
        }

        $this->resetState();
    }

    protected function resetState(): void
    {
        $this->cryptoPassword = null;
        $this->totpCode = null;
    }

    /**
     * Build the Filament form schema (v4.1+ uses Schema instead of Form).
     */
    public function form(Schema $schema): Schema
    {
        return $schema->schema([
            ...($this->supportsLockbox ? [
                Select::make('provider')
                    ->label(__('filament-lockbox::lockbox.form.provider'))
                    ->options($this->getProviderOptions())
                    ->required()
                    ->live(),

                TextInput::make('cryptoPassword')
                    ->password()
                    ->label(__('filament-lockbox::lockbox.form.crypto_password'))
                    ->revealable()
                    ->minLength(8)
                    ->visible(fn ($get) => $get('provider') === CryptoPasswordKeyMaterialProvider::class),

                TextInput::make('totpCode')
                    ->label(__('filament-lockbox::lockbox.form.totp'))
                    ->visible(fn ($get) => $get('provider') === TotpKeyMaterialProvider::class),
            ] : []),
        ]);
    }

    /**
     * Generate a new user key if missing.
     */
    public function generateKey(): void
    {
        if (!$this->supportsLockbox) {
            Notification::make()
                ->title(__('filament-lockbox::lockbox.notifications.not_supported'))
                ->danger()
                ->send();

            return;
        }

        /** @var User&HasLockboxKeys $user */
        $user = Auth::user();
        $user->initializeUserKeyIfMissing();

        Notification::make()
            ->title(__('filament-lockbox::lockbox.notifications.key_generated'))
            ->success()
            ->send();

        $this->dispatch('$refresh');
    }

    /**
     * Persist the selected provider and credentials for the current user.
     */
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

        $this->resetState();
    }

    /**
     * Return provider options from config as [class => label].
     */
    protected function getProviderOptions(): array
    {
        $providers = config('filament-lockbox.providers', []);
        $providers[] = CryptoPasswordKeyMaterialProvider::class;

        return collect($providers)
            ->mapWithKeys(fn (string $class) => [$class => class_basename($class)])
            ->toArray();
    }
}
