<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Facade for the Filament Lockbox service.
 *
 * @category Filament Security
 * @package  n3xt0r/filament-lockbox
 * @author   Ilya Beliaev
 * @license  MIT
 * @link     https://github.com/N3XT0R/filament-lockbox
 *
 * @see \N3XT0R\FilamentLockbox\FilamentLockbox
 */
class FilamentLockbox extends Facade
{
    /**
     * Get the underlying service class.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return \N3XT0R\FilamentLockbox\FilamentLockbox::class;
    }
}
