<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Tests\Integration\Forms\Components;

use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;
use N3XT0R\FilamentLockbox\Forms\Components\DecryptedTextDisplay;
use N3XT0R\FilamentLockbox\Resolvers\UserKeyMaterialResolver;
use N3XT0R\FilamentLockbox\Services\LockboxService;
use N3XT0R\FilamentLockbox\Tests\Stubs\Auth\LockboxUser;
use N3XT0R\FilamentLockbox\Tests\Stubs\Livewire\DummyLivewire;
use N3XT0R\FilamentLockbox\Tests\Stubs\Providers\EchoProvider;
use N3XT0R\FilamentLockbox\Tests\TestCase;

class DecryptedTextDisplayTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['app.key' => 'base64:' . base64_encode(random_bytes(32))]);
    }

    public function testDecryptedValueIsShownWhenSecretProvided(): void
    {
        $provider = new EchoProvider();
        app(UserKeyMaterialResolver::class)->registerProvider($provider);

        $user = new LockboxUser();
        $user->providerClass = $provider::class;
        $user->forceFill([
            'name' => 'Test',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ])->save();

        $this->actingAs($user);

        $service = app(LockboxService::class);
        $service->set($user, 'secret', 'plain-value', $user, 'input-secret');
        $livewire = new DummyLivewire();

        $component = DecryptedTextDisplay::make('secret')
            ->model($user)
            ->setLockboxInput('input-secret');
        $component->container(Schema::make($livewire));
        $component->callAfterStateHydrated();

        $state = $component->getState();
        $this->assertInstanceOf(HtmlString::class, $state);
        $this->assertSame('plain-value', (string)$state);
    }
}
