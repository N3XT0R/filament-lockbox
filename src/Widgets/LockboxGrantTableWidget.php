<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Widgets;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use N3XT0R\FilamentLockbox\Forms\Actions\RevokeLockboxGrantAction;
use N3XT0R\FilamentLockbox\Models\LockboxGrant;
use N3XT0R\FilamentLockbox\Models\LockboxGroup;

/**
 * Table widget listing lockbox grants with inline revocation support.
 */
class LockboxGrantTableWidget extends TableWidget
{
    public function table(Table $table): Table
    {
        return $table
            ->heading(__('filament-lockbox::lockbox.widgets.grants_heading'))
            ->query(function (): Builder {
                $user = Auth::user();

                if (!$user instanceof Model) {
                    return LockboxGrant::query()->whereRaw('1 = 0');
                }

                return LockboxGrant::query()
                    ->with(['lockbox', 'grantee'])
                    ->whereHas('lockbox', static function (Builder $query) use ($user): void {
                        $query->where('user_id', $user->getKey());
                    })
                    ->latest('created_at');
            })
            ->columns([
                TextColumn::make('lockbox.name')
                    ->label(__('filament-lockbox::lockbox.table.lockbox'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('grantee_label')
                    ->label(__('filament-lockbox::lockbox.table.grantee'))
                    ->state(fn (LockboxGrant $record): string => $this->formatGrantee($record))
                    ->wrap(),
                TextColumn::make('dek_version')
                    ->label(__('filament-lockbox::lockbox.table.dek_version'))
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('filament-lockbox::lockbox.table.created_at'))
                    ->since()
                    ->sortable(),
            ])
            ->recordActions([
                RevokeLockboxGrantAction::make()
                    ->visible(static fn (LockboxGrant $record): bool => $record->lockbox?->getAttribute('user_id') === Auth::id()),
            ])
            ->emptyStateHeading(__('filament-lockbox::lockbox.table.no_grants'));
    }

    protected function formatGrantee(LockboxGrant $grant): string
    {
        $grantee = $grant->grantee;

        if ($grantee instanceof LockboxGroup) {
            $name = $grantee->name ?? '#' . $grantee->getKey();

            return __('filament-lockbox::lockbox.table.grantee_group', ['name' => $name]);
        }

        if ($grantee instanceof Model) {
            $name = $grantee->getAttribute('name') ?? '#' . $grantee->getKey();

            return __('filament-lockbox::lockbox.table.grantee_user', ['name' => $name]);
        }

        return __('filament-lockbox::lockbox.table.unknown_grantee');
    }
}
