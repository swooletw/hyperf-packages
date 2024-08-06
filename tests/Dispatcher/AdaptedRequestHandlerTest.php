<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Tests\Dispatcher;

use Hyperf\Context\Context;
use Mockery as m;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use SwooleTW\Hyperf\Dispatcher\AdaptedRequestHandler;
use SwooleTW\Hyperf\Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class AdaptedRequestHandlerTest extends TestCase
{
    public function tearDown(): void
    {
        parent::tearDown();
        Context::destroy(ResponseInterface::class);
    }

    public function testHandle()
    {
        $mockedRequest = m::mock(ServerRequestInterface::class);
        $mockedResponse = m::mock(ResponseInterface::class);
        $closure = function ($request) use ($mockedRequest, $mockedResponse) {
            $this->assertSame($mockedRequest, $request);

            return $mockedResponse;
        };

        $response = (new AdaptedRequestHandler($closure, true))
            ->handle($mockedRequest);

        $this->assertSame($mockedResponse, $response);
        $this->assertSame($mockedResponse, Context::get(ResponseInterface::class));
    }
}
