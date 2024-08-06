<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Tests\Dispatcher;

use Mockery as m;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use SwooleTW\Hyperf\Dispatcher\AdaptedRequestHandler;
use SwooleTW\Hyperf\Dispatcher\Psr15AdapterMiddleware;
use SwooleTW\Hyperf\Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class Psr15AdapterMiddlewareTest extends TestCase
{
    public function testHandle()
    {
        $request = m::mock(ServerRequestInterface::class);

        $middleware = m::mock(MiddlewareInterface::class);
        $middleware->shouldReceive('process')
            ->with($request, m::type(AdaptedRequestHandler::class), 'foo')
            ->once()
            ->andReturn($mockedResponse = m::mock(ResponseInterface::class));

        $response = (new Psr15AdapterMiddleware($middleware))
            ->handle($request, fn () => null, 'foo');

        $this->assertSame($mockedResponse, $response);
    }
}
