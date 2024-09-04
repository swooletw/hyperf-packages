<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Http;

use Hyperf\Contract\Arrayable;
use Hyperf\Contract\Jsonable;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\CoreMiddleware as HyperfCoreMiddleware;
use Hyperf\View\RenderInterface;
use Hyperf\ViewEngine\Contract\ViewInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
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
}
