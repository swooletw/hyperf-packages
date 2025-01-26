<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Foundation\Http;

use Hyperf\Context\RequestContext;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Coordinator\Constants;
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\HttpMessage\Server\Request;
use Hyperf\HttpMessage\Server\Response;
use Hyperf\HttpMessage\Upload\UploadedFile as HyperfUploadedFile;
use Hyperf\HttpServer\Event\RequestHandled;
use Hyperf\HttpServer\Event\RequestReceived;
use Hyperf\HttpServer\Event\RequestTerminated;
use Hyperf\HttpServer\Server as HyperfServer;
use Hyperf\Support\SafeCaller;
use Psr\Http\Message\ResponseInterface;
use SwooleTW\Hyperf\Foundation\Exceptions\Handlers\HttpExceptionHandler;
use SwooleTW\Hyperf\Foundation\Http\Contracts\MiddlewareContract;
use SwooleTW\Hyperf\Foundation\Http\Traits\HasMiddleware;
use SwooleTW\Hyperf\Http\UploadedFile;
use Throwable;

use function Hyperf\Coroutine\defer;

class Kernel extends HyperfServer implements MiddlewareContract
{
    use HasMiddleware;

    public function initCoreMiddleware(string $serverName): void
    {
        $this->serverName = $serverName;
        $this->coreMiddleware = $this->createCoreMiddleware();

        $config = $this->container->get(ConfigInterface::class);
        $this->exceptionHandlers = $config->get('exceptions.handler.' . $serverName, $this->getDefaultExceptionHandler());

        $this->initOption();
    }

    public function onRequest($swooleRequest, $swooleResponse): void
    {
        try {
            CoordinatorManager::until(Constants::WORKER_START)->yield();

            [$request, $response] = $this->initRequestAndResponse($swooleRequest, $swooleResponse);

            // Trim the trailing slashes of the path.
            $uri = $request->getUri();
            if ($uri->getPath() !== '/') {
                $request->setUri(
                    $uri->setPath(rtrim($uri->getPath(), '/'))
                );
            }

            // Convert Hyperf's uploaded files to Laravel style UploadedFile
            if ($uploadedFiles = $request->getUploadedFiles()) {
                $request = $request->withUploadedFiles(array_map(function (HyperfUploadedFile $uploadedFile) {
                    return UploadedFile::createFromBase($uploadedFile);
                }, $uploadedFiles));

                RequestContext::set($request);
            }

            $this->dispatchRequestReceivedEvent(
                $request = $this->coreMiddleware->dispatch($request),
                $response
            );

            $response = $this->dispatcher->dispatch(
                $request,
                $this->getMiddlewareForRequest($request),
                $this->coreMiddleware
            );
        } catch (Throwable $throwable) {
            $response = $this->getResponseForException($throwable);
        } finally {
            if (isset($request)) {
                /* @phpstan-ignore-next-line */
                $this->dispatchRequestHandledEvents($request, $response);
            }

            if (! isset($response) || ! $response instanceof ResponseInterface) {
                return;
            }

            // Send the Response to client.
            if (isset($request) && $request->getMethod() === 'HEAD') {
                $this->responseEmitter->emit($response, $swooleResponse, false);
            } else {
                $this->responseEmitter->emit($response, $swooleResponse);
            }
        }
    }

    protected function dispatchRequestReceivedEvent(Request $request, Response $response): void
    {
        if (! $this->option?->isEnableRequestLifecycle()) {
            return;
        }

        $this->event?->dispatch(new RequestReceived(
            request: $request,
            response: $response,
            server: $this->serverName
        ));
    }

    protected function dispatchRequestHandledEvents(Request $request, Response $response, ?Throwable $throwable = null): void
    {
        if (! $this->option?->isEnableRequestLifecycle()) {
            return;
        }

        defer(fn () => $this->event?->dispatch(new RequestTerminated(
            request: $request,
            response: $response,
            exception: $throwable,
            server: $this->serverName
        )));

        $this->event?->dispatch(new RequestHandled(
            request: $request,
            response: $response,
            exception: $throwable,
            server: $this->serverName
        ));
    }

    protected function getResponseForException(Throwable $throwable): Response
    {
        return $this->container->get(SafeCaller::class)->call(function () use ($throwable) {
            return $this->exceptionHandlerDispatcher->dispatch($throwable, $this->exceptionHandlers);
        }, static function () {
            return (new Response())->withStatus(400);
        });
    }

    protected function getDefaultExceptionHandler(): array
    {
        return [
            HttpExceptionHandler::class,
        ];
    }
}
