<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Telescope\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SwooleTW\Hyperf\Http\Contracts\RequestContract;
use SwooleTW\Hyperf\HttpMessage\Exceptions\HttpException;
use SwooleTW\Hyperf\Telescope\Telescope;

class Authorize implements MiddlewareInterface
{
    public function __construct(
        protected RequestContract $request
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (! Telescope::check($this->request)) {
            throw new HttpException(403);
        }

        return $handler->handle($request);
    }
}
