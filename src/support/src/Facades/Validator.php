<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Support\Facades;

use Hyperf\Validation\Contract\ValidatorFactoryInterface;

/**
 * @mixin ValidatorFactoryInterface
 */
class Validator extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ValidatorFactoryInterface::class;
    }
}
