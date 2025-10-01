<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Tests\Stubs\Auth;

use Illuminate\Foundation\Auth\User as BaseUser;
use N3XT0R\FilamentLockbox\Tests\Stubs\Concerns\HasLockboxUserFactory;

/**
 * Dummy user without HasLockboxKeys interface.
 */
class UserWithoutLockboxKeys extends BaseUser
{
    use HasLockboxUserFactory;

    protected $table = 'users';
}
