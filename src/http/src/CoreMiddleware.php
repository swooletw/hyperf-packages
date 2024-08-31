<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Http;

use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\CoreMiddleware as HyperfCoreMiddleware;
use Hyperf\View\RenderInterface;
use Hyperf\ViewEngine\Contract\ViewInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SwooleTW\Hyperf\Http\Contracts\RequestContract;
use Swow\Psr7\Message\ResponsePlusInterface;

class CoreMiddleware extends HyperfCoreMiddleware
{
    /**
     * Process an incoming server request and return a response, optionally delegating
     * response creation to a handler.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($request instanceof RequestContract) {
            $request = $request->getPsr7Request();
        }

        return parent::process($request, $handler);
    }

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
}
