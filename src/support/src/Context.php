<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Support;

use Hyperf\Context\Context as HyperfContext;
use Hyperf\Engine\Coroutine;

class Context extends HyperfContext
{
    protected const DEPTH_KEY = 'di.depth';

    public static function destroyAll(?int $coroutineId = null): void
    {
        if (! $context = Coroutine::getContextFor($coroutineId)) {
            return;
        }

        foreach ($context as $key => $_value) {
            if ($key === static::DEPTH_KEY) {
                continue;
            }
            unset($context[$key]);
        }
    }
}
