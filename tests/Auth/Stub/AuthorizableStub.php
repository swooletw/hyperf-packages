<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Tests\Auth\Stub;

use Hyperf\Database\Model\Model;
use SwooleTW\Hyperf\Auth\Access\Authorizable;
use SwooleTW\Hyperf\Auth\Authenticatable;
use SwooleTW\Hyperf\Auth\Contracts\Authenticatable as AuthenticatableContract;
use SwooleTW\Hyperf\Auth\Contracts\Authorizable as AuthorizableContract;

class AuthorizableStub extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable;
    use Authorizable;
}
