<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Support\Facades;

use Hyperf\HttpServer\Contract\RequestInterface;

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
