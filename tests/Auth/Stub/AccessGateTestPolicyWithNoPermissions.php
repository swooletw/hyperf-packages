<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Tests\Auth\Stub;

class AccessGateTestPolicyWithNoPermissions
{
    public function edit($user, AccessGateTestDummy $dummy)
    {
        return false;
    }

    public function update($user, AccessGateTestDummy $dummy)
    {
        return false;
    }
}