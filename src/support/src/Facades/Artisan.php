<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Support\Facades;

use SwooleTW\Hyperf\Foundation\Console\Contracts\Kernel as KernelContract;

/**
 * @mixin Container
 */
class Artisan extends Facade
{
    protected static function getFacadeAccessor()
    {
        return KernelContract::class;
    }
}
