<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Support\Facades;

use SwooleTW\Hyperf\Encryption\Contracts\Encrypter;

/**
 * @mixin Encrypter
 */
class Crypt extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Encrypter::class;
    }
}
