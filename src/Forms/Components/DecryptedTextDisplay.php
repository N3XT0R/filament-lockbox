<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Forms\Components;

use Filament\Forms\Components\Field;
use Illuminate\Auth\Authenticatable;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\HtmlString;
use N3XT0R\FilamentLockbox\Contracts\HasLockboxKeys;
use N3XT0R\FilamentLockbox\Support\LockboxManager;

/**
 * Displays decrypted value of a field stored with EncryptedTextInput.
 */
class DecryptedTextDisplay extends Field
{
    protected string $view = 'filament-lockbox::components.decrypted-text-display';

    /**
     * Optional secret input (crypto password or TOTP) to unlock decryption.
     */
    protected ?string $lockboxInput = null;

    public function setLockboxInput(?string $input): static
    {
        $this->lockboxInput = $input;

        return $this;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->afterStateHydrated(function (DecryptedTextDisplay $component, $state): void {
            if ($state === null || $state === '') {
                $component->state(new HtmlString('<span class="text-gray-400">—</span>'));

                return;
            }

            /** * @var Authenticatable&User $user */
            $user = auth()->user();

            if (!$user instanceof HasLockboxKeys) {
                $component->state(new HtmlString('<span class="text-red-600">' .
                    __('filament-lockbox::lockbox.decryption.status.not_supported') .
                    '</span>'));

                return;
            }

            $input = $this->lockboxInput ?? (string)request('lockbox_input', '');

            if ($input === '') {
                $component->state(new HtmlString('<span class="text-yellow-600">' .
                    __('filament-lockbox::lockbox.decryption.status.input_required') .
                    '</span>'));

                return;
            }

            try {
                /** @var LockboxManager $manager */
                $manager = app(LockboxManager::class);
                $encrypter = $manager->forUser($user, $input);

                $decrypted = $encrypter->decryptString($state);

                $component->state(new HtmlString(e($decrypted)));
            } catch (\Throwable $e) {
                $component->state(new HtmlString('<span class="text-red-600">' .
                    __('filament-lockbox::lockbox.decryption.status.decrypt_failed') .
                    '</span>'));
            }
        });
    }
}
