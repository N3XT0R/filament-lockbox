<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Forms\Actions;

use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;

/**
 * Opens a modal to collect the user's Lockbox secret (crypto password or TOTP).
 * The provided value is merged into the current request as "lockbox_input",
 * so any EncryptedTextInput can read it during dehydration.
 */
class UnlockLockboxAction extends Action
{
    /**
     * Provide a sane default name so developers can simply call `UnlockLockboxAction::make()`.
     */
    public static function getDefaultName(): ?string
    {
        return 'unlockLockbox';
    }

    /**
     * Configure the action: modal, form schema, and behavior.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('filament-lockbox::lockbox.buttons.unlock'))
            ->modalHeading(__('filament-lockbox::lockbox.modal.unlock_heading'))
            ->modalDescription(__('filament-lockbox::lockbox.modal.unlock_description'))
            ->form([
                TextInput::make('lockbox_input')
                    ->password()
                    ->label(__('filament-lockbox::lockbox.form.crypto_password'))
                    ->required()
                    ->revealable(),
            ])
            ->action(function (array $data): void {
                // Make the input available to the current request cycle
                // so form components can read it during dehydration.
                request()->merge([
                    'lockbox_input' => $data['lockbox_input'],
                ]);
            });
    }
}
