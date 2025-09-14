<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;
use N3XT0R\FilamentLockbox\Contracts\HasLockbox;

class LockboxService
{
    public function set(Model&HasLockbox $lockboxable, string $name, string $value, User $user): void
    {
        $encrypter = app(LockboxManager::class)->forUser($user);

        $lockboxable->lockbox()->updateOrCreate(
            ['name' => $name, 'user_id' => $user->getKey()],
            ['value' => $encrypter->encrypt($value)],
        );
    }

    public function get(Model&HasLockbox $lockboxable, string $name, User $user): ?string
    {
        /** @var \N3XT0R\FilamentLockbox\Models\Lockbox|null $record */
        $record = $lockboxable->lockbox()
            ->where('name', $name)
            ->where('user_id', $user->getKey())
            ->first();

        if ($record === null) {
            return null;
        }

        $encrypter = app(LockboxManager::class)->forUser($user);

        return $encrypter->decrypt($record->value);
    }
}
