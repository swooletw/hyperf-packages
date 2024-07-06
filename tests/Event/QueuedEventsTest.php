<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Tests\Event;

use Hyperf\AsyncQueue\Driver\DriverFactory as QueueFactory;
use Hyperf\AsyncQueue\Driver\DriverInterface as QueueDriver;
use Hyperf\Contract\StdoutLoggerInterface;
use Mockery as m;
use Mockery\MockInterface;
use Psr\Container\ContainerInterface;
use SwooleTW\Hyperf\Event\CallQueuedListener;
use SwooleTW\Hyperf\Event\EventDispatcher;
use SwooleTW\Hyperf\Event\ListenerProvider;
use SwooleTW\Hyperf\Foundation\Contracts\Queue\ShouldQueue;
use SwooleTW\Hyperf\Tests\TestCase;

use function SwooleTW\Hyperf\Event\queueable;

/**
 * @internal
 * @coversNothing
 */
class QueuedEventsTest extends TestCase
{
    /**
     * @var ContainerInterface|MockInterface
     */
    private ContainerInterface $container;

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = m::mock(ContainerInterface::class);
    }

    public function testQueuedEventHandlersAreQueued()
    {
        $this->container
            ->shouldReceive('get')
            ->once()
            ->with(TestDispatcherQueuedHandler::class)
            ->andReturn(new TestDispatcherQueuedHandler());

        $d = $this->getEventDispatcher();

        $queue = m::mock(QueueFactory::class);
        $driver = m::mock(QueueDriver::class);
        $queue->shouldReceive('get')->once()->with('default')->andReturn($driver);
        $driver->shouldReceive('push')->once()->with(m::type(CallQueuedListener::class), 0);

        $d->setQueueResolver(fn () => $queue);

        $d->listen('some.event', TestDispatcherQueuedHandler::class . '@someMethod');
        $d->dispatch('some.event', ['foo', 'bar']);
    }

    public function testQueueIsSetByGetConnection()
    {
        $this->container
            ->shouldReceive('get')
            ->once()
            ->with(TestDispatcherGetConnection::class)
            ->andReturn(new TestDispatcherGetConnection());

        $d = $this->getEventDispatcher();

        $queue = m::mock(QueueFactory::class);
        $driver = m::mock(QueueDriver::class);
        $queue->shouldReceive('get')->once()->with('some_other_connection')->andReturn($driver);
        $driver->shouldReceive('push')->once()->with(m::type(CallQueuedListener::class), 0);

        $d->setQueueResolver(fn () => $queue);

        $d->listen('some.event', TestDispatcherGetConnection::class . '@handle');
        $d->dispatch('some.event', ['foo', 'bar']);
    }

    public function testDelayIsSetByWithDelay()
    {
        $this->container
            ->shouldReceive('get')
            ->once()
            ->with(TestDispatcherGetDelay::class)
            ->andReturn(new TestDispatcherGetConnection());

        $d = $this->getEventDispatcher();

        $queue = m::mock(QueueFactory::class);
        $driver = m::mock(QueueDriver::class);
        $queue->shouldReceive('get')->once()->with('default')->andReturn($driver);
        $driver->shouldReceive('push')->once()->with(m::type(CallQueuedListener::class), 20);

        $d->setQueueResolver(fn () => $queue);

        $d->listen('some.event', TestDispatcherGetDelay::class . '@handle');
        $d->dispatch('some.event', ['foo', 'bar']);
    }

    public function testQueueIsSetByGetConnectionDynamically()
    {
        $this->container
            ->shouldReceive('get')
            ->once()
            ->with(TestDispatcherGetConnectionDynamically::class)
            ->andReturn(new TestDispatcherGetConnectionDynamically());

        $d = $this->getEventDispatcher();

        $queue = m::mock(QueueFactory::class);
        $driver = m::mock(QueueDriver::class);
        $queue->shouldReceive('get')->once()->with('redis')->andReturn($driver);
        $driver->shouldReceive('push')->once()->with(m::type(CallQueuedListener::class), 0);

        $d->setQueueResolver(fn () => $queue);

        $d->listen('some.event', TestDispatcherGetConnectionDynamically::class . '@handle');
        $d->dispatch('some.event', [
            ['shouldUseRedisConnection' => true],
            'bar',
        ]);
    }

    public function testDelayIsSetByWithDelayDynamically()
    {
        $this->container
            ->shouldReceive('get')
            ->once()
            ->with(TestDispatcherGetDelayDynamically::class)
            ->andReturn(new TestDispatcherGetDelayDynamically());

        $d = $this->getEventDispatcher();

        $queue = m::mock(QueueFactory::class);
        $driver = m::mock(QueueDriver::class);
        $queue->shouldReceive('get')->once()->with('default')->andReturn($driver);
        $driver->shouldReceive('push')->once()->with(m::type(CallQueuedListener::class), 60);

        $d->setQueueResolver(fn () => $queue);

        $d->listen('some.event', TestDispatcherGetDelayDynamically::class . '@handle');
        $d->dispatch('some.event', [['useHighDelay' => true], 'bar']);
    }

    public function testQueueMaxAttempts()
    {
        $this->container
            ->shouldReceive('get')
            ->once()
            ->with(TestDispatcherOptions::class)
            ->andReturn(new TestDispatcherOptions());

        $d = $this->getEventDispatcher();

        $queue = m::mock(QueueFactory::class);
        $driver = m::mock(QueueDriver::class);
        $queue->shouldReceive('get')->once()->with('default')->andReturn($driver);
        $driver->shouldReceive('push')->once()->withArgs(function ($job, $delay) {
            return $job->getMaxAttempts() === 1 && $delay === 0;
        });

        $d->setQueueResolver(fn () => $queue);

        $d->listen('some.event', TestDispatcherOptions::class . '@handle');
        $d->dispatch('some.event', ['foo', 'bar']);
    }

    public function testQueuedClosureEventHandlersAreQueued()
    {
        $d = $this->getEventDispatcher();

        $queue = m::mock(QueueFactory::class);
        $driver = m::mock(QueueDriver::class);
        $queue->shouldReceive('get')->once()->with('default')->andReturn($driver);
        $driver->shouldReceive('push')->once()->with(m::type(CallQueuedListener::class), 0);

        $d->setQueueResolver(fn () => $queue);

        $d->listen('some.event', queueable(function () {}));
        $d->dispatch('some.event', ['foo', 'bar']);
    }

    public function testQueuedClosureQueueIsSetByOnConnection()
    {
        $d = $this->getEventDispatcher();

        $queue = m::mock(QueueFactory::class);
        $driver = m::mock(QueueDriver::class);
        $queue->shouldReceive('get')->once()->with('some_other_connection')->andReturn($driver);
        $driver->shouldReceive('push')->once()->with(m::type(CallQueuedListener::class), 0);

        $d->setQueueResolver(fn () => $queue);

        $d->listen('some.event', queueable(function () {})->onConnection('some_other_connection'));
        $d->dispatch('some.event', ['foo', 'bar']);
    }

    public function testQueuedClosureDelayIsSetByDelay()
    {
        $d = $this->getEventDispatcher();

        $queue = m::mock(QueueFactory::class);
        $driver = m::mock(QueueDriver::class);
        $queue->shouldReceive('get')->once()->with('default')->andReturn($driver);
        $driver->shouldReceive('push')->once()->with(m::type(CallQueuedListener::class), 20);

        $d->setQueueResolver(fn () => $queue);

        $d->listen('some.event', queueable(function () {})->delay(20));
        $d->dispatch('some.event', ['foo', 'bar']);
    }

    public function testAnonymousQueuedClosureListeners()
    {
        $d = $this->getEventDispatcher();

        $queue = m::mock(QueueFactory::class);
        $driver = m::mock(QueueDriver::class);
        $queue->shouldReceive('get')->once()->with('default')->andReturn($driver);
        $driver->shouldReceive('push')->once()->with(m::type(CallQueuedListener::class), 0);

        $d->setQueueResolver(fn () => $queue);

        $d->listen(queueable(function (TestDispatcherAnonymousQueuedClosureEvent $event) {}));
        $d->dispatch(new TestDispatcherAnonymousQueuedClosureEvent());
    }

    private function getEventDispatcher(?StdoutLoggerInterface $logger = null): EventDispatcher
    {
        return new EventDispatcher(new ListenerProvider(), $logger, $this->container);
    }
}

class TestDispatcherQueuedHandler implements ShouldQueue
{
    public function handle() {}
}

class TestDispatcherGetConnection implements ShouldQueue
{
    public $connection = 'my_connection';

    public function handle() {}

    public function viaConnection()
    {
        return 'some_other_connection';
    }
}

class TestDispatcherGetDelay implements ShouldQueue
{
    public $delay = 10;

    public function handle() {}

    public function withDelay()
    {
        return 20;
    }
}

class TestDispatcherOptions implements ShouldQueue
{
    public $maxAttempts = 1;

    public function handle() {}
}

class TestDispatcherGetConnectionDynamically implements ShouldQueue
{
    public function handle() {}

    public function viaConnection($_, $event)
    {
        if ($event['shouldUseRedisConnection']) {
            return 'redis';
        }

        return 'sqs';
    }
}

class TestDispatcherGetDelayDynamically implements ShouldQueue
{
    public $delay = 10;

    public function handle() {}

    public function withDelay($_, $event)
    {
        if ($event['useHighDelay']) {
            return 60;
        }

        return 20;
    }
}

class TestDispatcherAnonymousQueuedClosureEvent {}
