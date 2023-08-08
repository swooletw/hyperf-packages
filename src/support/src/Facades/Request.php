<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Support\Facades;

use Hyperf\HttpServer\Contract\RequestInterface;
use SwooleTW\Hyperf\Support\Facades\Facade;

/*
 * @mixin Accessor
 */
class Request extends Facade
{
    protected static function getFacadeAccessor()
    {
        return RequestInterface::class;
    }
}
