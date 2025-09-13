<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox;

use Filament\Facades\Filament;
use Filament\Forms\FormsComponent;
use Filament\Support\Assets\AlpineComponent;
use Filament\Support\Assets\Asset;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Event;
use Livewire\Features\SupportTesting\Testable;
use N3XT0R\FilamentLockbox\Commands\FilamentLockboxCommand;
use N3XT0R\FilamentLockbox\Forms\Components\EncryptedTextInput;
use N3XT0R\FilamentLockbox\Listeners\SetLockboxPasskeyFlag;
use N3XT0R\FilamentLockbox\Support\KeyMaterial\CryptoPasswordKeyMaterialProvider;
use N3XT0R\FilamentLockbox\Support\UserKeyMaterialResolver;
use N3XT0R\FilamentLockbox\Testing\TestsFilamentLockbox;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Spatie\LaravelPasskeys\Events\PasskeyUsedToAuthenticateEvent;

class FilamentLockboxServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-lockbox';

    public static string $viewNamespace = 'filament-lockbox';

    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package->name(static::$name)
            ->hasCommands($this->getCommands())
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->publishMigrations()
                    ->askToRunMigrations()
                    ->askToStarRepoOnGitHub('n3xt0r/filament-lockbox');
            });

        $configFileName = $package->shortName();

        if (file_exists($package->basePath("/../config/{$configFileName}.php"))) {
            $package->hasConfigFile();
        }

        if (file_exists($package->basePath('/../database/migrations'))) {
            $package->hasMigrations($this->getMigrations());
        }

        if (file_exists($package->basePath('/../resources/lang'))) {
            $package->hasTranslations();
        }

        if (file_exists($package->basePath('/../resources/views'))) {
            $package->hasViews(static::$viewNamespace);
        }
    }

    public function packageRegistered(): void
    {
    }

    public function packageBooted(): void
    {
        // Asset Registration
        FilamentAsset::register(
            $this->getAssets(),
            $this->getAssetPackageName(),
        );

        FilamentAsset::registerScriptData(
            $this->getScriptData(),
            $this->getAssetPackageName(),
        );

        // Icon Registration
        FilamentIcon::register($this->getIcons());

        // Handle Stubs
        if (app()->runningInConsole()) {
            foreach (app(Filesystem::class)->files(__DIR__ . '/../stubs/') as $file) {
                $this->publishes([
                    $file->getRealPath() => base_path("stubs/filament-lockbox/{$file->getFilename()}"),
                ], 'filament-lockbox-stubs');
            }
        }

        // Testing
        Testable::mixin(new TestsFilamentLockbox());
        $this->registerComponents();
        $this->registerSingletons();
    }

    protected function registerComponents(): void
    {
        // Register a macro for convenient usage in Filament forms
        FormsComponent::macro('encryptedText', function (string $name) {
            return EncryptedTextInput::make($name);
        });
    }

    protected function registerSingletons(): void
    {
        $this->app->singleton(UserKeyMaterialResolver::class, function () {
            $providerClasses = config('filament-lockbox.providers', []);

            $providers = array_map(static function (string $class) {
                return new $class();
            }, $providerClasses);

            $resolver = new UserKeyMaterialResolver($providers);
            // Always register fallback provider
            $resolver->registerProvider(new CryptoPasswordKeyMaterialProvider());

            return $resolver;
        });

        Event::listen(
            PasskeyUsedToAuthenticateEvent::class,
            SetLockboxPasskeyFlag::class,
        );

    }

    protected function getAssetPackageName(): ?string
    {
        return 'n3xt0r/filament-lockbox';
    }

    /**
     * @return array<Asset>
     */
    protected function getAssets(): array
    {
        return [
            // AlpineComponent::make('filament-lockbox', __DIR__ . '/../resources/dist/components/filament-lockbox.js'),
            // Css::make('filament-lockbox-styles', __DIR__ . '/../resources/dist/filament-lockbox.css'),
            // Js::make('filament-lockbox-scripts', __DIR__ . '/../resources/dist/filament-lockbox.js'),
        ];
    }

    /**
     * @return array<class-string>
     */
    protected function getCommands(): array
    {
        return [
            FilamentLockboxCommand::class,
        ];
    }

    /**
     * @return array<string>
     */
    protected function getIcons(): array
    {
        return [];
    }

    /**
     * @return array<string>
     */
    protected function getRoutes(): array
    {
        return [];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getScriptData(): array
    {
        return [];
    }

    /**
     * @return array<string>
     */
    protected function getMigrations(): array
    {
        return [
            '2025_09_12_213516_add_lockbox_fields_to_users_table',
        ];
    }
}
