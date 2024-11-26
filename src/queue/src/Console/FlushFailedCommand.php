<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Queue\Console;

use SwooleTW\Hyperf\Foundation\Console\Command;
use SwooleTW\Hyperf\Queue\Failed\FailedJobProviderInterface;

class FlushFailedCommand extends Command
{
    /**
     * The console command name.
     */
    protected ?string $signature = 'queue:flush {--hours= : The number of hours to retain failed job data}';

    /**
     * The console command description.
     */
    protected string $description = 'Flush all of the failed queue jobs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->app->get(FailedJobProviderInterface::class)
            ->flush($this->option('hours'));

        if ($this->option('hours')) {
            $this->info("All jobs that failed more than {$this->option('hours')} hours ago have been deleted successfully.");

            return;
        }

        $this->info('All failed jobs deleted successfully.');
    }
}
