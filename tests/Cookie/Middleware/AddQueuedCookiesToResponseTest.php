<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Tests\Cookie;

use Mockery as m;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SwooleTW\Hyperf\Cookie\Contracts\Cookie as ContractsCookie;
use SwooleTW\Hyperf\Cookie\Middleware\AddQueuedCookiesToResponse;
use SwooleTW\Hyperf\Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class AddQueuedCookiesToResponseTest extends TestCase
{
    public function testProcess()
    {
        $cookie = m::mock(ContractsCookie::class);
        $cookie->shouldReceive('getQueuedCookies')->once()->andReturn(['cookie']);

        $request = m::mock(ServerRequestInterface::class);
        $response = m::mock(ResponseInterface::class);
        $response->shouldReceive('withCookie')->once()->with('cookie')->andReturnSelf();

        $handler = m::mock(RequestHandlerInterface::class);
        $handler->shouldReceive('handle')->with($request)->once()->andReturn($response);

        $middle = new AddQueuedCookiesToResponse($cookie);

        $middle->process($request, $handler);
    }
}
