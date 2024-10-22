<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Tests\ObjectPool;

use Closure;
use Hyperf\Context\ApplicationContext;
use Mockery as m;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use SwooleTW\Hyperf\ObjectPool\ObjectPool;
use SwooleTW\Hyperf\ObjectPool\PoolFactory;
use SwooleTW\Hyperf\ObjectPool\PoolProxy;

/**
 * @internal
 * @coversNothing
 */
class PoolProxyTest extends TestCase
{
    public function testCallPoolProxy()
    {
        $pool = m::mock(ObjectPool::class);
        $pool->shouldReceive('get')
            ->once()
            ->andReturn($object = new Foo());
        $pool->shouldReceive('release')
            ->once();

        $poolFactory = m::mock(PoolFactory::class);
        $poolFactory->shouldReceive('get')
            ->with('foo', m::type(Closure::class), ['foo' => 'bar'])
            ->once()
            ->andReturn($pool);

        $container = m::mock(ContainerInterface::class);
        $container->shouldReceive('get')
            ->with(PoolFactory::class)
            ->once()
            ->andReturn($poolFactory);

        ApplicationContext::setContainer($container);

        $proxy = new PoolProxy(
            'foo',
            fn () => $object,
            ['foo' => 'bar'],
            fn ($object) => $object->state = 'released'
        );

        $this->assertSame('init', $object->state);

        $proxy->handle();

        $this->assertSame('released', $object->state);
    }
}

class Foo
{
    public string $state = 'init';

    public function handle(): void
    {
    }
}