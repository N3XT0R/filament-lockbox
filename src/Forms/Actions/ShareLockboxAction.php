<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Forms\Actions;

use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Collection;
use N3XT0R\FilamentLockbox\Models\Lockbox;
use N3XT0R\FilamentLockbox\Models\LockboxGroup;
use N3XT0R\FilamentLockbox\Services\LockboxGrantService;

/**
 * Action to share a lockbox entry with another user or group.
 *
 * @category Filament Security
 * @package  n3xt0r/filament-lockbox
 */
class ShareLockboxAction extends Action
{
    /**
     * Default name so developers can call `ShareLockboxAction::make()`.
     */
    public static function getDefaultName(): ?string
    {
        return 'shareLockbox';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('filament-lockbox::lockbox.buttons.share'))
            ->modalHeading(__('filament-lockbox::lockbox.modal.share_heading'))
            ->modalDescription(__('filament-lockbox::lockbox.modal.share_description'))
            ->schema([
                Select::make('type')
                    ->label(__('filament-lockbox::lockbox.form.share_type'))
                    ->options([
                        'user' => __('filament-lockbox::lockbox.form.share_with_user'),
                        'group' => __('filament-lockbox::lockbox.form.share_with_group'),
                    ])
                    ->reactive()
                    ->required(),

                Select::make('recipient_id')
                    ->label(__('filament-lockbox::lockbox.form.recipient'))
                    ->options(
                        fn (callable $get): Collection => $get('type') === 'group'
                            ? LockboxGroup::query()->pluck('name', 'id')
                            : User::query()->pluck('name', 'id'),
                    )
                    ->searchable()
                    ->required(),
            ])
            ->action(function (array $data, $record): void {
                if (!$record instanceof Lockbox) {
                    return;
                }

                /** @var LockboxGrantService $service */
                $service = app(LockboxGrantService::class);

                if ($data['type'] === 'group') {
                    $group = LockboxGroup::findOrFail($data['recipient_id']);
                    $service->shareWithGroup($record, $group);
                } else {
                    $user = User::findOrFail($data['recipient_id']);
                    $service->shareWithUser($record, $user);
                }
            });
    }
}
