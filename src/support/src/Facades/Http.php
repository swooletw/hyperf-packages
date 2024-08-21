<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Support\Facades;

use FriendsOfHyperf\Http\Client\Factory as Accessor;

/**
 * @mixin Accessor
 */
class Http extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Accessor::class;
    }
}
