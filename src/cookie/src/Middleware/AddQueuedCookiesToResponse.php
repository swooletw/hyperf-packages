<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Cookie\Middleware;

use Hyperf\Collection\Arr;
use Hyperf\Context\Context;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SwooleTW\Hyperf\Cookie\Contracts\Cookie as CookieContract;

class AddQueuedCookiesToResponse implements MiddlewareInterface
{
    public function __construct(
        protected CookieContract $cookie
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (! $cookies = $this->cookie->getQueuedCookies()) {
            return $handler->handle($request);
        }

        $response = Context::get(ResponseInterface::class);
        foreach (Arr::flatten($cookies) as $cookie) {
            $response = $response->withCookie($cookie);
        }

        Context::set(ResponseInterface::class, $response);

        return $handler->handle($request);
    }
}
