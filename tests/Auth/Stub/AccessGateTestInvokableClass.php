<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Tests\Auth\Stub;

class AccessGateTestInvokableClass
{
    public function __invoke($user)
    {
        return $user->getAuthIdentifier() === 1;
    }
}
