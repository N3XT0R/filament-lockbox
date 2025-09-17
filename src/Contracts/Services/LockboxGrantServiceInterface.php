<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Contracts\Services;

use Illuminate\Foundation\Auth\User;
use N3XT0R\FilamentLockbox\Models\Lockbox;
use N3XT0R\FilamentLockbox\Models\LockboxGrant;
use N3XT0R\FilamentLockbox\Models\LockboxGroup;

interface LockboxGrantServiceInterface
{
    /**
     * Share a lockbox item with a specific user.
     *
     * @param Lockbox $lockbox Lockbox entry to share
     * @param User    $user    Recipient user
     */
    public function shareWithUser(Lockbox $lockbox, User $user): LockboxGrant;

    /**
     * Share a lockbox item with a group.
     *
     * @param Lockbox      $lockbox Lockbox entry to share
     * @param LockboxGroup $group   Recipient group
     */
    public function shareWithGroup(Lockbox $lockbox, LockboxGroup $group): LockboxGrant;

    /**
     * Resolve a usable DEK for a given user (direct grant or via group).
     *
     * @param Lockbox $lockbox Lockbox entry
     * @param User    $user    Accessing user
     *
     * @return string|null Plaintext DEK or null if no access
     */
    public function resolveDekForUser(Lockbox $lockbox, User $user): ?string;
}
