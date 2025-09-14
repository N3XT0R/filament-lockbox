<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox;

use Filament\Contracts\Plugin;
use Filament\Panel;
use N3XT0R\FilamentLockbox\Widgets\LockboxStatusWidget;

/**
 * Filament plugin registering lockbox widgets.
 *
 * @category Filament Security
 * @package  n3xt0r/filament-lockbox
 * @author   Ilya Beliaev
 * @license  MIT
 * @link     https://github.com/N3XT0R/filament-lockbox
 */
class FilamentLockboxPlugin implements Plugin
{
    /**
     * Get the plugin identifier.
     *
     * @return string Plugin ID
     */
    public function getId(): string
    {
        return 'filament-lockbox';
    }

    /**
     * Register plugin resources on the given panel.
     *
     * @param Panel $panel Filament panel instance
     *
     * @return void
     */
    public function register(Panel $panel): void
    {
        if (config('filament-lockbox.show_widget', true)) {
            $panel->widgets([
                LockboxStatusWidget::class,
            ]);
        }
    }

    /**
     * Boot the plugin.
     *
     * @param Panel $panel Filament panel instance
     *
     * @return void
     */
    public function boot(Panel $panel): void
    {
        //
    }

    /**
     * Create a new plugin instance.
     *
     * @return static
     */
    public static function make(): static
    {
        return app(static::class);
    }

    /**
     * Retrieve the plugin from the Filament registry.
     *
     * @return static
     */
    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }
}
