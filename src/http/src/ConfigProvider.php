<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Http;

use Hyperf\HttpServer\CoreMiddleware as HyperfCoreMiddleware;
use Psr\Http\Message\ServerRequestInterface;
use SwooleTW\Hyperf\Http\Contracts\RequestContract;
use SwooleTW\Hyperf\Http\Contracts\ResponseContract;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                RequestContract::class => Request::class,
                ResponseContract::class => Response::class,
                ServerRequestInterface::class => Request::class,
                HyperfCoreMiddleware::class => CoreMiddleware::class,
            ],
        ];
    }
}
