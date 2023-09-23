<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Tests\Auth\Stub;

use SwooleTW\Hyperf\Auth\Access\Response;

class AccessGateTestPolicyWithDeniedResponseObject
{
    public function create()
    {
        return Response::deny('Not allowed.', 'some_code');
    }
}
