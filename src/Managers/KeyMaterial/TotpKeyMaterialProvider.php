<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Managers\KeyMaterial;

use Filament\Auth\MultiFactor\App\AppAuthentication;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthentication;
use Illuminate\Foundation\Auth\User;
use N3XT0R\FilamentLockbox\Contracts\UserKeyMaterialProviderInterface;
use RuntimeException;

/**
 * Generates key material using a time-based one-time password.
 *
 * @category Filament Security
 * @package  n3xt0r/filament-lockbox
 * @author   Ilya Beliaev
 * @license  MIT
 * @link     https://github.com/N3XT0R/filament-lockbox
 */
class TotpKeyMaterialProvider implements UserKeyMaterialProviderInterface
{
    /**
     * Determine if the user has TOTP authentication enabled.
     *
     * @param User $user User to check
     *
     * @return bool True if TOTP is configured, false otherwise
     */
    public function supports(User $user): bool
    {
        return $user instanceof HasAppAuthentication
            && !empty($user->getAppAuthenticationSecret());
    }

    /**
     * Verify the TOTP code and return derived key material.
     *
     * @param User        $user  User attempting authentication
     * @param string|null $input TOTP code input
     *
     *
     * @throws RuntimeException When verification fails
     * @return string           Derived key material
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
