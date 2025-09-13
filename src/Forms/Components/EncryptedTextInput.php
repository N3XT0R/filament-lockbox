<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Forms\Components;

use Filament\Forms\Components\TextInput;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Auth\User;
use N3XT0R\FilamentLockbox\Support\LockboxManager;

class EncryptedTextInput extends TextInput
{
    protected function setUp(): void
    {
        parent::setUp();

        // Mask state when loading form
        $this->afterStateHydrated(function (EncryptedTextInput $component, $state): void {
            if (!empty($state)) {
                $component->state('••••••');
            }
        });

        // Encrypt state before saving to database
        $this->dehydrateStateUsing(function (?string $state): ?string {
            if (empty($state)) {
                return $state;
            }

            /** @var LockboxManager $manager */
            $manager = app(LockboxManager::class);
            /**
             * @var Authenticatable&User $user
             */
            $user = auth()->user();
            $encrypter = $manager->forUser($user, request('lockbox_input'));

            return $encrypter->encryptString($state);
        });
    }
}
