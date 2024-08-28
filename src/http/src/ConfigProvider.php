<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Http;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\HttpServer\CoreMiddleware as HyperfCoreMiddleware;
use Hyperf\HttpServer\Request as HyperfRequest;
use Hyperf\HttpServer\Response as HyperfResponse;
use Psr\Http\Message\ServerRequestInterface;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                RequestInterface::class => Request::class,
                ResponseInterface::class => Response::class,
                HyperfCoreMiddleware::class => CoreMiddleware::class,
                ServerRequestInterface::class => Request::class,
                HyperfRequest::class => Request::class,
                HyperfResponse::class => Response::class,
            ],
        ];
    }
}
