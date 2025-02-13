<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Http;

use Closure;
use FastRoute\Dispatcher;
use Hyperf\Context\RequestContext;
use Hyperf\Contract\Arrayable;
use Hyperf\Contract\Jsonable;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\CoreMiddleware as HyperfCoreMiddleware;
use Hyperf\HttpServer\Router\Dispatched;
use Hyperf\Server\Exception\ServerException;
use Hyperf\View\RenderInterface;
use Hyperf\ViewEngine\Contract\ViewInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SwooleTW\Hyperf\HttpMessage\Exceptions\ServerErrorHttpException;
use Swow\Psr7\Message\ResponsePlusInterface;

class CoreMiddleware extends HyperfCoreMiddleware
{
    /**
     * Transfer the non-standard response content to a standard response object.
     *
     * @param null|array|Arrayable|Jsonable|ResponseInterface|string $response
     */
    protected function transferToResponse($response, ServerRequestInterface $request): ResponsePlusInterface
    {
        if ($response instanceof ViewInterface) {
            return $this->response()
                ->setHeader('Content-Type', $this->container->get(RenderInterface::class)->getContentType())
                ->setBody(new SwooleStream((string) $response));
        }

        return parent::transferToResponse($response, $request);
    }

    /**
     * Handle the response when found.
     *
     * @return array|Arrayable|mixed|ResponseInterface|string
     */
    protected function handleFound(Dispatched $dispatched, ServerRequestInterface $request): mixed
    {
        if ($dispatched->handler->callback instanceof Closure) {
            $parameters = $this->parseClosureParameters($dispatched->handler->callback, $dispatched->params);
            $callback = $dispatched->handler->callback;

            return $callback(...$parameters);
        }

        [$controller, $action] = $this->prepareHandler($dispatched->handler->callback);
        $controllerInstance = $this->container->get($controller);
        if (! method_exists($controllerInstance, $action)) {
            throw new ServerErrorHttpException("{$controller}@{$action} does not exist.");
        }

        $parameters = $this->parseMethodParameters($controller, $action, $dispatched->params);
        if (method_exists($controllerInstance, 'callAction')) {
            return $controllerInstance->callAction($action, $parameters);
        }

        return $controllerInstance->{$action}(...$parameters);
    }

    /**
     * Process an incoming server request and return a response, optionally delegating
     * response creation to a handler.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $request = RequestContext::set($request);

        /** @var Dispatched $dispatched */
        $dispatched = $request->getAttribute(Dispatched::class);

        if (! $dispatched instanceof Dispatched) {
            throw new ServerException(sprintf('The dispatched object is not a %s object.', Dispatched::class));
        }

        $response = match ($dispatched->status) {
            Dispatcher::NOT_FOUND => $this->handleNotFound($request),
            Dispatcher::METHOD_NOT_ALLOWED => $this->handleMethodNotAllowed($dispatched->params, $request),
            Dispatcher::FOUND => $this->handleFound($dispatched, $request),
            default => null,
        };

        if (! $response instanceof ResponsePlusInterface) {
            $response = $this->transferToResponse($response, $request);
        }

        return $response->addHeader('Server', 'Laravel Hyperf');
    }
}
