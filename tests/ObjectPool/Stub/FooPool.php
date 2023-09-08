<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Tests\ObjectPool\Stub;

use stdClass;
use SwooleTW\Hyperf\ObjectPool\ObjectPool;

class FooPool extends ObjectPool
{
    protected function createObject(): object
    {
        return new stdClass();
    }
}
