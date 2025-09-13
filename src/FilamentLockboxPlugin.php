<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox;

use Filament\Contracts\Plugin;
use Filament\Panel;
use N3XT0R\FilamentLockbox\Widgets\LockboxStatusWidget;

class FilamentLockboxPlugin implements Plugin
{
    public function getId(): string
    {
        return 'filament-lockbox';
    }

    public function register(Panel $panel): void
    {
        if (config('filament-lockbox.show_widget', true)) {
            $panel->widgets([
                LockboxStatusWidget::class,
            ]);
        }
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }
}
