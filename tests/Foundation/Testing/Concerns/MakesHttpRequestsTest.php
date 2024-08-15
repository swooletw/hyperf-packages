<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Tests\Foundation\Testing\Concerns;

use Hyperf\HttpMessage\Server\Response;
use SwooleTW\Hyperf\Foundation\Testing\Http\TestResponse;
use SwooleTW\Hyperf\Foundation\Testing\Stubs\FakeMiddleware;
use SwooleTW\Hyperf\Router\RouteFileCollector;
use SwooleTW\Hyperf\Tests\Foundation\Testing\ApplicationTestCase;

/**
 * @internal
 * @coversNothing
 */
class MakesHttpRequestsTest extends ApplicationTestCase
{
    public function testWithTokenSetsAuthorizationHeader()
    {
        $this->withToken('foobar');
        $this->assertSame('Bearer foobar', $this->defaultHeaders['Authorization']);

        $this->withToken('foobar', 'Basic');
        $this->assertSame('Basic foobar', $this->defaultHeaders['Authorization']);
    }

    public function testWithBasicAuthSetsAuthorizationHeader()
    {
        $callback = function ($username, $password) {
            return base64_encode("{$username}:{$password}");
        };

        $username = 'foo';
        $password = 'bar';

        $this->withBasicAuth($username, $password);
        $this->assertSame('Basic ' . $callback($username, $password), $this->defaultHeaders['Authorization']);

        $password = 'buzz';

        $this->withBasicAuth($username, $password);
        $this->assertSame('Basic ' . $callback($username, $password), $this->defaultHeaders['Authorization']);
    }

    public function testWithoutTokenRemovesAuthorizationHeader()
    {
        $this->withToken('foobar');
        $this->assertSame('Bearer foobar', $this->defaultHeaders['Authorization']);

        $this->withoutToken();
        $this->assertArrayNotHasKey('Authorization', $this->defaultHeaders);
    }

    public function testWithoutAndWithMiddleware()
    {
        $this->assertFalse($this->app->bound('middleware.disable'));

        $this->withoutMiddleware();
        $this->assertTrue($this->app->bound('middleware.disable'));
        $this->assertTrue($this->app->get('middleware.disable'));

        $this->withMiddleware();
        $this->assertFalse($this->app->bound('middleware.disable'));
    }

    public function testWithoutAndWithMiddlewareWithParameter()
    {
        $next = function ($request) {
            return $request;
        };

        $this->assertFalse($this->app->bound(MyMiddleware::class));
        $this->assertSame(
            'fooWithMiddleware',
            $this->app->get(MyMiddleware::class)->handle('foo', $next)
        );

        $this->withoutMiddleware(MyMiddleware::class);
        $this->assertTrue($this->app->bound(MyMiddleware::class));
        $this->assertInstanceOf(FakeMiddleware::class, $this->app->get(MyMiddleware::class));

        $this->withMiddleware(MyMiddleware::class);
        $this->assertFalse($this->app->bound(MyMiddleware::class));
        $this->assertSame(
            'fooWithMiddleware',
            $this->app->get(MyMiddleware::class)->handle('foo', $next)
        );
    }

    public function testWithCookieSetCookie()
    {
        $this->withCookie('foo', 'bar');

        $this->assertCount(1, $this->defaultCookies);
        $this->assertSame('bar', $this->defaultCookies['foo']);
    }

    public function testWithCookiesSetsCookiesAndOverwritesPreviousValues()
    {
        $this->withCookie('foo', 'bar');
        $this->withCookies([
            'foo' => 'baz',
            'new-cookie' => 'new-value',
        ]);

        $this->assertCount(2, $this->defaultCookies);
        $this->assertSame('baz', $this->defaultCookies['foo']);
        $this->assertSame('new-value', $this->defaultCookies['new-cookie']);
    }

    public function testFollowingRedirects()
    {
        $this->app->get(RouteFileCollector::class)
            ->addRouteFile(BASE_PATH . '/routes/test-api.php');

        $response = (new Response())
            ->withStatus(301)
            ->withHeader('Location', 'http://localhost/foo');

        $this->followRedirects(new TestResponse($response))
            ->assertSuccessful()
            ->assertContent('foo');
    }

    public function testGetNotFound()
    {
        $this->get('/foo')
            ->assertNotFound();
    }

    public function testGetFoundRoute()
    {
        $this->app->get(RouteFileCollector::class)
            ->addRouteFile(BASE_PATH . '/routes/test-api.php');

        $this->get('/foo')
            ->assertSuccessFul()
            ->assertContent('foo');
    }
}

class MyMiddleware
{
    public function handle($request, $next)
    {
        return $next($request . 'WithMiddleware');
    }
}
