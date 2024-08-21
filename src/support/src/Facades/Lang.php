<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Support\Facades;

use Hyperf\Contract\TranslatorLoaderInterface as Accessor;

/**
 * @mixin Accessor
 */
class Lang extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Accessor::class;
    }
}
