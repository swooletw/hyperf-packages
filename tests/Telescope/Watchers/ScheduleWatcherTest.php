<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Tests\Telescope\Watchers;

use Exception;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Crontab\Crontab;
use Hyperf\Crontab\Event\AfterExecute;
use Hyperf\Crontab\Event\BeforeExecute;
use Hyperf\Crontab\Event\FailToExecute;
use Mockery as m;
use Psr\EventDispatcher\EventDispatcherInterface;
use SwooleTW\Hyperf\Telescope\EntryType;
use SwooleTW\Hyperf\Telescope\Watchers\ScheduleWatcher;
use SwooleTW\Hyperf\Tests\Telescope\FeatureTestCase;

/**
 * @internal
 * @coversNothing
 */
class ScheduleWatcherTest extends FeatureTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->app->get(ConfigInterface::class)
            ->set('telescope.watchers', [
                ScheduleWatcher::class => true,
            ]);

        $_SERVER['argv'][1] = 'schedule:run';

        $this->startTelescope();
    }

    protected function tearDown(): void
    {
        unset($_SERVER['argv'][1]);

        parent::tearDown();
    }

    public function testScheduleRegistersEntryWithSuccessfulTask()
    {
        $this->app->get(EventDispatcherInterface::class)
            ->dispatch(new BeforeExecute(
                m::mock(Crontab::class)
            ));

        $crontab = m::mock(Crontab::class);
        $crontab->shouldReceive('getType')
            ->once()
            ->andReturn('closure');
        $crontab->shouldReceive('getMemo')
            ->once()
            ->andReturn('test schedule');
        $crontab->shouldReceive('getRule')
            ->once()
            ->andReturn('* * * * *');
        $crontab->shouldReceive('getTimezone')
            ->once()
            ->andReturn('UTC');

        $this->app->get(EventDispatcherInterface::class)
            ->dispatch(new AfterExecute($crontab));

        $entry = $this->loadTelescopeEntries()->first();

        $this->assertSame(EntryType::SCHEDULED_TASK, $entry->type);
        $this->assertSame('Closure', $entry->content['command']);
        $this->assertSame('test schedule', $entry->content['description']);
        $this->assertSame('* * * * *', $entry->content['expression']);
        $this->assertSame('UTC', $entry->content['timezone']);
        $this->assertSame('', $entry->content['user']);
        $this->assertSame('success', $entry->content['output']);
    }

    public function testScheduleRegistersEntryWithFailedTask()
    {
        $this->app->get(EventDispatcherInterface::class)
            ->dispatch(new BeforeExecute(
                m::mock(Crontab::class)
            ));

        $crontab = m::mock(Crontab::class);
        $crontab->shouldReceive('getType')
            ->once()
            ->andReturn('command');
        $crontab->shouldReceive('getName')
            ->once()
            ->andReturn('command');
        $crontab->shouldReceive('getMemo')
            ->once()
            ->andReturn('test schedule');
        $crontab->shouldReceive('getRule')
            ->once()
            ->andReturn('* * * * *');
        $crontab->shouldReceive('getTimezone')
            ->once()
            ->andReturn('UTC');

        $this->app->get(EventDispatcherInterface::class)
            ->dispatch(new FailToExecute(
                $crontab,
                new Exception('test')
            ));

        $entry = $this->loadTelescopeEntries()->first();

        $this->assertSame(EntryType::SCHEDULED_TASK, $entry->type);
        $this->assertSame('command', $entry->content['command']);
        $this->assertSame('test schedule', $entry->content['description']);
        $this->assertSame('* * * * *', $entry->content['expression']);
        $this->assertSame('UTC', $entry->content['timezone']);
        $this->assertSame('', $entry->content['user']);
        $this->assertTrue(str_starts_with($entry->content['output'], '[fail]'));
    }
}
