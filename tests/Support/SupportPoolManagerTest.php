<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Tests\Support;

use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;
use Mockery as m;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use SwooleTW\Hyperf\ObjectPool\ObjectPool;
use SwooleTW\Hyperf\ObjectPool\PoolFactory;
use SwooleTW\Hyperf\ObjectPool\PoolProxy;
use SwooleTW\Hyperf\Support\PoolManager;

/**
 * @internal
 * @coversNothing
 */
class SupportPoolManagerTest extends TestCase
{
    public function testAddPoolables()
    {
        $manager = $this->getManager();
        $manager->addPoolable('bar');

        $this->assertEquals(['foo', 'bar'], $manager->getPoolables());
    }

    public function testRemovePoolable()
    {
        $manager = $this->getManager();
        $manager->removePoolable('bar');

        $this->assertEquals(['foo'], $manager->getPoolables());

        $manager->removePoolable('foo');
        $this->assertEquals([], $manager->getPoolables());
    }

    public function testSetPoolables()
    {
        $manager = $this->getManager();
        $manager->setPoolables(['bar', 'baz']);

        $this->assertEquals(['bar', 'baz'], $manager->getPoolables());
    }

    public function testCreateDriverWithPoolProxy()
    {
        $manager = $this->getManager(new FooDriver());
        $driver = $manager->driver('foo');

        $this->assertInstanceOf(PoolProxy::class, $driver);
    }

    public function testCreateDriverWithPoolProxyReleaseCallback()
    {
        $manager = $this->getManager($driver = new FooDriver());
        $manager->setReleaseCallback('foo', function ($driver) {
            $driver->state = 'released';
        });
        $proxy = $manager->driver('foo');

        $this->assertSame('init', $driver->state);

        $proxy->handle();

        $this->assertSame('released', $driver->state);
    }

    public function testCreateDriver()
    {
        $manager = $this->getManager();

        $this->assertInstanceOf(BarDriver::class, $manager->driver('bar'));
    }

    protected function getManager(?object $object = null): StubPoolManager
    {
        $container = m::mock(ContainerInterface::class);
        $container->shouldReceive('get')
            ->with(ConfigInterface::class)
            ->andReturn(m::mock(ConfigInterface::class));

        $pool = m::mock(ObjectPool::class);
        $pool->shouldReceive('get')
            ->andReturn($object);
        $pool->shouldReceive('release');

        $poolFactory = m::mock(PoolFactory::class);
        $poolFactory->shouldReceive('get')
            ->andReturn($pool);

        $container->shouldReceive('get')
            ->with(PoolFactory::class)
            ->andReturn($poolFactory);

        ApplicationContext::setContainer($container);

        return new StubPoolManager($container);
    }
}

class StubPoolManager extends PoolManager
{
    protected array $poolables = ['foo'];

    public function getDefaultDriver(): string
    {
        return 'foo';
    }

    public function getPoolConfig(string $driver): array
    {
        return [$driver];
    }

    public function createFooDriver()
    {
        return new FooDriver();
    }

    public function createBarDriver()
    {
        return new BarDriver();
    }
}

class FooDriver
{
    public string $state = 'init';

    public function handle(): void
    {
    }
}

class BarDriver
{
}
