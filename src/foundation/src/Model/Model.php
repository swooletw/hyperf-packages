<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Foundation\Model;

use Hyperf\DbConnection\Model\Model as BaseModel;

abstract class Model extends BaseModel
{
    protected ?string $connection = null;
}
