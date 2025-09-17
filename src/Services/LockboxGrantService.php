<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Services;

use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\DB;
use N3XT0R\FilamentLockbox\Contracts\Services\LockboxGrantServiceInterface;
use N3XT0R\FilamentLockbox\Managers\LockboxManager;
use N3XT0R\FilamentLockbox\Models\Lockbox;
use N3XT0R\FilamentLockbox\Models\LockboxGrant;
use N3XT0R\FilamentLockbox\Models\LockboxGroup;
use RuntimeException;

class LockboxGrantService implements LockboxGrantServiceInterface
{
    /**
     * Share a lockbox item with a specific user.
     *
     * @param Lockbox $lockbox Lockbox entry to share
     * @param User    $user    Recipient user
     */
    public function shareWithUser(Lockbox $lockbox, User $user): LockboxGrant
    {
        $dek = $this->decryptDekForOwner($lockbox);
        $wrappedDek = $this->wrapDekForUser($dek, $user);

        return LockboxGrant::create([
            'lockbox_id' => $lockbox->getKey(),
            'grantee_type' => $user->getMorphClass(),
            'grantee_id' => $user->getKey(),
            'wrapped_dek' => $wrappedDek,
        ]);
    }

    /**
     * Share a lockbox item with a group.
     *
     * @param Lockbox      $lockbox Lockbox entry to share
     * @param LockboxGroup $group   Recipient group
     */
    public function shareWithGroup(Lockbox $lockbox, LockboxGroup $group): LockboxGrant
    {
        $dek = $this->decryptDekForOwner($lockbox);
        $wrappedDek = $this->wrapDekForGroup($dek, $group);

        return LockboxGrant::create([
            'lockbox_id' => $lockbox->getKey(),
            'grantee_type' => $group->getMorphClass(),
            'grantee_id' => $group->getKey(),
            'wrapped_dek' => $wrappedDek,
        ]);
    }

    /**
     * Resolve a usable DEK for a given user (direct grant or via group).
     * Uses eager loading for groups to avoid N+1 queries.
     *
     * @param Lockbox $lockbox Lockbox entry
     * @param User    $user    Accessing user
     *
     * @return string|null Plaintext DEK or null if no access
     */
    public function resolveDekForUser(Lockbox $lockbox, User $user): ?string
    {
        // 1) Direct user grant
        $grant = $lockbox->grants()
            ->where('grantee_type', $user->getMorphClass())
            ->where('grantee_id', $user->getKey())
            ->first();

        if ($grant) {
            return $this->unwrapDekForUser($grant->getAttribute('wrapped_dek'), $user);
        }

        // 2) Group grants (eager loaded)
        $groupGrants = $lockbox->grants()
            ->where('grantee_type', (new LockboxGroup())->getMorphClass())
            ->get();

        if ($groupGrants->isEmpty()) {
            return null;
        }

        $groupIds = $groupGrants->pluck('grantee_id')->all();

        // Load all groups with members in one query
        $groups = LockboxGroup::with('members')
            ->whereIn('id', $groupIds)
            ->get()
            ->keyBy('id');

        foreach ($groupGrants as $groupGrant) {
            $group = $groups->get((int)$groupGrant->getAttribute('grantee_id'));
            if ($group && $group->members->contains($user)) {
                return $this->unwrapDekForGroup($groupGrant->getAttribute('wrapped_dek'), $group, $user);
            }
        }

        return null; // no access
    }

    /**
     * Return [plaintext DEK, LockboxGrant|null] for UI components that need source info.
     *
     * @return array{0: string|null, 1: LockboxGrant|null}
     */
    public function resolveDekForUserWithGrant(Lockbox $lockbox, User $user): array
    {
        $grant = $this->findGrantForUserOrGroups($lockbox, $user);
        if (!$grant) {
            return [null, null];
        }

        return [$this->unwrapFromGrant($grant, $user), $grant];
    }

    /**
     * Decrypt the DEK as owner (using encrypted_dek field).
     */
    private function decryptDekForOwner(Lockbox $lockbox): string
    {
        if (empty($lockbox->encrypted_dek)) {
            throw new RuntimeException('Lockbox entry has no encrypted DEK.');
        }

        $owner = $lockbox->user;
        $encrypter = app(LockboxManager::class)->forUser($owner);

        return $encrypter->decrypt($lockbox->encrypted_dek);
    }

    /**
     * Wrap the DEK for a user.
     */
    private function wrapDekForUser(string $dek, User $user): string
    {
        $encrypter = app(LockboxManager::class)->forUser($user);

        return $encrypter->encrypt($dek);
    }

    /**
     * ⚠️ Uses Laravel Crypt (APP_KEY) to encrypt the group DEK.
     * Consider migrating to per-user wrapped group keys for full zero-knowledge.
     */
    private function wrapDekForGroup(string $dek, LockboxGroup $group): string
    {
        $groupKey = decrypt($group->getAttribute('encrypted_group_key'));

        return encrypt($dek, $groupKey);
    }

    /**
     * Unwrap DEK from a user grant.
     */
    private function unwrapDekForUser(string $wrappedDek, User $user): string
    {
        $encrypter = app(LockboxManager::class)->forUser($user);

        return $encrypter->decrypt($wrappedDek);
    }

    /**
     * Unwrap DEK from a group grant.
     */
    private function unwrapDekForGroup(string $wrappedDek, LockboxGroup $group, User $user): string
    {
        $groupKeyWrapped = $group->getWrappedGroupKeyForUser($user);

        if (!$groupKeyWrapped) {
            throw new RuntimeException('User has no wrapped group key.');
        }

        $userEncrypter = app(LockboxManager::class)->forUser($user);
        $groupKey = $userEncrypter->decrypt($groupKeyWrapped);

        return decrypt($wrappedDek, $groupKey);
    }

    /**
     * Find the first applicable grant for the user (direct or via any of user's groups).
     */
    private function findGrantForUserOrGroups(Lockbox $lockbox, User $user): ?LockboxGrant
    {
        // Direct user grant
        /** @var LockboxGrant|null $direct */
        $direct = $lockbox->grants()
            ->where('grantee_type', $user->getMorphClass())
            ->where('grantee_id', $user->getKey())
            ->first();

        if ($direct) {
            return $direct;
        }

        // Group grants (any group the user is a member of)
        $groupType = (new LockboxGroup())->getMorphClass();

        $groupIds = DB::table('lockbox_group_user')
            ->where('user_id', $user->getKey())
            ->pluck('group_id');

        if ($groupIds->isEmpty()) {
            return null;
        }

        /** @var LockboxGrant|null $grant */
        $grant = $lockbox->grants()
            ->where('grantee_type', $groupType)
            ->whereIn('grantee_id', $groupIds)
            ->first();

        return $grant;
    }

    /**
     * Unwrap the DEK from a grant (handles user or group recipients).
     */
    private function unwrapFromGrant(LockboxGrant $grant, User $user): string
    {
        $userType = $user->getMorphClass();

        if ($grant->getAttribute('grantee_type') === $userType
            && (int)$grant->getAttribute('grantee_id') === (int)$user->getKey()) {
            return $this->unwrapDekForUser($grant->getAttribute('wrapped_dek'), $user);
        }

        $group = LockboxGroup::with('members')->findOrFail((int)$grant->getAttribute('grantee_id'));

        return $this->unwrapDekForGroup($grant->getAttribute('wrapped_dek'), $group, $user);
    }
}
