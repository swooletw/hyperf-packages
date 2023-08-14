<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Tests\Router;

use Hyperf\HttpServer\Router\DispatcherFactory;
use Mockery;
use Monolog\Test\TestCase;
use SwooleTW\Hyperf\Router\RouteCollector;
use SwooleTW\Hyperf\Tests\Router\Stub\ContainerStub;

/**
 * @internal
 * @coversNothing
 */
class RouteCollectorTest extends TestCase
{
    public function tearDown(): void
    {
        Mockery::close();
    }

    public function testGetPath()
    {
        $container = ContainerStub::getContainer();
        $collector = new RouteCollector(
            $container,
            $container->get(DispatcherFactory::class)
        );
        $this->assertSame('/', $collector->getPath('index'));
        $this->assertSame('/user/123', $collector->getPath('user.info', ['id' => 123]));
        $this->assertSame('/user', $collector->getPath('user.list'));
        $this->assertSame('/author/Hyperf/book/PHP', $collector->getPath('author.book', ['user' => 'Hyperf', 'name' => 'PHP']));
        $this->assertSame('/author/Hyperf', $collector->getPath('author.role', ['user' => 'Hyperf']));
        $this->assertSame('/author/Hyperf/role/master', $collector->getPath('author.role', ['user' => 'Hyperf', 'name' => 'master']));
        $this->assertSame('/book', $collector->getPath('book.author'));
    }
}
