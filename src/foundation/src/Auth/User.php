<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Foundation\Auth;

use SwooleTW\Hyperf\Auth\Access\Authorizable;
use SwooleTW\Hyperf\Auth\Authenticatable;
use SwooleTW\Hyperf\Auth\Contracts\Authenticatable as AuthenticatableContract;
use SwooleTW\Hyperf\Auth\Contracts\Authorizable as AuthorizableContract;
use SwooleTW\Hyperf\Database\Eloquent\Model;

class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable;
    use Authorizable;
}
