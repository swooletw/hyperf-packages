<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Tests\Queue;

use Hyperf\Context\ApplicationContext;
use Hyperf\Di\Container;
use Hyperf\Di\Definition\DefinitionSource;
use Mockery;
use PHPUnit\Framework\TestCase;
use SwooleTW\Hyperf\Bus\Contracts\Dispatcher;
use SwooleTW\Hyperf\Bus\PendingDispatch;
use SwooleTW\Hyperf\Bus\Queueable;
use SwooleTW\Hyperf\Queue\Contracts\ShouldQueue;

/**
 * @internal
 * @coversNothing
 */
class QueueDelayTest extends TestCase
{
    public function testQueueDelay()
    {
        $this->mockContainer();

        new PendingDispatch($job = new TestJob());

        $this->assertEquals(60, $job->delay);
    }

    public function testQueueWithoutDelay()
    {
        $this->mockContainer();

        $job = new TestJob();

        dispatch($job->withoutDelay());

        $this->assertEquals(0, $job->delay);
    }

    public function testPendingDispatchWithoutDelay()
    {
        $this->mockContainer();

        $job = new TestJob();

        dispatch($job)->withoutDelay();

        $this->assertEquals(0, $job->delay);
    }

    protected function mockContainer(): void
    {
        $event = Mockery::mock(Dispatcher::class);
        $event->shouldReceive('dispatch');
        $container = new Container(
            new DefinitionSource([
                Dispatcher::class => fn () => $event,
            ])
        );

        ApplicationContext::setContainer($container);
    }
}

class TestJob implements ShouldQueue
{
    use Queueable;

    public function __construct()
    {
        $this->delay(60);
    }
}
