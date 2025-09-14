<?php
/**
 * Key material provider that validates a TOTP code from the user.
 */

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Support\KeyMaterial;

use Filament\Auth\MultiFactor\App\AppAuthentication;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthentication;
use Illuminate\Foundation\Auth\User;
use N3XT0R\FilamentLockbox\Contracts\UserKeyMaterialProviderInterface;
use RuntimeException;

/**
 * Generates key material using a time-based one-time password (TOTP).
 */
class TotpKeyMaterialProvider implements UserKeyMaterialProviderInterface
{
    /**
     * Determine if the user has TOTP authentication enabled.
     */
    public function supports(User $user): bool
    {
        return $user instanceof HasAppAuthentication && !empty($user->getAppAuthenticationSecret());
    }

    /**
     * Return key material after verifying the provided TOTP code.
     *
     * @throws RuntimeException when verification fails
     */
    public function provide(User $user, ?string $input): string
    {
        if (!$user instanceof HasAppAuthentication) {
            throw new RuntimeException(sprintf(
                'Model %s must implement %s to use TotpKeyMaterialProvider.',
                $user::class,
                HasAppAuthentication::class,
            ));
        }

        if ($input === null || $input === '') {
            throw new RuntimeException('TOTP input is required.');
        }

        /** @var AppAuthentication $appAuth */
        $appAuth = app(AppAuthentication::class);

        if (!$appAuth->verifyCode($input, $user->getAppAuthenticationSecret())) {
            throw new RuntimeException('Invalid TOTP code.');
        }

        return hash('sha256', $input . $user->getKey(), true);
    }
}
