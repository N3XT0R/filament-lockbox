<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Tests\Integration\Forms\Components;

use Illuminate\Foundation\Auth\User as BaseUser;
use Illuminate\Support\Facades\Crypt;
use N3XT0R\FilamentLockbox\Concerns\InteractsWithLockbox;
use N3XT0R\FilamentLockbox\Contracts\HasLockbox;
use N3XT0R\FilamentLockbox\Contracts\HasLockboxKeys;
use N3XT0R\FilamentLockbox\Contracts\UserKeyMaterialProviderInterface;
use N3XT0R\FilamentLockbox\Forms\Components\EncryptedTextInput;
use N3XT0R\FilamentLockbox\Resolvers\UserKeyMaterialResolver;
use N3XT0R\FilamentLockbox\Services\LockboxService;
use N3XT0R\FilamentLockbox\Tests\TestCase;

class EncryptedTextInputTest extends TestCase
{
    public function testPersistsAndRetrievesViaLockboxService(): void
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

        $livewire = new class () extends \Livewire\Component implements \Filament\Schemas\Contracts\HasSchemas {
            use \Filament\Schemas\Concerns\InteractsWithSchemas;

            public function getSchemas(): array
            {
                return [];
            }
        };

        $component = EncryptedTextInput::make('secret')
            ->model($user)
            ->setLockboxInput('input-secret');
        $component->container(\Filament\Schemas\Schema::make($livewire));
        $component->state('plain-value');
        $save = \invade($component)->saveRelationshipsUsing;
        $save($component);

        $service = app(LockboxService::class);
        $retrieved = $service->get($user, 'secret', $user, 'input-secret');
        $this->assertSame('plain-value', $retrieved);

        $component2 = EncryptedTextInput::make('secret')
            ->model($user)
            ->setLockboxInput('input-secret');
        $component2->container(\Filament\Schemas\Schema::make($livewire));
        $hydrate = \invade($component2)->afterStateHydrated;
        $hydrate($component2);
        $this->assertSame('plain-value', $component2->getState());
    }
}
