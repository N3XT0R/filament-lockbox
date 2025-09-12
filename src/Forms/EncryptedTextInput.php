<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Forms\Components;

use Filament\Forms\Components\TextInput;
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
            $encrypter = $manager->forUser(auth()->user(), request('lockbox_input'));

            return $encrypter->encryptString($state);
        });
    }
}
