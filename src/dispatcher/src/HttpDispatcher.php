<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Dispatcher;

use Hyperf\Dispatcher\AbstractDispatcher;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;

class HttpDispatcher extends AbstractDispatcher
{
    public function __construct(private ContainerInterface $container) {}

    public function dispatch(...$params): ResponseInterface
    {
        /**
         * @param RequestInterface $request
         * @param array $middlewares
         * @param MiddlewareInterface $coreHandler
         */
        [$request, $middlewares, $coreHandler] = $params;

        $requestHandler = new HttpRequestHandler($middlewares, $coreHandler, $this->container);

        return $requestHandler->handle($request);
    }
}
