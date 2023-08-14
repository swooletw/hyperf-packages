<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Router;

use Hyperf\Context\Context;
use Hyperf\HttpServer\Router\Dispatched;
use Psr\Http\Message\ServerRequestInterface;
use SwooleTW\Hyperf\Router\Exceptions\RequestNotFoundException;

class RouteContext
{
    public function getRouteName(): string
    {
        $dispatched = $this->getRequest()->getAttribute(Dispatched::class);
        if (! $dispatched instanceof Dispatched) {
            throw new RequestNotFoundException('Request is invalid.');
        }

        $handler = $dispatched->handler;
        return $handler->options['name'] ?? $handler->route;
    }

    protected function getRequest(): ServerRequestInterface
    {
        if (! Context::has(ServerRequestInterface::class)) {
            throw new RequestNotFoundException('Request is not found.');
        }

        return Context::get(ServerRequestInterface::class);
    }
}
