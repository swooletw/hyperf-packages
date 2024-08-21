<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Support\Facades;

use Hyperf\View\RenderInterface;

/**
 * @mixin RenderInterface
 */
class View extends Facade
{
    protected static function getFacadeAccessor()
    {
        return RenderInterface::class;
    }
}
