<?php

namespace SwooleTW\Hyperf\Tests\Router\Stub;

use SwooleTW\Hyperf\router\src\Contracts\UrlRoutable;

class UrlRoutableStub implements UrlRoutable
{
    public function getRouteKey(): string
    {
        return '1';
    }
}
