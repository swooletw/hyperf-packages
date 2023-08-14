<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Support\Facades;

use Psr\Log\LoggerInterface;

class Log extends Facade
{
    protected static function getFacadeAccessor()
    {
        return LoggerInterface::class;
    }
}
