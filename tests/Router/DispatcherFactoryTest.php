<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Tests\Router;

use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ContainerInterface;
use Hyperf\HttpServer\Router\RouteCollector;
use Mockery;
use Mockery\MockInterface;
use SwooleTW\Hyperf\Router\DispatcherFactory;
use SwooleTW\Hyperf\Router\NamedRouteCollector;
use SwooleTW\Hyperf\Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class DispatcherFactoryTest extends TestCase
{
    /**
     * @var ContainerInterface|MockInterface
     */
    private ContainerInterface $container;

    protected function setUp(): void
    {
        $this->mockContainer();
    }

    public function testGetRouter()
    {
        /** @var MockInterface|NamedRouteCollector */
        $router = Mockery::mock(NamedRouteCollector::class);

        $this->container
            ->shouldReceive('make')
            ->with(RouteCollector::class, ['server' => 'http'])
            ->once()
            ->andReturn($router);

        $factory = new DispatcherFactory($this->container);

        $this->assertEquals($router, $factory->getRouter('http'));
    }

    public function testInitConfigRoute()
    {
        DispatcherFactory::addRouteFile(__DIR__ . '/routes/foo.php');
        DispatcherFactory::addRouteFile(__DIR__ . '/routes/bar.php');

        /** @var MockInterface|NamedRouteCollector */
        $router = Mockery::mock(NamedRouteCollector::class);

        $router->shouldReceive('get')->with('/foo', 'Handler::Foo')->once();
        $router->shouldReceive('get')->with('/bar', 'Handler::Bar')->once();

        $this->container
            ->shouldReceive('make')
            ->with(RouteCollector::class, ['server' => 'http'])
            ->andReturn($router);

        $factory = new DispatcherFactory($this->container);

        $factory->initConfigRoute();
    }

    private function mockContainer()
    {
        ! defined('BASE_PATH') && define('BASE_PATH', __DIR__);

        $this->container = Mockery::mock(ContainerInterface::class);

        ApplicationContext::setContainer($this->container);
    }
}
