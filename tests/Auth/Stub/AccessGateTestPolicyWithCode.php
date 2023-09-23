<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Tests\Auth\Stub;

use SwooleTW\Hyperf\Auth\Access\HandlesAuthorization;

class AccessGateTestPolicyWithCode
{
    use HandlesAuthorization;

    public function view($user)
    {
        if (! $user->isAdmin()) {
            return $this->deny('Not allowed to view as it is not published.', 'unpublished');
        }

        return true;
    }
}
