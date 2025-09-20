<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Services;

use Illuminate\Foundation\Auth\User;
use N3XT0R\FilamentLockbox\Managers\LockboxManager;
use N3XT0R\FilamentLockbox\Models\LockboxGroup;
use N3XT0R\FilamentLockbox\Models\LockboxGroupUser;
use RuntimeException;
use Throwable;

/**
 * Resolve group keys for users without exposing plaintext to the server's APP_KEY.
 */
class GroupKeyResolver
{
    public function __construct(
        private readonly LockboxManager $lockboxManager,
    ) {
    }

    /**
     * ⚠️ Resolve the group key for a user by decrypting their wrapped copy without touching APP_KEY secrets.
     *
     * @throws RuntimeException If the wrap is missing or invalid
     */
    public function resolveGroupKeyForUser(LockboxGroup $group, User $user): string
    {
        $pivot = LockboxGroupUser::query()
            ->where('group_id', $group->getKey())
            ->where('user_id', $user->getKey())
            ->first();

        if ($pivot === null) {
            throw new RuntimeException('User does not belong to the requested lockbox group.');
        }

        $wrappedKey = $pivot->getAttribute('wrapped_group_key_for_user');
        if ($wrappedKey === null || $wrappedKey === '') {
            throw new RuntimeException('No wrapped group key available for user.');
        }

        $encrypter = $this->lockboxManager->forUser($user);

        try {
            return $encrypter->decrypt($wrappedKey);
        } catch (Throwable $exception) {
            throw new RuntimeException('Failed to decrypt wrapped group key for user.', 0, $exception);
        }
    }
}
