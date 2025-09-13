<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Auth\User;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use N3XT0R\FilamentLockbox\Support\LockboxManager;

class ReencryptLockboxData implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public User $user,
        public string $oldProvider,
        public string $newProvider,
    ) {
    }

    public function handle(LockboxManager $manager): void
    {
        // Acquire encrypter instances for old and new providers
        $old = $manager->forUser($this->user, null, $this->oldProvider);
        $new = $manager->forUser($this->user, null, $this->newProvider);

        // The package does not manage user data storage directly.
        // Applications should extend this job to iterate their own
        // encrypted records and re-encrypt values using `$old` and `$new`.
    }
}
