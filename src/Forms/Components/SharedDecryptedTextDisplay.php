<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Forms\Components;

use Illuminate\Foundation\Auth\User;
use Illuminate\Support\HtmlString;
use N3XT0R\FilamentLockbox\Models\LockboxGrant;
use N3XT0R\FilamentLockbox\Services\LockboxGrantService;

/**
 * Field component that extends DecryptedTextDisplay to also support
 * decrypting values via grants (shared access) and exposing the grant source.
 *
 * @category Filament Security
 * @package  n3xt0r/filament-lockbox
 */
class SharedDecryptedTextDisplay extends DecryptedTextDisplay
{
    /**
     * Indicates whether the value was decrypted using a grant.
     */
    protected bool $isShared = false;

    /**
     * Stores information about the source (user or group name).
     */
    protected ?string $sharedBy = null;

    /**
     * Returns whether the value was decrypted via a grant.
     */
    public function isShared(): bool
    {
        return $this->isShared;
    }

    /**
     * Returns the name of the user/group that shared this value.
     */
    public function sharedBy(): ?string
    {
        return $this->sharedBy;
    }

    /**
     * Configure component behavior to include grant-based decryption.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->afterStateHydrated(function (SharedDecryptedTextDisplay $component): void {
            if (!blank($component->getState())) {
                return;
            }

            $record = $component->getRecord();
            /**
             * @var User $user
             *
             */
            $user = auth()->user();

            try {
                $lockbox = $record->lockbox()
                    ->where('name', $component->getName())
                    ->first();

                if (!$lockbox) {
                    return;
                }

                /** @var LockboxGrantService $grantService */
                $grantService = app(LockboxGrantService::class);

                // get both DEK and grant model
                [$dek, $grant] = $grantService->resolveDekForUserWithGrant($lockbox, $user);

                if (!$dek) {
                    return;
                }

                $component->isShared = true;
                $component->sharedBy = $this->resolveSharedByName($grant);

                $plaintext = decrypt($lockbox->value, $dek);
                $component->state(new HtmlString(e($plaintext)));
            } catch (\Throwable $e) {
                report($e);

                $component->state(new HtmlString('<span class="text-red-600">' .
                    __('filament-lockbox::lockbox.decryption.status.decrypt_failed') .
                    '</span>'));
            }
        });
    }

    /**
     * Derive the name of the grant source (user or group).
     */
    protected function resolveSharedByName(?LockboxGrant $grant): ?string
    {
        if (!$grant) {
            return null;
        }

        $grantee = $grant->grantee;

        if (!$grantee) {
            return null;
        }

        return match (class_basename($grantee)) {
            'User' => $grantee->name ?? __('filament-lockbox::lockbox.decryption.unknown_user'),
            'LockboxGroup' => __('filament-lockbox::lockbox.decryption.group_prefix') . ($grantee->name ?? '—'),
            default => null,
        };
    }
}
