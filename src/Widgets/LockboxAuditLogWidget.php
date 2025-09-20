<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Widgets;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use N3XT0R\FilamentLockbox\Models\LockboxAudit;

/**
 * Table widget exposing recent lockbox audit activity to dashboard users.
 */
class LockboxAuditLogWidget extends TableWidget
{
    public function table(Table $table): Table
    {
        return $table
            ->heading(__('filament-lockbox::lockbox.widgets.audit_heading'))
            ->query(function (): Builder {
                $user = Auth::user();

                if (!$user instanceof Model) {
                    return LockboxAudit::query()->whereRaw('1 = 0');
                }

                return LockboxAudit::query()
                    ->with(['lockbox', 'actor'])
                    ->whereHas('lockbox', static function (Builder $query) use ($user): void {
                        $query->where('user_id', $user->getKey());
                    })
                    ->latest('created_at');
            })
            ->columns([
                TextColumn::make('lockbox.name')
                    ->label(__('filament-lockbox::lockbox.table.lockbox'))
                    ->searchable()
                    ->wrap(),
                TextColumn::make('event')
                    ->label(__('filament-lockbox::lockbox.table.audit_event'))
                    ->badge()
                    ->color('warning')
                    ->formatStateUsing(fn (LockboxAudit $record): string => $this->formatEvent($record))
                    ->sortable(),
                TextColumn::make('actor.name')
                    ->label(__('filament-lockbox::lockbox.table.audit_actor'))
                    ->formatStateUsing(fn (LockboxAudit $record): string => $this->formatActor($record))
                    ->wrap(),
                TextColumn::make('created_at')
                    ->label(__('filament-lockbox::lockbox.table.audit_time'))
                    ->since()
                    ->sortable(),
                TextColumn::make('context')
                    ->label(__('filament-lockbox::lockbox.table.audit_details'))
                    ->formatStateUsing(fn (LockboxAudit $record): string => $this->formatContext($record))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrap()
                    ->limit(60),
            ])
            ->emptyStateHeading(__('filament-lockbox::lockbox.table.audit_empty'));
    }

    protected function formatEvent(LockboxAudit $audit): string
    {
        $key = 'filament-lockbox::lockbox.audit.events.' . $audit->getAttribute('event');
        $translated = __($key);

        return $translated !== $key ? $translated : Str::of($audit->getAttribute('event'))
            ->headline()
            ->toString();
    }

    protected function formatActor(LockboxAudit $audit): string
    {
        $actor = $audit->getRelationValue('actor');

        if ($actor instanceof Model) {
            $name = $actor->getAttribute('name');

            return $name !== null && $name !== ''
                ? (string) $name
                : '#' . $actor->getKey();
        }

        return __('filament-lockbox::lockbox.audit.unknown_actor');
    }

    protected function formatContext(LockboxAudit $audit): string
    {
        $context = $audit->getAttribute('context');

        if (!is_array($context) || $context === []) {
            return '';
        }

        return collect($context)
            ->map(static function ($value, string $key): string {
                if (is_bool($value)) {
                    $value = $value ? 'true' : 'false';
                } elseif (is_array($value)) {
                    $value = json_encode($value, JSON_THROW_ON_ERROR);
                }

                return sprintf('%s: %s', $key, $value);
            })
            ->implode(', ');
    }
}
