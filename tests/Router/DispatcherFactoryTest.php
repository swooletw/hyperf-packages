<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Tests\Router;

use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ContainerInterface;
use Hyperf\HttpServer\Router\RouteCollector;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use SwooleTW\Hyperf\Router\DispatcherFactory;
use SwooleTW\Hyperf\Router\NamedRouteCollector;

/**
 * @internal
 * @coversNothing
 */
class DispatcherFactoryTest extends TestCase
{
    protected function setUp(): void
    {
        ! defined('BASE_PATH') && define('BASE_PATH', __DIR__);
    }

    public function testGetRouter()
    {
        /** @var ContainerInterface|MockInterface */
        $container = Mockery::mock(ContainerInterface::class);

        /** @var NamedRouteCollector|MockInterface */
        $router = Mockery::mock(NamedRouteCollector::class);

        $container
            ->shouldReceive('make')
            ->with(RouteCollector::class, ['server' => 'http'])
            ->once()
            ->andReturn($router);

        ApplicationContext::setContainer($container);

        $factory = new DispatcherFactory($container);

        $this->assertEquals($router, $factory->getRouter('http'));
    }
}
