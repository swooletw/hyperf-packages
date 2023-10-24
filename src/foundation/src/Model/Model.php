<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Foundation\Model;

use Hyperf\DbConnection\Model\Model as BaseModel;
use SwooleTW\Hyperf\Router\Contracts\UrlRoutable;

abstract class Model extends BaseModel implements UrlRoutable
{
    protected ?string $connection = null;

    public function resolveRouteBinding($value)
    {
        return $this->where($this->getRouteKeyName(), $value)->firstOrFail();
    }
}
