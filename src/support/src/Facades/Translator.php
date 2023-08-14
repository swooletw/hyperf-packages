<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Support\Facades;

use Hyperf\Contract\TranslatorInterface;

/**
 * @mixin TranslatorInterface
 */
class Translator extends Facade
{
    protected static function getFacadeAccessor()
    {
        return TranslatorInterface::class;
    }
}
