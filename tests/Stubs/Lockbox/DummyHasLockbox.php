<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Tests\Stubs\Lockbox;

use Illuminate\Foundation\Auth\User as BaseUser;
use N3XT0R\FilamentLockbox\Concerns\InteractsWithLockbox;
use N3XT0R\FilamentLockbox\Contracts\HasLockbox;
use N3XT0R\FilamentLockbox\Tests\Stubs\Concerns\HasLockboxUserFactory;

class DummyHasLockbox extends BaseUser implements HasLockbox
{
    use InteractsWithLockbox;
    use HasLockboxUserFactory;

    protected $guarded = [];
    protected $table = 'users';
}
