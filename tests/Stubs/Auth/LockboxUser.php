<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Tests\Stubs\Auth;

use Illuminate\Foundation\Auth\User as BaseUser;
use N3XT0R\FilamentLockbox\Concerns\InteractsWithLockbox;
use N3XT0R\FilamentLockbox\Concerns\InteractsWithLockboxKeys;
use N3XT0R\FilamentLockbox\Contracts\HasLockbox;
use N3XT0R\FilamentLockbox\Contracts\HasLockboxKeys;

class LockboxUser extends BaseUser implements HasLockboxKeys, HasLockbox
{
    use InteractsWithLockbox;
    use InteractsWithLockboxKeys;

    protected $guarded = [];
    protected $table = 'users';

}
