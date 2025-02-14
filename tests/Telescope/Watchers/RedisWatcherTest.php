<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Tests\Telescope\Watchers;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Redis\Event\CommandExecuted;
use Hyperf\Redis\RedisConnection;
use Mockery as m;
use Psr\EventDispatcher\EventDispatcherInterface;
use SwooleTW\Hyperf\Telescope\EntryType;
use SwooleTW\Hyperf\Telescope\Watchers\RedisWatcher;
use SwooleTW\Hyperf\Tests\Telescope\FeatureTestCase;

/**
 * @internal
 * @coversNothing
 */
class RedisWatcherTest extends FeatureTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->app->get(ConfigInterface::class)
            ->set('telescope.watchers', [
                RedisWatcher::class => true,
            ]);
        $this->app->get(ConfigInterface::class)
            ->set('redis.foo', []);

        RedisWatcher::enableRedisEvents($this->app);

        $this->startTelescope();
    }

    public function testRegisterEnableRedisEvents()
    {
        $this->assertTrue(
            $this->app->get(ConfigInterface::class)
                ->get('redis.foo.event.enable', false)
        );
    }

    public function testRedisWatcherRegistersEntries()
    {
        $this->app->get(EventDispatcherInterface::class)
            ->dispatch(new CommandExecuted(
                'command',
                ['foo', 'bar'],
                0.0123,
                m::mock(RedisConnection::class),
                'connection',
                'result',
                null
            ));

        $entry = $this->loadTelescopeEntries()->first();

        $this->assertSame(EntryType::REDIS, $entry->type);
        $this->assertSame('command foo bar', $entry->content['command']);
        $this->assertSame('connection', $entry->content['connection']);
        $this->assertSame('0.01', $entry->content['time']);
    }
}
