<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Support\Composer;

use Composer\InstalledVersions;

/**
 * Utility for querying installed Composer packages.
 *
 * @category Filament Security
 * @package  n3xt0r/filament-lockbox
 * @author   Ilya Beliaev
 * @license  MIT
 * @link     https://github.com/N3XT0R/filament-lockbox
 */
class Package
{
    /**
     * Determine whether a Composer package is installed.
     *
     * @param string $package Name of the Composer package
     *
     * @return bool True if the package is installed, false otherwise
     */
    public static function isInstalled(string $package): bool
    {
        return InstalledVersions::isInstalled($package);
    }

    /**
     * Retrieve the version of an installed Composer package.
     *
     * @param string $package Name of the Composer package
     *
     * @return string|null Package version or null if not installed
     */
    public static function getVersion(string $package): ?string
    {
        return self::isInstalled($package)
            ? InstalledVersions::getPrettyVersion($package)
            : null;
    }
}
