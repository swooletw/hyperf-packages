<?php

declare(strict_types=1);

namespace Hyperf\Dispatcher;

use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class HttpRequestHandler extends AbstractRequestHandler implements RequestHandlerInterface
{
    /**
     * Handles a request and produces a response.
     * May call other collaborating code to generate the response.
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->handleRequest($request);
    }

    protected function handleRequest($request)
    {
        [$handler, $arguments] = $this->getHandlerAndArguments();

        if (! $handler || ! method_exists($handler, 'process')) {
            throw new InvalidArgumentException('Invalid middleware, it has to provide a process() method.');
        }

        return $handler->process($request, $this->next(), ...$arguments);
    }

    protected function getHandlerAndArguments(): array
    {
        if (! isset($this->middlewares[$this->offset])) {
            return [$this->coreHandler, []];
        }

        $handler = $this->middlewares[$this->offset];

        if (is_string($handler)) {
            [$handler, $arguments] = $this->parseHandleAndArguments($handler);
            $handler = $this->container->get($handler);

            return [$handler, $arguments];
        }

        return [$handler, []];
    }

    protected function parseHandleAndArguments(string $handler): array
    {
        if (! strpos($handler, ':')) {
            return [$handler, []];
        }

        [$handler, $arguments] = explode(':', $handler, 2);
        $arguments = explode(',', $arguments);

        return [$handler, $arguments];
    }
}
