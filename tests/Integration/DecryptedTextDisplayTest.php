<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Tests\Integration;

use Illuminate\Foundation\Auth\User as BaseUser;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\HtmlString;
use N3XT0R\FilamentLockbox\Concerns\InteractsWithLockbox;
use N3XT0R\FilamentLockbox\Contracts\HasLockbox;
use N3XT0R\FilamentLockbox\Contracts\HasLockboxKeys;
use N3XT0R\FilamentLockbox\Contracts\UserKeyMaterialProviderInterface;
use N3XT0R\FilamentLockbox\Forms\Components\DecryptedTextDisplay;
use N3XT0R\FilamentLockbox\Support\LockboxService;
use N3XT0R\FilamentLockbox\Support\UserKeyMaterialResolver;
use N3XT0R\FilamentLockbox\Tests\TestCase;

class DecryptedTextDisplayTest extends TestCase
{
    public function testDecryptedValueIsShownWhenSecretProvided(): void
    {
        $provider = new class () implements UserKeyMaterialProviderInterface {
            public function supports(BaseUser $user): bool
            {
                return true;
            }

            public function provide(BaseUser $user, ?string $input): string
            {
                return $input ?? '';
            }
        };

        app(UserKeyMaterialResolver::class)->registerProvider($provider);

        config(['app.key' => 'base64:' . base64_encode(random_bytes(32))]);

        $user = new class () extends BaseUser implements HasLockboxKeys, HasLockbox {
            use InteractsWithLockbox;
            protected $guarded = [];
            protected $table = 'users';
            public ?string $encryptedUserKey;
            public string $providerClass;

            public function __construct()
            {
                parent::__construct();
                $this->encryptedUserKey = Crypt::encryptString('server-key');
            }

            public function getEncryptedUserKey(): ?string
            {
                return $this->encryptedUserKey;
            }

            public function setEncryptedUserKey(string $value): void
            {
                $this->encryptedUserKey = $value;
            }

            public function getCryptoPasswordHash(): ?string
            {
                return null;
            }

            public function setCryptoPasswordHash(string $hash): void
            {
            }

            public function getLockboxProvider(): ?string
            {
                return $this->providerClass;
            }

            public function setLockboxProvider(string $provider): void
            {
                $this->providerClass = $provider;
            }

            public function initializeUserKeyIfMissing(): void
            {
            }

            public function setCryptoPassword(string $plainPassword): void
            {
            }
        };

        $user->providerClass = $provider::class;
        $user->forceFill([
            'name' => 'Test',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ])->save();

        $this->actingAs($user);

        $service = app(LockboxService::class);
        $service->set($user, 'secret', 'plain-value', $user, 'input-secret');

        $livewire = new class () extends \Livewire\Component implements \Filament\Schemas\Contracts\HasSchemas {
            use \Filament\Schemas\Concerns\InteractsWithSchemas;

            public function getSchemas(): array
            {
                return [];
            }
        };

        $component = DecryptedTextDisplay::make('secret')
            ->model($user)
            ->setLockboxInput('input-secret');
        $component->container(\Filament\Schemas\Schema::make($livewire));
        $hydrate = \invade($component)->afterStateHydrated;
        $hydrate($component);

        $state = $component->getState();
        $this->assertInstanceOf(HtmlString::class, $state);
        $this->assertSame('plain-value', (string) $state);
    }
}
