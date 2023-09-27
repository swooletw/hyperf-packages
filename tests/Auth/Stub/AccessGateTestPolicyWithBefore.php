<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Tests\Auth\Stub;

class AccessGateTestPolicyWithBefore
{
    public function before($user, $ability)
    {
        return true;
    }

    public function update($user, AccessGateTestDummy $dummy)
    {
        return false;
    }
}
