<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Forms\Components;

use Filament\Forms\Components\Field;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\HtmlString;
use N3XT0R\FilamentLockbox\Contracts\HasLockbox;
use N3XT0R\FilamentLockbox\Contracts\HasLockboxKeys;
use N3XT0R\FilamentLockbox\Support\LockboxService;

/**
 * Displays decrypted value of a field stored with EncryptedTextInput.
 *
 * @category Filament Security
 * @package  n3xt0r/filament-lockbox
 * @author   Ilya Beliaev
 * @license  MIT
 * @link     https://github.com/N3XT0R/filament-lockbox
 */
class DecryptedTextDisplay extends Field
{
    protected string $view = 'filament-lockbox::components.decrypted-text-display';

    /**
     * Optional secret input (crypto password or TOTP) to unlock decryption.
     */
    protected ?string $lockboxInput = null;

    /**
     * Set the lockbox input used for decryption.
     *
     * @param string|null $input Secret provided by the user
     *
     * @return static
     */
    public function setLockboxInput(?string $input): static
    {
        $this->lockboxInput = $input;

        return $this;
    }

    /**
     * Configure component behavior for decrypting values.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->afterStateHydrated(function (DecryptedTextDisplay $component): void {
            $record = $component->getRecord();

            if (!$record instanceof HasLockbox) {
                $component->state(new HtmlString('<span class="text-gray-400">—</span>'));

                return;
            }

            /** @var Authenticatable&User|null $user */
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
                $service = app(LockboxService::class);
                $decrypted = $service->get($record, $component->getName(), $user, $input);

                if ($decrypted === null || $decrypted === '') {
                    $component->state(new HtmlString('<span class="text-gray-400">—</span>'));

                    return;
                }

                $component->state(new HtmlString(e($decrypted)));
            } catch (\Throwable $e) {
                $component->state(new HtmlString('<span class="text-red-600">' .
                    __('filament-lockbox::lockbox.decryption.status.decrypt_failed') .
                    '</span>'));
            }
        });
    }
}
