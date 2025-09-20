<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Events;

use Illuminate\Foundation\Auth\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use N3XT0R\FilamentLockbox\Models\Lockbox;
use N3XT0R\FilamentLockbox\Models\LockboxGrant;

/**
 * Event dispatched whenever a user unwraps a lockbox grant to access data.
 */
class LockboxAccessed
{
    use Dispatchable;
    use SerializesModels;

    public Lockbox $lockbox;

    public LockboxGrant $grant;

    public User $user;

    public function __construct(Lockbox $lockbox, LockboxGrant $grant, User $user)
    {
        $this->lockbox = $lockbox;
        $this->grant = $grant;
        $this->user = $user;
    }
}
