<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Support\Facades;

use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use SwooleTW\Hyperf\Support\Facades\Facade;

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
