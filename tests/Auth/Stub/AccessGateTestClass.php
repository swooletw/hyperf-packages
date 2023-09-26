<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Tests\Auth\Stub;

class AccessGateTestClass
{
    public function foo($user)
    {
        return $user->getAuthIdentifier() === 1;
    }
}
