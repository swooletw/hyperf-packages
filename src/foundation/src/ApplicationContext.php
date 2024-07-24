<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Foundation;

use Hyperf\Context\ApplicationContext as HyperfApplicationContext;
use SwooleTW\Hyperf\Container\Contracts\Container as ContainerContract;

class ApplicationContext extends HyperfApplicationContext
{
    /**
     * @throws TypeError
     */
    public static function getContainer(): ContainerContract
    {
        return self::$container;
    }
}
