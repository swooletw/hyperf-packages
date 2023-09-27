<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Tests\Auth\Stub;

use SwooleTW\Hyperf\Auth\Access\HandlesAuthorization;
use SwooleTW\Hyperf\Auth\Contracts\Authenticatable;

class AccessGateTestPolicy
{
    use HandlesAuthorization;

    public function createAny($user, $additional)
    {
        return $additional;
    }

    public function create($user)
    {
        return $user->isAdmin() ? $this->allow() : $this->deny('You are not an admin.');
    }

    public function updateAny($user, AccessGateTestDummy $dummy)
    {
        return ! $user->isAdmin();
    }

    public function update($user, AccessGateTestDummy $dummy)
    {
        return ! $user->isAdmin();
    }

    public function updateDash($user, AccessGateTestDummy $dummy)
    {
        return $user instanceof Authenticatable;
    }
}
