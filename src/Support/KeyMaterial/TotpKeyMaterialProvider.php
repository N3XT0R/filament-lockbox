<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Support\KeyMaterial;

use Filament\Auth\MultiFactor\App\AppAuthentication;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthentication;
use Illuminate\Foundation\Auth\User;
use N3XT0R\FilamentLockbox\Contracts\UserKeyMaterialProviderInterface;
use RuntimeException;

class TotpKeyMaterialProvider implements UserKeyMaterialProviderInterface
{
    public function supports(User $user): bool
    {
        return $user instanceof HasAppAuthentication && !empty($user->getAppAuthenticationSecret());
    }

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
