<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Tests\Router;

use Hyperf\Config\Config;
use Hyperf\Context\ApplicationContext;
use Hyperf\Context\Context;
use Hyperf\Context\RequestContext;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\ContainerInterface;
use Hyperf\Contract\SessionInterface;
use Hyperf\HttpMessage\Server\Request as ServerRequest;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Request;
use Hyperf\HttpServer\Router\DispatcherFactory as HyperfDispatcherFactory;
use InvalidArgumentException;
use Mockery;
use Mockery\MockInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionMethod;
use SwooleTW\Hyperf\Router\DispatcherFactory;
use SwooleTW\Hyperf\Router\NamedRouteCollector;
use SwooleTW\Hyperf\Router\UrlGenerator;
use SwooleTW\Hyperf\Tests\Router\Stub\UrlRoutableStub;
use SwooleTW\Hyperf\Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class UrlGeneratorTest extends TestCase
{
    /**
     * @var ContainerInterface|MockInterface
     */
    private ContainerInterface $container;

    /**
     * @var MockInterface|NamedRouteCollector
     */
    private NamedRouteCollector $router;

    protected function setUp(): void
    {
        $this->mockContainer();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        Context::destroy('__request.root.uri');
        Context::destroy(ServerRequestInterface::class);
    }

    public function testRoute()
    {
        if (! defined('BASE_PATH')) {
            $this->markTestSkipped('skip it because DispatcherFactory in hyperf is dirty.');
        }

        $this->mockRouter();

        $this->router
            ->shouldReceive('getNamedRoutes')
            ->andReturn([
                'foo' => ['/foo'],
                'bar' => ['/foo/', ['bar', '[^/]+']],
                'baz' => ['/foo/', ['bar', '[^/]+'], '/baz'],
            ]);

        $urlGenerator = new UrlGenerator($this->container);

        $this->assertEquals('/foo', $urlGenerator->route('foo'));
        $this->assertEquals('/foo?bar=1', $urlGenerator->route('foo', ['bar' => 1]));
        $this->assertEquals('/foo?bar=1&baz=2', $urlGenerator->route('foo', ['bar' => 1, 'baz' => 2]));
        $this->assertEquals('/foo', $urlGenerator->route('bar'));
        $this->assertEquals('/foo/1', $urlGenerator->route('bar', ['bar' => 1]));
        $this->assertEquals('/foo/1?baz=2', $urlGenerator->route('bar', ['bar' => 1, 'baz' => 2]));
        $this->assertEquals('/foo/1/baz', $urlGenerator->route('baz', ['bar' => 1]));
        $this->assertEquals('/foo/1/baz?baz=2', $urlGenerator->route('baz', ['bar' => 1, 'baz' => 2]));
    }

    public function testRouteWithNotDefined()
    {
        if (! defined('BASE_PATH')) {
            $this->markTestSkipped('skip it because DispatcherFactory in hyperf is dirty.');
        }

        $this->mockRouter();

        $this->router
            ->shouldReceive('getNamedRoutes')
            ->andReturn([]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Route [foo] not defined.');

        $urlGenerator = new UrlGenerator($this->container);
        $urlGenerator->route('foo');
    }

    public function testTo()
    {
        $this->mockRequest();

        $urlGenerator = new UrlGenerator($this->container);

        $this->assertEquals('http://example.com/foo', $urlGenerator->to('foo'));
    }

    public function testToWithValidUrl()
    {
        $this->mockRequest();

        $urlGenerator = new UrlGenerator($this->container);

        $this->assertEquals('http://example.com', $urlGenerator->to('http://example.com'));
        $this->assertEquals('https://example.com', $urlGenerator->to('https://example.com'));
        $this->assertEquals('//example.com', $urlGenerator->to('//example.com'));
        $this->assertEquals('mailto:hello@example.com', $urlGenerator->to('mailto:hello@example.com'));
        $this->assertEquals('tel:1234567890', $urlGenerator->to('tel:1234567890'));
        $this->assertEquals('sms:1234567890', $urlGenerator->to('sms:1234567890'));
        $this->assertEquals('#foo', $urlGenerator->to('#foo'));
        $this->assertEquals('ftp://example.com', $urlGenerator->to('ftp://example.com'));
    }

    public function testToWithExtra()
    {
        $this->mockRequest();

        $urlGenerator = new UrlGenerator($this->container);

        $this->assertEquals('http://example.com/foo/bar/baz', $urlGenerator->to('foo', ['bar', 'baz']));
        $this->assertEquals('http://example.com/foo/%3F/%3D', $urlGenerator->to('foo', ['?', '=']));
        $this->assertEquals('http://example.com/foo/1', $urlGenerator->to('foo', [new UrlRoutableStub()]));
    }

    public function testToWithSecure()
    {
        $this->mockRequest();

        $urlGenerator = new UrlGenerator($this->container);

        $this->assertEquals('https://example.com/foo', $urlGenerator->to('foo', secure: true));
    }

    public function testToWithRootUrlCache()
    {
        $this->mockRequest();

        $urlGenerator = new UrlGenerator($this->container);

        $this->assertEquals('http://example.com/foo', $urlGenerator->to('foo'));
        $this->assertEquals('http://example.com', Context::get('__request.root.uri')->toString());
    }

    public function testSecure()
    {
        $this->mockRequest();

        $urlGenerator = new UrlGenerator($this->container);

        $this->assertEquals('https://example.com/foo', $urlGenerator->secure('foo'));
        $this->assertEquals('https://example.com/foo/bar', $urlGenerator->secure('foo', ['bar']));
    }

    public function testNoRequestContext()
    {
        $urlGenerator = new UrlGenerator($this->container);

        $this->container->shouldReceive('get')->with(ConfigInterface::class)->andReturn(new Config([
            'app' => [
                'url' => 'http://localhost',
            ],
        ]));

        $this->assertEquals('http://localhost/foo', $urlGenerator->to('foo'));
    }

    public function testFull()
    {
        $this->mockRequest();

        $urlGenerator = new UrlGenerator($this->container);

        $this->assertEquals('http://example.com/foo?bar=baz#boom', $urlGenerator->full());
    }

    public function testCurrent()
    {
        $this->mockRequest();

        $urlGenerator = new UrlGenerator($this->container);

        $this->assertEquals('http://example.com/foo', $urlGenerator->current());
    }

    public function testPrevious()
    {
        $urlGenerator = new UrlGenerator($this->container);

        // Test with referer header
        RequestContext::set(
            new ServerRequest(
                'GET',
                'http://example.com/foo',
                ['referer' => 'http://example.com/previous']
            )
        );
        $this->container->shouldReceive('has')
            ->with(SessionInterface::class)
            ->andReturnFalse();

        $this->assertEquals('http://example.com/previous', $urlGenerator->previous());

        // Test without referer header and no session
        RequestContext::set(
            new ServerRequest(
                'GET',
                'http://example.com/foo'
            )
        );

        $this->assertEquals('http://example.com', $urlGenerator->previous());

        // Test with fallback
        $this->assertEquals('http://example.com/fallback', $urlGenerator->previous('fallback'));
    }

    public function testPreviousPath()
    {
        $urlGenerator = new UrlGenerator($this->container);

        // Mock RequestContext
        RequestContext::set(
            new ServerRequest(
                'GET',
                'http://example.com/foo',
                ['referer' => 'http://example.com/previous/path']
            )
        );

        // Mock ConfigInterface for app.url
        $mockConfig = Mockery::mock(ConfigInterface::class);
        $mockConfig->shouldReceive('get')
            ->with('app.url')
            ->andReturn('http://example.com');
        $this->container->shouldReceive('get')
            ->with(ConfigInterface::class)
            ->andReturn($mockConfig);

        // Test with referer header
        $this->container->shouldReceive('has')
            ->with(SessionInterface::class)
            ->andReturnFalse();

        $this->assertEquals('/previous/path', $urlGenerator->previousPath());

        // Test without referer header and no session
        RequestContext::set(
            new ServerRequest(
                'GET',
                'http://example.com/foo'
            )
        );

        $this->assertEquals('/', $urlGenerator->previousPath());

        // Test with fallback
        $this->assertEquals('/fallback', $urlGenerator->previousPath('fallback'));
    }

    public function testIsValidUrl()
    {
        $urlGenerator = new UrlGenerator($this->container);
        $method = new ReflectionMethod(UrlGenerator::class, 'isValidUrl');
        $method->setAccessible(true);

        $this->assertTrue($method->invoke($urlGenerator, 'http://example.com'));
        $this->assertTrue($method->invoke($urlGenerator, 'https://example.com'));
        $this->assertTrue($method->invoke($urlGenerator, '//example.com'));
        $this->assertTrue($method->invoke($urlGenerator, 'mailto:test@example.com'));
        $this->assertTrue($method->invoke($urlGenerator, 'tel:1234567890'));
        $this->assertTrue($method->invoke($urlGenerator, 'sms:1234567890'));
        $this->assertTrue($method->invoke($urlGenerator, '#anchor'));
        $this->assertFalse($method->invoke($urlGenerator, 'not-a-url'));
    }

    public function testFormatParameters()
    {
        $urlGenerator = new UrlGenerator($this->container);
        $method = new ReflectionMethod(UrlGenerator::class, 'formatParameters');
        $method->setAccessible(true);

        $urlRoutable = new UrlRoutableStub();
        $parameters = ['key' => $urlRoutable, 'normal' => 'value'];

        $result = $method->invoke($urlGenerator, $parameters);
        $this->assertEquals(['key' => '1', 'normal' => 'value'], $result);
    }

    private function mockContainer()
    {
        /** @var ContainerInterface|MockInterface */
        $container = Mockery::mock(ContainerInterface::class);

        $container->shouldReceive('get')
            ->with(RequestInterface::class)
            ->andReturn(new Request());

        ApplicationContext::setContainer($container);

        $this->container = $container;
    }

    private function mockRouter()
    {
        /** @var DispatcherFactory|MockInterface */
        $factory = Mockery::mock(DispatcherFactory::class);

        /** @var MockInterface|NamedRouteCollector */
        $router = Mockery::mock(NamedRouteCollector::class);

        $this->container
            ->shouldReceive('get')
            ->with(HyperfDispatcherFactory::class)
            ->andReturn($factory);

        $factory
            ->shouldReceive('getRouter')
            ->with('http')
            ->andReturn($router);

        $this->router = $router;
    }

    private function mockRequest()
    {
        RequestContext::set(new ServerRequest('GET', 'http://example.com/foo?bar=baz#boom'));
    }
}
