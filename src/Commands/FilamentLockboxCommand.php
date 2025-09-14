<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Commands;

use Illuminate\Console\Command;

/**
 * Console command for Filament Lockbox.
 *
 * @category Filament Security
 * @package  n3xt0r/filament-lockbox
 * @author   Ilya Beliaev
 * @license  MIT
 * @link     https://github.com/N3XT0R/filament-lockbox
 */
class FilamentLockboxCommand extends Command
{
    public $signature = 'filament-lockbox';

    public $description = 'My command';

    /**
     * Execute the console command.
     *
     * @return int Command exit code
     */
    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
