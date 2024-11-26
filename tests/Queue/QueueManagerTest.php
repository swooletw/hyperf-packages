<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Tests\Queue;

use Hyperf\Config\Config;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Container;
use Hyperf\Di\Definition\DefinitionSource;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use SwooleTW\Hyperf\Encryption\Contracts\Encrypter;
use SwooleTW\Hyperf\Queue\Connectors\ConnectorInterface;
use SwooleTW\Hyperf\Queue\Contracts\Queue;
use SwooleTW\Hyperf\Queue\QueueManager;

/**
 * @internal
 * @coversNothing
 */
class QueueManagerTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    public function testDefaultConnectionCanBeResolved()
    {
        $container = $this->getContainer();
        $config = $container->get(ConfigInterface::class);
        $config->set('queue.default', 'sync');
        $config->set('queue.connections.sync', ['driver' => 'sync']);

        $manager = new QueueManager($container);
        $connector = m::mock(ConnectorInterface::class);
        $queue = m::mock(Queue::class);
        $queue->shouldReceive('setConnectionName')->once()->with('sync')->andReturnSelf();
        $connector->shouldReceive('connect')->once()->with(['driver' => 'sync'])->andReturn($queue);
        $manager->addConnector('sync', function () use ($connector) {
            return $connector;
        });

        $queue->shouldReceive('setContainer')->once()->with($container);
        $this->assertSame($queue, $manager->connection('sync'));
    }

    public function testOtherConnectionCanBeResolved()
    {
        $container = $this->getContainer();
        $config = $container->get(ConfigInterface::class);
        $config->set('queue.default', 'sync');
        $config->set('queue.connections.foo', ['driver' => 'bar']);

        $manager = new QueueManager($container);
        $connector = m::mock(ConnectorInterface::class);
        $queue = m::mock(Queue::class);
        $queue->shouldReceive('setConnectionName')->once()->with('foo')->andReturnSelf();
        $connector->shouldReceive('connect')->once()->with(['driver' => 'bar'])->andReturn($queue);
        $manager->addConnector('bar', function () use ($connector) {
            return $connector;
        });
        $queue->shouldReceive('setContainer')->once()->with($container);

        $this->assertSame($queue, $manager->connection('foo'));
    }

    public function testNullConnectionCanBeResolved()
    {
        $container = $this->getContainer();
        $config = $container->get(ConfigInterface::class);
        $config->set('queue.default', 'null');

        $manager = new QueueManager($container);
        $connector = m::mock(ConnectorInterface::class);
        $queue = m::mock(Queue::class);
        $queue->shouldReceive('setConnectionName')->once()->with('null')->andReturnSelf();
        $connector->shouldReceive('connect')->once()->with(['driver' => 'null'])->andReturn($queue);
        $manager->addConnector('null', function () use ($connector) {
            return $connector;
        });
        $queue->shouldReceive('setContainer')->once()->with($container);

        $this->assertSame($queue, $manager->connection('null'));
    }

    protected function getContainer(): Container
    {
        return new Container(
            new DefinitionSource([
                ConfigInterface::class => fn () => new Config([]),
                Encrypter::class => fn () => m::mock(Encrypter::class),
            ])
        );
    }
}
