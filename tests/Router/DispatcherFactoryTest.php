<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Tests\Router;

use Hyperf\Context\ApplicationContext;
use Hyperf\Di\Container;
use Hyperf\Di\Definition\DefinitionSource;
use Hyperf\HttpServer\Router\RouteCollector;
use Mockery;
use Mockery\MockInterface;
use SwooleTW\Hyperf\Router\DispatcherFactory;
use SwooleTW\Hyperf\Router\NamedRouteCollector;
use SwooleTW\Hyperf\Router\RouteFileCollector;
use SwooleTW\Hyperf\Router\Router;
use SwooleTW\Hyperf\Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class DispatcherFactoryTest extends TestCase
{
    public function testGetRouter()
    {
        if (! defined('BASE_PATH')) {
            $this->markTestSkipped('skip it because DispatcherFactory in hyperf is dirty.');
        }

        /** @var MockInterface|NamedRouteCollector */
        $routeCollector = Mockery::mock(NamedRouteCollector::class);

        $getContainer = $this->getContainer([
            RouteCollector::class => fn () => $routeCollector,
            RouteFileCollector::class => fn () => new RouteFileCollector(['foo']),
        ]);

        $factory = new DispatcherFactory($getContainer);

        $this->assertEquals($routeCollector, $factory->getRouter('http'));
    }

    public function testInitConfigRoute()
    {
        if (! defined('BASE_PATH')) {
            $this->markTestSkipped('skip it because DispatcherFactory in hyperf is dirty.');
        }

        /** @var MockInterface|NamedRouteCollector */
        $routeCollector = Mockery::mock(NamedRouteCollector::class);
        $routeCollector->shouldReceive('get')->with('/foo', 'Handler::Foo')->once();
        $routeCollector->shouldReceive('get')->with('/bar', 'Handler::Bar')->once();

        $container = $this->getContainer([
            RouteCollector::class => fn () => $routeCollector,
            RouteFileCollector::class => fn () => new RouteFileCollector([
                __DIR__ . '/routes/foo.php',
                __DIR__ . '/routes/bar.php',
            ]),
        ]);

        $dispatcherFactory = new DispatcherFactory($container);
        $container->define(Router::class, fn () => new Router($dispatcherFactory));

        $dispatcherFactory->initRoutes('http');
    }

    private function getContainer(array $bindings = []): Container
    {
        $container = new Container(
            new DefinitionSource($bindings)
        );

        ApplicationContext::setContainer($container);

        return $container;
    }
}
