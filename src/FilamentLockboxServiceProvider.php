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
use N3XT0R\FilamentLockbox\Managers\KeyMaterial\CryptoPasswordKeyMaterialProvider;
use N3XT0R\FilamentLockbox\Resolvers\UserKeyMaterialResolver;
use N3XT0R\FilamentLockbox\Testing\TestsFilamentLockbox;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Spatie\LaravelPasskeys\Events\PasskeyUsedToAuthenticateEvent;

/**
 * Service provider for the Filament Lockbox package.
 *
 * @category Filament Security
 * @package  n3xt0r/filament-lockbox
 * @author   Ilya Beliaev
 * @license  MIT
 * @link     https://github.com/N3XT0R/filament-lockbox
 */
class FilamentLockboxServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-lockbox';

    public static string $viewNamespace = 'filament-lockbox';

    /**
     * Configure package assets, commands, and resources.
     *
     * @param Package $package Package instance to configure
     *
     * @return void
     */
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

    /**
     * Execute actions after the package is registered.
     *
     * @return void
     */
    public function packageRegistered(): void
    {
    }

    /**
     * Boot the package services.
     *
     * @return void
     */
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

    /**
     * Register form components and macros.
     *
     * @return void
     */
    protected function registerComponents(): void
    {
        // Register a macro for convenient usage in Filament forms
        FormsComponent::macro('encryptedText', function (string $name) {
            return EncryptedTextInput::make($name);
        });
    }

    /**
     * Register container singletons and event listeners.
     *
     * @return void
     */
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

    /**
     * Get the package name used for asset registration.
     *
     * @return string|null Asset package name
     */
    protected function getAssetPackageName(): ?string
    {
        return 'n3xt0r/filament-lockbox';
    }

    /**
     * Get assets to register with Filament.
     *
     * @return array<Asset> Asset definitions
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
     * Get the package's console commands.
     *
     * @return array<class-string> Command class names
     */
    protected function getCommands(): array
    {
        return [
            FilamentLockboxCommand::class,
        ];
    }

    /**
     * Get icon definitions to register.
     *
     * @return array<string> Icon mappings
     */
    protected function getIcons(): array
    {
        return [];
    }

    /**
     * Get additional route files to include.
     *
     * @return array<string> Route file paths
     */
    protected function getRoutes(): array
    {
        return [];
    }

    /**
     * Get script data to expose to the front end.
     *
     * @return array<string, mixed> Data key-value pairs
     */
    protected function getScriptData(): array
    {
        return [];
    }

    /**
     * Get migration file names for installation.
     *
     * @return array<string> Migration file names
     */
    protected function getMigrations(): array
    {
        return [
            '2025_09_12_213516_add_lockbox_fields_to_users_table',
            '2025_09_12_213517_create_lockbox_table',
        ];
    }
}
