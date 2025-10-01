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
use N3XT0R\FilamentLockbox\Tests\Stubs\Lockbox\DummyHasLockbox;
use N3XT0R\FilamentLockbox\Tests\Stubs\Lockbox\DummyHasLockboxKeys;
use N3XT0R\FilamentLockbox\Tests\Stubs\Lockbox\DummyNoLockboxKeysUser;
use N3XT0R\FilamentLockbox\Tests\Stubs\Providers\EchoProvider;
use N3XT0R\FilamentLockbox\Tests\TestCase;

class DecryptedTextDisplayTest extends TestCase
{
    public function testDecryptedValueIsShownWhenSecretProvided(): void
    {
        $provider = new EchoProvider();
        app(UserKeyMaterialResolver::class)->registerProvider($provider);

        $user = LockboxUser::factory()->create([
            'lockbox_provider' => $provider::class,
        ]);

        $this->actingAs($user);

        $service = app(LockboxService::class);
        $service->set($user, 'secret', 'plain-value', $user, 'input-secret');

        $component = DecryptedTextDisplay::make('secret')
            ->model($user)
            ->setLockboxInput('input-secret');

        $component->container(Schema::make(new DummyLivewire()));
        $component->callAfterStateHydrated();

        $state = $component->getState();
        $this->assertInstanceOf(HtmlString::class, $state);
        $this->assertSame('plain-value', (string)$state);
    }

    public function testShowsWarningWhenInputMissing(): void
    {
        $user = LockboxUser::factory()->create();
        $this->actingAs($user);

        $record = new DummyHasLockbox();

        $component = DecryptedTextDisplay::make('secret')->model($record);
        $component->container(Schema::make(new DummyLivewire()));
        $component->callAfterStateHydrated();

        $state = (string)$component->getState();
        $this->assertStringContainsString('Unlock required to view this field.', $state);
    }

    public function testShowsDashWhenRecordDoesNotImplementHasLockbox(): void
    {
        $user = DummyHasLockboxKeys::factory()->create();
        $this->actingAs($user);

        $component = DecryptedTextDisplay::make('secret')->model(new DummyNoLockboxKeysUser());
        $component->container(Schema::make(new DummyLivewire()));
        $component->callAfterStateHydrated();

        $state = (string)$component->getState();
        $this->assertStringContainsString('—', $state);
    }

    public function testShowsErrorWhenUserDoesNotImplementHasLockboxKeys(): void
    {
        $record = new DummyHasLockbox();
        $user = DummyNoLockboxKeysUser::factory()->create();

        $this->actingAs($user);

        $component = DecryptedTextDisplay::make('secret')
            ->model($record)
            ->setLockboxInput('any');

        $component->container(Schema::make(new DummyLivewire()));
        $component->callAfterStateHydrated();

        $state = (string)$component->getState();
        $this->assertStringContainsString('—', $state);
    }

    public function testShowsDashWhenDecryptedValueIsEmpty(): void
    {
        $user = LockboxUser::factory()->create();
        $this->actingAs($user);

        $record = new DummyHasLockbox();

        $this->mock(LockboxService::class)
            ->shouldReceive('get')
            ->andReturn('');

        $component = DecryptedTextDisplay::make('secret')
            ->model($record)
            ->setLockboxInput('secret');

        $component->container(Schema::make(new DummyLivewire()));
        $component->callAfterStateHydrated();

        $state = (string)$component->getState();
        $this->assertStringContainsString('—', $state);
    }

    public function testShowsDashWhenUserDoesNotImplementHasLockboxKeys(): void
    {
        $record = new DummyHasLockbox();
        $user = DummyNoLockboxKeysUser::factory()->create();
        $this->actingAs($user);

        $component = DecryptedTextDisplay::make('secret')
            ->model($record)
            ->setLockboxInput('any');

        $component->container(Schema::make(new DummyLivewire()));
        $component->callAfterStateHydrated();

        $this->assertStringContainsString('—', (string)$component->getState());
    }

    public function testShowsDashWhenNoAuthenticatedUser(): void
    {
        $record = new DummyHasLockbox();

        $component = DecryptedTextDisplay::make('secret')->model($record);
        $component->container(Schema::make(new DummyLivewire()));
        $component->callAfterStateHydrated();

        $this->assertStringContainsString('—', (string)$component->getState());
    }

    public function testShowsDashWhenLockboxValueIsMissing(): void
    {
        $user = LockboxUser::factory()->create();
        $this->actingAs($user);

        $record = new DummyHasLockbox();

        $this->mock(LockboxService::class)
            ->shouldReceive('get')
            ->andReturn(null);

        $component = DecryptedTextDisplay::make('secret')
            ->model($record)
            ->setLockboxInput('dummy-input');

        $component->container(Schema::make(new DummyLivewire()));
        $component->callAfterStateHydrated();

        $this->assertStringContainsString('—', (string)$component->getState());
    }
}
