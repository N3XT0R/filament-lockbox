<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Services;

use function base64_decode;
use function base64_encode;
use function config;
use function event;

use Illuminate\Encryption\Encrypter;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use function is_string;

use N3XT0R\FilamentLockbox\Contracts\Services\LockboxGrantServiceInterface;
use N3XT0R\FilamentLockbox\Events\LockboxAccessed;
use N3XT0R\FilamentLockbox\Events\LockboxGrantCreated;
use N3XT0R\FilamentLockbox\Events\LockboxGrantRevoked;
use N3XT0R\FilamentLockbox\Managers\LockboxManager;
use N3XT0R\FilamentLockbox\Models\Lockbox;
use N3XT0R\FilamentLockbox\Models\LockboxAudit;
use N3XT0R\FilamentLockbox\Models\LockboxGrant;
use N3XT0R\FilamentLockbox\Models\LockboxGroup;

use function now;
use function random_bytes;

use RuntimeException;

use function str_starts_with;
use function strlen;
use function trim;

class LockboxGrantService implements LockboxGrantServiceInterface
{
    public function __construct(
        private readonly LockboxManager $lockboxManager,
        private readonly GroupKeyResolver $groupKeyResolver,
    ) {
    }

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
        $actor = $this->resolveActor($lockbox->user);

        $grant = LockboxGrant::create([
            'lockbox_id' => $lockbox->getKey(),
            'grantee_type' => $user->getMorphClass(),
            'grantee_id' => $user->getKey(),
            'wrapped_dek' => $wrappedDek,
            'dek_version' => $this->resolveDekVersion($lockbox),
        ]);

        $this->recordAudit('share_user', $lockbox, $actor, $grant, [
            'recipient_id' => $user->getKey(),
        ]);

        event(new LockboxGrantCreated($lockbox, $grant, $actor));

        return $grant;
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
        $actor = $this->resolveActor($lockbox->user);
        $wrappedDek = $this->wrapDekForGroup($dek, $group, $actor);

        $grant = LockboxGrant::create([
            'lockbox_id' => $lockbox->getKey(),
            'grantee_type' => $group->getMorphClass(),
            'grantee_id' => $group->getKey(),
            'wrapped_dek' => $wrappedDek,
            'dek_version' => $this->resolveDekVersion($lockbox),
        ]);

        $this->recordAudit('share_group', $lockbox, $actor, $grant, [
            'group_id' => $group->getKey(),
        ]);

        event(new LockboxGrantCreated($lockbox, $grant, $actor));

