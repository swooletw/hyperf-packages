<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Support\Facades;

use Hyperf\DbConnection\Db as HyperfDb;
use SwooleTW\Hyperf\Support\Facades\Facade;

/**
 * @mixin Hyperf\DbConnection\Db
 */
class DB extends Facade
{
    protected static function getFacadeAccessor()
    {
        return HyperfDb::class;
    }
}
