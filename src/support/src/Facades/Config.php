<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Support\Facades;

use Hyperf\Contract\ConfigInterface;
use SwooleTW\Hyperf\Support\Facades\Facade;

/**
 * @mixin Accessor
 */
class Config extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ConfigInterface::class;
    }
}