        return $grant;
    }

    /**
     * ⚠️ Revoke an existing grant and optionally rotate the lockbox DEK for all remaining recipients.
     *
     * @param LockboxGrant $grant     Grant instance to revoke
     * @param bool         $rotateDek Whether the lockbox DEK should be rotated post-revocation
     */
    public function revokeGrant(LockboxGrant $grant, bool $rotateDek = false): void
    {
        $grant->loadMissing('lockbox.user');

        $lockbox = $grant->lockbox;
        if (!$lockbox instanceof Lockbox) {
            throw new RuntimeException('Cannot revoke a grant without an associated lockbox.');
        }

        $owner = $lockbox->user;
        if (!$owner instanceof User) {
            throw new RuntimeException('Lockbox is missing its owner user relation.');
        }

        $actor = $this->resolveActor($owner);
        $context = [
            'grantee_type' => $grant->getAttribute('grantee_type'),
            'grantee_id' => $grant->getAttribute('grantee_id'),
            'revoked_at' => now()->toIso8601String(),
        ];

        DB::transaction(function () use ($grant, $lockbox, $actor, $context, $rotateDek): void {
            $grant->setAttribute('_lockbox_audit_recorded', true);
            $grant->delete();

            $this->recordAudit('revoke', $lockbox, $actor, $grant, $context);

            if ($rotateDek) {
                $this->rotateDekForRemainingGrants($lockbox, $actor);
            }

            event(new LockboxGrantRevoked($lockbox, $grant, $actor));
        });
    }

    /**
     * ⚠️ Resolve a usable DEK for a given user via a single optimized query to avoid N+1 issues.
     *
     * @param Lockbox $lockbox Lockbox entry
     * @param User    $user    Accessing user
     *
     * @return string|null Plaintext DEK or null if no access
     */
    public function resolveDekForUser(Lockbox $lockbox, User $user): ?string
    {
        [$dek] = $this->resolveDekForUserWithGrant($lockbox, $user);

        return $dek;
    }

    /**
     * ⚠️ Return [plaintext DEK, LockboxGrant|null] for UI components and emit an access audit.
     *
     * @return array{0: string|null, 1: LockboxGrant|null}
     */
    public function resolveDekForUserWithGrant(Lockbox $lockbox, User $user): array
    {
        $grant = $this->findGrantForUserOrGroups($lockbox, $user);
        if (!$grant) {
            return [null, null];
        }

        $dek = $this->unwrapFromGrant($grant, $user);

        $this->recordAudit('access', $lockbox, $user, $grant, [
            'grantee_type' => $grant->getAttribute('grantee_type'),
            'grantee_id' => $grant->getAttribute('grantee_id'),
            'accessed_at' => now()->toIso8601String(),
        ]);

        event(new LockboxAccessed($lockbox, $grant, $user));

        return [$dek, $grant];
    }

    /**
     * ⚠️ Decrypt the DEK using the owner's lockbox key material only.
     */
    private function decryptDekForOwner(Lockbox $lockbox): string
    {
        if (empty($lockbox->encrypted_dek)) {
            throw new RuntimeException('Lockbox entry has no encrypted DEK.');
        }

        $owner = $lockbox->user;
        $encrypter = $this->lockboxManager->forUser($owner);

        return $encrypter->decrypt($lockbox->encrypted_dek);
    }

    /**
     * Wrap the DEK for a user.
     */
    private function wrapDekForUser(string $dek, User $user): string
    {
        $encrypter = $this->lockboxManager->forUser($user);

        return $encrypter->encrypt($dek);
    }

    /**
     * ⚠️ Wrap the DEK with a group key resolved via the actor's user key to preserve zero-knowledge.
     */
    private function wrapDekForGroup(string $dek, LockboxGroup $group, User $actor): string
    {
        $groupKey = $this->groupKeyResolver->resolveGroupKeyForUser($group, $actor);
        $encrypter = $this->buildGroupEncrypter($groupKey);

        return $encrypter->encrypt($dek);
    }

    /**
     * Unwrap DEK from a user grant.
     */
    private function unwrapDekForUser(string $wrappedDek, User $user): string
    {
        $encrypter = $this->lockboxManager->forUser($user);

        return $encrypter->decrypt($wrappedDek);
    }

    /**
     * ⚠️ Unwrap DEK from a group grant using the caller's wrapped group key – never via APP_KEY.
     */
    private function unwrapDekForGroup(string $wrappedDek, LockboxGroup $group, User $user): string
    {
        $groupKey = $this->groupKeyResolver->resolveGroupKeyForUser($group, $user);
        $encrypter = $this->buildGroupEncrypter($groupKey);

        return $encrypter->decrypt($wrappedDek);
    }

    /**
     * Find the first applicable grant for the user (direct or via any of user's groups).
     */
    private function findGrantForUserOrGroups(Lockbox $lockbox, User $user): ?LockboxGrant
    {
        $userType = $user->getMorphClass();
        $groupType = (new LockboxGroup())->getMorphClass();

        return LockboxGrant::query()
            ->where('lockbox_id', $lockbox->getKey())
            ->where('dek_version', $this->resolveDekVersion($lockbox))
            ->where(function ($query) use ($user, $userType, $groupType): void {
                $query->where(static function ($subQuery) use ($user, $userType): void {
                    $subQuery->where('grantee_type', $userType)
                        ->where('grantee_id', $user->getKey());
                })->orWhere(static function ($subQuery) use ($user, $groupType): void {
                    $subQuery->where('grantee_type', $groupType)
                        ->whereExists(static function ($exists) use ($user): void {
                            $exists->from('lockbox_group_user')
                                ->whereColumn('lockbox_group_user.group_id', 'lockbox_grants.grantee_id')
                                ->where('lockbox_group_user.user_id', $user->getKey());
                        });
                });
            })
            ->first();
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

        $group = LockboxGroup::findOrFail((int)$grant->getAttribute('grantee_id'));

        return $this->unwrapDekForGroup($grant->getAttribute('wrapped_dek'), $group, $user);
    }

    /**
     * ⚠️ Build an encrypter instance using the resolved group key material.
     */
    private function buildGroupEncrypter(string $groupKey): Encrypter
    {
        $normalized = trim($groupKey);
        $decoded = $normalized;

        if (str_starts_with($normalized, 'base64:')) {
            $decoded = base64_decode(substr($normalized, 7), true);
        }

        if (!is_string($decoded) || strlen($decoded) === 0) {
            throw new RuntimeException('Invalid group key payload.');
        }

        if (strlen($decoded) !== 32) {
            throw new RuntimeException('Group key material must be exactly 32 bytes for AES-256 operations.');
        }

        return new Encrypter($decoded, config('app.cipher'));
    }

    /**
     * ⚠️ Generate a new DEK and propagate updated wraps to all remaining grantees.
     *
     * @param Lockbox $lockbox Lockbox whose DEK should be rotated
     * @param User    $actor   Actor supplying the group key material during rotation
     */
    private function rotateDekForRemainingGrants(Lockbox $lockbox, User $actor): void
    {
        $owner = $lockbox->user;
        if (!$owner instanceof User) {
            throw new RuntimeException('Lockbox is missing its owner user relation.');
        }

        $newDek = base64_encode(random_bytes(32));
        $ownerEncrypter = $this->lockboxManager->forUser($owner);
        $encryptedDek = $ownerEncrypter->encrypt($newDek);
        $newVersion = $this->resolveDekVersion($lockbox) + 1;

        $lockbox->forceFill([
            'encrypted_dek' => $encryptedDek,
            'dek_version' => $newVersion,
        ]);
        $lockbox->save();

        $groupType = (new LockboxGroup())->getMorphClass();
        $remainingGrants = LockboxGrant::query()
            ->where('lockbox_id', $lockbox->getKey())
            ->with('grantee')
            ->get();

        foreach ($remainingGrants as $remainingGrant) {
            $grantee = $remainingGrant->getRelationValue('grantee');

            if ($remainingGrant->getAttribute('grantee_type') === $groupType && $grantee instanceof LockboxGroup) {
                $wrappedDek = $this->wrapDekForGroup($newDek, $grantee, $actor);
            } elseif ($grantee instanceof User) {
                $wrappedDek = $this->wrapDekForUser($newDek, $grantee);
            } else {
                continue;
            }

            $remainingGrant->forceFill([
                'wrapped_dek' => $wrappedDek,
                'dek_version' => $newVersion,
            ]);
            $remainingGrant->save();
        }
    }

    /**
     * Resolve the dek version stored on the lockbox.
     */
    private function resolveDekVersion(Lockbox $lockbox): int
    {
        return (int)($lockbox->getAttribute('dek_version') ?? 1);
    }

    /**
     * Record an audit event for lockbox operations.
     */
    private function recordAudit(
        string $event,
        Lockbox $lockbox,
        ?User $actor = null,
        ?LockboxGrant $grant = null,
        array $context = [],
    ): void {
        LockboxAudit::record($event, $lockbox, $actor, $grant, $context);
    }

    /**
     * Resolve the acting user, preferring the authenticated identity.
     *
     * ⚠️ The actor must possess a lockbox key to unwrap group secrets.
     */
    private function resolveActor(?User $fallback = null): User
    {
        $actor = Auth::user();
        if ($actor instanceof User) {
            return $actor;
        }

        if ($fallback instanceof User) {
            return $fallback;
        }

        throw new RuntimeException('Unable to determine acting user for lockbox operation.');
    }
}
