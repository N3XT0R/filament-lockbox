<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Tests\Stubs\Lockbox;

use Illuminate\Foundation\Auth\User as BaseUser;
use N3XT0R\FilamentLockbox\Tests\Stubs\Concerns\HasLockboxUserFactory;

class DummyNoLockboxKeysUser extends BaseUser
{
    use HasLockboxUserFactory;

    protected $guarded = [];

}
