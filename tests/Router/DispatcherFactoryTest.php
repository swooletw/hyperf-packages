<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Tests\Router;

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
        $factory = new DispatcherFactory();

        $this->assertInstanceOf(NamedRouteCollector::class, $factory->getRouter('http'));
    }
}
