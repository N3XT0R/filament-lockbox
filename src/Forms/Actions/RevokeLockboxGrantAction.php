<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Forms\Actions;

use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Notifications\Notification;
use N3XT0R\FilamentLockbox\Models\LockboxGrant;
use N3XT0R\FilamentLockbox\Services\LockboxGrantService;

/**
 * Action to revoke a lockbox grant with optional DEK rotation.
 *
 * @category Filament Security
 * @package  n3xt0r/filament-lockbox
 */
class RevokeLockboxGrantAction extends Action
{
    /**
     * Provide a default name so consumers can simply call `make()`.
     */
    public static function getDefaultName(): ?string
    {
        return 'revokeLockboxGrant';
    }

    /**
     * Configure the modal, confirmation and execution logic.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('filament-lockbox::lockbox.buttons.revoke'))
            ->color('danger')
            ->modalHeading(__('filament-lockbox::lockbox.modal.revoke_heading'))
            ->modalDescription(__('filament-lockbox::lockbox.modal.revoke_description'))
            ->schema([
                Checkbox::make('rotate')
                    ->label(__('filament-lockbox::lockbox.form.rotate_dek'))
                    ->helperText(__('filament-lockbox::lockbox.form.rotate_dek_helper')),
            ])
            ->action(function (array $data, $record): void {
                if (!$record instanceof LockboxGrant) {
                    return;
                }

                /** @var LockboxGrantService $service */
                $service = app(LockboxGrantService::class);
                $service->revokeGrant($record, (bool) ($data['rotate'] ?? false));

                Notification::make()
                    ->title(__('filament-lockbox::lockbox.notifications.grant_revoked'))
                    ->success()
                    ->send();
            });
    }
}
