<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Tests\Router;

use FastRoute\Dispatcher;
use Hyperf\Context\Context;
use Hyperf\HttpServer\Router\Dispatched;
use Hyperf\HttpServer\Router\Handler;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use SwooleTW\Hyperf\Router\RouteContext;

/**
 * @internal
 * @coversNothing
 */
class RouteContextTest extends TestCase
{
    public function tearDown(): void
    {
        Mockery::close();
        Context::set(ServerRequestInterface::class, null);
    }

    public function testGetRouteName()
    {
        $request = Mockery::mock(ServerRequestInterface::class);
        $request->shouldReceive('getAttribute')->with(Dispatched::class)->andReturnUsing(function () {
            return new Dispatched([
                Dispatcher::FOUND,
                new Handler([], '/', ['name' => 'index']),
                [
                    'id' => uniqid(),
                ],
            ]);
        });
        Context::set(ServerRequestInterface::class, $request);
        $context = new RouteContext();
        $this->assertSame('index', $context->getRouteName());
    }
}
