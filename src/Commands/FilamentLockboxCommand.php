<?php

namespace N3XT0R\FilamentLockbox\Commands;

use Illuminate\Console\Command;

class FilamentLockboxCommand extends Command
{
    public $signature = 'filament-lockbox';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
