<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Support\Facades;

use SwooleTW\Hyperf\Http\Contracts\Request as RequestContract;

/*
 * @mixin RequestContract
 */
class Request extends Facade
{
    protected static function getFacadeAccessor()
    {
        return RequestContract::class;
    }
}
