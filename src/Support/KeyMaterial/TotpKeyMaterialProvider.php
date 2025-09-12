<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Support\KeyMaterial;

use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthentication;
use Illuminate\Foundation\Auth\User;
use N3XT0R\FilamentLockbox\Contracts\UserKeyMaterialProviderInterface;
use PragmaRX\Google2FA\Google2FA;
use RuntimeException;

class TotpKeyMaterialProvider implements UserKeyMaterialProviderInterface
{
    public function supports(User $user): bool
    {
        return $user instanceof HasAppAuthentication && !empty($user->getAppAuthenticationSecret());
    }

    public function provide(User $user, ?string $input): string
    {
        $google2fa = app(Google2FA::class);
        $secret = decrypt($user->getAppAuthenticationSecret());

        if (!$google2fa->verifyKey($secret, (string)$input)) {
            throw new RuntimeException('Invalid TOTP code.');
        }

        return hash('sha256', $input . $user->getKey(), true);
    }
}
