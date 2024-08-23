<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Database\Eloquent;

use Hyperf\DbConnection\Model\Model as BaseModel;
use SwooleTW\Hyperf\Router\Contracts\UrlRoutable;

abstract class Model extends BaseModel implements UrlRoutable
{
    protected ?string $connection = null;

    public function resolveRouteBinding($value)
    {
        /** @phpstan-ignore-next-line */
        return $this->where($this->getRouteKeyName(), $value)->firstOrFail();
    }
}
