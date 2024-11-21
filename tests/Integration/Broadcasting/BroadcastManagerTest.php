<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Tests\Integration\Broadcasting;

use Hyperf\Contract\ConfigInterface;
use InvalidArgumentException;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use SwooleTW\Hyperf\Broadcasting\BroadcastManager;
use SwooleTW\Hyperf\Broadcasting\Channel;
use SwooleTW\Hyperf\Broadcasting\Contracts\ShouldBeUnique;
use SwooleTW\Hyperf\Broadcasting\Contracts\ShouldBroadcast;
use SwooleTW\Hyperf\Broadcasting\Contracts\ShouldBroadcastNow;

/**
 * @internal
 * @coversNothing
 */
class BroadcastManagerTest extends TestCase
{
    // TODO: waiting for queue implementation
    // public function testEventCanBeBroadcastNow()
    // {
    //     Bus::fake();
    //     Queue::fake();
    //
    //     Broadcast::queue(new TestEventNow);
    //
    //     Bus::assertDispatched(BroadcastEvent::class);
    //     Queue::assertNotPushed(BroadcastEvent::class);
    // }
    //
    // public function testEventsCanBeBroadcast()
    // {
    //     Bus::fake();
    //     Queue::fake();
    //
    //     Broadcast::queue(new TestEvent);
    //
    //     Bus::assertNotDispatched(BroadcastEvent::class);
    //     Queue::assertPushed(BroadcastEvent::class);
    // }
    //
    // public function testUniqueEventsCanBeBroadcast()
    // {
    //     Bus::fake();
    //     Queue::fake();
    //
    //     Broadcast::queue(new TestEventUnique);
    //
    //     Bus::assertNotDispatched(UniqueBroadcastEvent::class);
    //     Queue::assertPushed(UniqueBroadcastEvent::class);
    //
    //     $lockKey = 'laravel_unique_job:'.UniqueBroadcastEvent::class.':'.TestEventUnique::class;
    //     $this->assertFalse($this->app->get(Cache::class)->lock($lockKey, 10)->get());
    // }

    public function testThrowExceptionWhenUnknownStoreIsUsed()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Broadcast connection [alien_connection] is not defined.');

        $config = m::mock(ContainerInterface::class);
        $config->shouldReceive('get')->with('broadcasting.connections.alien_connection')->andReturn(null);

        $app = m::mock(ContainerInterface::class);
        $app->shouldReceive('get')->with(ConfigInterface::class)->andReturn($config);

        $broadcastManager = new BroadcastManager($app);

        $broadcastManager->connection('alien_connection');
    }
}

class TestEvent implements ShouldBroadcast
{
    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel[]|string[]
     */
    public function broadcastOn(): array
    {
        return [];
    }
}

class TestEventNow implements ShouldBroadcastNow
{
    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel[]|string[]
     */
    public function broadcastOn(): array
    {
        return [];
    }
}

class TestEventUnique implements ShouldBroadcast, ShouldBeUnique
{
    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel[]|string[]
     */
    public function broadcastOn(): array
    {
        return [];
    }
}
