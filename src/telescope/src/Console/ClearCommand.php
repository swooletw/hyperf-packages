<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Telescope\Console;

use SwooleTW\Hyperf\Foundation\Console\Command;
use SwooleTW\Hyperf\Telescope\Contracts\ClearableRepository;

class ClearCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected ?string $signature = 'telescope:clear';

    /**
     * The console command description.
     */
    protected string $description = 'Delete all Telescope data from storage';

    /**
     * Execute the console command.
     */
    public function handle(ClearableRepository $storage)
    {
        $storage->clear();

        $this->info('Telescope entries cleared!');
    }
}
