<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Support\Facades;

use League\Flysystem\Filesystem as Accessor;

/**
 * @mixin Accessor
 */
class Filesystem extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Accessor::class;
    }
}
