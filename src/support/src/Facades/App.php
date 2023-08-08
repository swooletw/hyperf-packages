<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Support\Facades;

use SwooleTW\Hyperf\Support\Facades\Facade;

/**
 * @mixin Container
 */
class App extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'app';
    }
}
