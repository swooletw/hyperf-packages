<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Http;

use Hyperf\HttpServer\CoreMiddleware as HyperfCoreMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SwooleTW\Hyperf\Http\Contracts\RequestContract;

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
}
