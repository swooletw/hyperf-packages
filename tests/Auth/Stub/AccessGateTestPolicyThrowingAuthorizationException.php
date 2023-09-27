<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Tests\Auth\Stub;

use SwooleTW\Hyperf\Auth\Access\AuthorizationException;

class AccessGateTestPolicyThrowingAuthorizationException
{
    public function create()
    {
        throw new AuthorizationException('Not allowed.', 'some_code');
    }
}
