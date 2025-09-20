<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Forms\Components;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Auth\User;
use N3XT0R\FilamentLockbox\Services\LockboxGrantService;

/**
 * Variant of EncryptedTextInput that reads its value via a user/group grant.
 * This component is read-only by default and does not persist new values.
 *
 * @category Filament Security
 * @package  n3xt0r/filament-lockbox
 */
class SharedEncryptedTextInput extends EncryptedTextInput
{
    /**
     * Indicates whether the value was successfully decrypted.
     */
    protected bool $isDecrypted = false;

    /**
     * Get whether the value was decrypted successfully.
     */
    public function isDecrypted(): bool
    {
        return $this->isDecrypted;
    }

    protected string $view = 'filament-lockbox::components.shared-encrypted-text-input';

    /**
     * Configure the component to override hydration/dehydration behavior.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Make this field read-only (shared values should not be overwritten)
        $this->disabled();
        $this->dehydrated(false);

        // Override afterStateHydrated defined in parent
        $this->afterStateHydrated(function (SharedEncryptedTextInput $component): void {
            $record = $component->getRecord();

            if (!is_object($record) || !method_exists($record, 'lockbox')) {
                return;
            }

            /** @var (Authenticatable&User)|null $user */
            $user = auth()->user();
            if (!$user instanceof User) {
                return;
            }

            $lockbox = $record->lockbox()
                ->where('name', $component->getName())
                ->first();

            if (!$lockbox) {
                $component->state('••••••');

                return;
            }

            try {
                /** @var LockboxGrantService $grantService */
                $grantService = app(LockboxGrantService::class);
                [$dek, $grant] = $grantService->resolveDekForUserWithGrant($lockbox, $user);

                if (!$dek) {
                    $component->state('••••••');

                    return;
                }

                $this->isDecrypted = true;
                $plaintext = decrypt($lockbox->value, $dek);

                $component->state($plaintext);
            } catch (\Throwable $e) {
                report($e);
                $component->state('••••••');
            }
        });

        // Remove saveRelationshipsUsing defined in parent
        $this->saveRelationshipsUsing(null);
    }
}
