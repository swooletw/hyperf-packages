<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Tests\Routing;

use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ContainerInterface;
use Hyperf\HttpServer\Router\DispatcherFactory as HyperfDispatcherFactory;
use Hyperf\HttpServer\Router\Router;
use InvalidArgumentException;
use Mockery;
use PHPUnit\Framework\TestCase;
use SwooleTW\Hyperf\Routing\DispatcherFactory;
use SwooleTW\Hyperf\Routing\UrlGenerator;

/**
 * @internal
 * @coversNothing
 */
class UrlGeneratorTest extends TestCase
{
    private ContainerInterface $container;

    protected function setUp(): void
    {
        ! defined('BASE_PATH') && define('BASE_PATH', __DIR__);

        $this->container = Mockery::mock(ContainerInterface::class);

        $this->container->shouldReceive('get')
            ->with(HyperfDispatcherFactory::class)
            ->andReturn(new DispatcherFactory());

        ApplicationContext::setContainer($this->container);
    }

    public function testRoute()
    {
        Router::get('/foo', 'Handler::Foo', ['as' => 'foo']);
        Router::get('/foo/{bar}', 'Handler::Bar', ['as' => 'bar']);
        Router::get('/foo/{bar}/baz', 'Handler::Bar', ['as' => 'baz']);

        $urlGenerator = new UrlGenerator($this->container);

        $this->assertEquals('/foo', $urlGenerator->route('foo'));
        $this->assertEquals('/foo?bar=1', $urlGenerator->route('foo', ['bar' => 1]));
        $this->assertEquals('/foo?bar=1&baz=2', $urlGenerator->route('foo', ['bar' => 1, 'baz' => 2]));
        $this->assertEquals('/foo/1', $urlGenerator->route('bar', ['bar' => 1]));
        $this->assertEquals('/foo/1?baz=2', $urlGenerator->route('bar', ['bar' => 1, 'baz' => 2]));
        $this->assertEquals('/foo/1/baz', $urlGenerator->route('baz', ['bar' => 1]));
        $this->assertEquals('/foo/1/baz?baz=2', $urlGenerator->route('baz', ['bar' => 1, 'baz' => 2]));
    }

    public function testRouteWithNotDefined()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Route [foo] not defined.');

        $urlGenerator = new UrlGenerator($this->container);
        $urlGenerator->route('foo');
    }

    public function testRouteWithGroup()
    {
        Router::addGroup('/foo', function () {
            Router::get('/', 'Handler::Foo', ['as' => 'foo']);
            Router::addGroup('/bar', function () {
                Router::get('/', 'Handler::Bar', ['as' => 'bar']);
                Router::addGroup('/baz', function () {
                    Router::get('/', 'Handler::Baz', ['as' => 'baz']);
                });
            });
        });

        $urlGenerator = new UrlGenerator($this->container);

        $this->assertEquals('/foo', $urlGenerator->route('foo'));
        $this->assertEquals('/foo/bar', $urlGenerator->route('bar'));
        $this->assertEquals('/foo/bar/baz', $urlGenerator->route('baz'));
    }
}
