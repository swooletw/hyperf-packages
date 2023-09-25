<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Foundation\Auth;

use SwooleTW\Hyperf\Auth\Authenticatable;
use SwooleTW\Hyperf\Auth\Contracts\Authenticatable as AuthenticatableContract;
use SwooleTW\Hyperf\Foundation\Model\Model;

class User extends Model implements AuthenticatableContract
{
    use Authenticatable;
}
