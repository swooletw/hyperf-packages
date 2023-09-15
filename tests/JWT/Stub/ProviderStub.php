<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Tests\JWT\Stub;

use SwooleTW\Hyperf\JWT\Providers\Provider;

class ProviderStub extends Provider
{
    protected function isAsymmetric(): bool
    {
        return false;
    }
}
