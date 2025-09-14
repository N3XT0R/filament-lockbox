<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Forms\Actions;

use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;

/**
 * Action to prompt the user for lockbox secret input.
 *
 * @category Filament Security
 * @package  n3xt0r/filament-lockbox
 * @author   Ilya Beliaev
 * @license  MIT
 * @link     https://github.com/N3XT0R/filament-lockbox
 */
class UnlockLockboxAction extends Action
{
    /**
     * Provide a sane default name so developers can simply call `make()`.
     *
     * @return string|null Default name
     */
    public static function getDefaultName(): ?string
    {
        return 'unlockLockbox';
    }

    /**
     * Configure the action: modal, form schema, and behavior.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('filament-lockbox::lockbox.buttons.unlock'))
            ->modalHeading(__('filament-lockbox::lockbox.modal.unlock_heading'))
            ->modalDescription(__('filament-lockbox::lockbox.modal.unlock_description'))
            ->schema([
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
