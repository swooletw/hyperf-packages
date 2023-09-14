<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Tests\Router\Stub;

use SwooleTW\Hyperf\Router\Contracts\UrlRoutable;

class UrlRoutableStub implements UrlRoutable
{
    public function getRouteKey(): string
    {
        return '1';
    }
}
