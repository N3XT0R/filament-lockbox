<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Support\Composer;

use Composer\InstalledVersions;

class Package
{
    public static function isInstalled(string $package): bool
    {
        return InstalledVersions::isInstalled($package);
    }

    public static function getVersion(string $package): ?string
    {
        return self::isInstalled($package)
            ? InstalledVersions::getPrettyVersion($package)
            : null;
    }
}
