<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Tests\Telescope\Watchers;

use Hyperf\Contract\ConfigInterface;
use SwooleTW\Hyperf\Foundation\Console\Command;
use SwooleTW\Hyperf\Foundation\Console\Contracts\Kernel as KernelContract;
use SwooleTW\Hyperf\Telescope\EntryType;
use SwooleTW\Hyperf\Telescope\Watchers\CommandWatcher;
use SwooleTW\Hyperf\Tests\Telescope\FeatureTestCase;

/**
 * @internal
 * @coversNothing
 */
class CommandWatcherTest extends FeatureTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->app->get(ConfigInterface::class)
            ->set('telescope.watchers', [
                CommandWatcher::class => true,
            ]);

        $this->startTelescope();
    }

    public function testCommandWatcherRegisterEntry()
    {
        $this->app->get(KernelContract::class)
            ->registerCommand(MyCommand::class);

        $this->app->get(KernelContract::class)
            ->call('telescope:test-command');

        $entry = $this->loadTelescopeEntries()->first();

        $this->assertSame(EntryType::COMMAND, $entry->type);
        $this->assertSame('telescope:test-command', $entry->content['command']);
        $this->assertSame(0, $entry->content['exit_code']);
    }
}

class MyCommand extends Command
{
    protected ?string $signature = 'telescope:test-command';

    public function handle()
    {
    }
}
