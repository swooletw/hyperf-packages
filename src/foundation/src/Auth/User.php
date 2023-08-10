<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Foundation\Auth;

use App\Models\Model;
use SwooleTW\Hyperf\Auth\Authenticatable;
use SwooleTW\Hyperf\Auth\Contracts\Authenticatable as AuthenticatableContract;

class User extends Model implements AuthenticatableContract
{
    use Authenticatable;
}
