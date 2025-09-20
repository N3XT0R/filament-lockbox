<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Events;

use Illuminate\Foundation\Auth\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use N3XT0R\FilamentLockbox\Models\Lockbox;
use N3XT0R\FilamentLockbox\Models\LockboxGrant;

/**
 * Event dispatched after a lockbox grant has been created.
 */
class LockboxGrantCreated
{
    use Dispatchable;
    use SerializesModels;

    public Lockbox $lockbox;

    public LockboxGrant $grant;

    public User $actor;

    public function __construct(Lockbox $lockbox, LockboxGrant $grant, User $actor)
    {
        $this->lockbox = $lockbox;
        $this->grant = $grant;
        $this->actor = $actor;
    }
}
