<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Foundation\HttpServer;

use Hyperf\Codec\Json;
use Hyperf\Contract\Arrayable;
use Hyperf\Contract\Jsonable;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\CoreMiddleware as HyperfCoreMiddleware;
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
        if (is_string($response)) {
            return $this->response()->addHeader('content-type', 'text/plain')->setBody(new SwooleStream($response));
        }

        if (is_array($response) || $response instanceof Arrayable) {
            return $this->response()
                ->addHeader('content-type', 'application/json')
                ->setBody(new SwooleStream(Json::encode($response)));
        }

        if ($response instanceof Jsonable) {
            return $this->response()
                ->addHeader('content-type', 'application/json')
                ->setBody(new SwooleStream((string) $response));
        }

        if ($this->response()->hasHeader('content-type')) {
            return $this->response()->setBody(new SwooleStream((string) $response));
        }

        return $this->response()->addHeader('content-type', 'text/plain')->setBody(new SwooleStream((string) $response));
    }
}
