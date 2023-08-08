<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Support\Facades;

use Hyperf\HttpServer\Contract\ResponseInterface;
use SwooleTW\Hyperf\Support\Facades\Facade;

/*
 * @mixin Accessor
 */
class Response extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ResponseInterface::class;
    }
}
