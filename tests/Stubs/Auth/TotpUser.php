<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Tests\Stubs\Auth;

use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthentication;
use Illuminate\Foundation\Auth\User as BaseUser;

class TotpUser extends BaseUser implements HasAppAuthentication
{
    public ?string $appAuthenticationSecret = 'totp-secret';

    public function getAppAuthenticationSecret(): ?string
    {
        return $this->appAuthenticationSecret;
    }

    public function saveAppAuthenticationSecret(?string $secret): void
    {
        $this->appAuthenticationSecret = $secret;
    }

    public function getAppAuthenticationHolderName(): string
    {
        return 'Test User';
    }
}
