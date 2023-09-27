<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Tests\Auth\Stub;

use SwooleTW\Hyperf\Auth\Contracts\Authenticatable;

class AccessGateTestPolicyWithNonGuestBefore
{
    public function before(Authenticatable $user)
    {
        $_SERVER['__hyperf.testBefore'] = true;
    }

    public function edit(?Authenticatable $user, AccessGateTestDummy $dummy)
    {
        return true;
    }

    public function update($user, AccessGateTestDummy $dummy)
    {
        return true;
    }
}
