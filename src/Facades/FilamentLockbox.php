<?php

namespace N3XT0R\FilamentLockbox\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \N3XT0R\FilamentLockbox\FilamentLockbox
 */
class FilamentLockbox extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \N3XT0R\FilamentLockbox\FilamentLockbox::class;
    }
}
