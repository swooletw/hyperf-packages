<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Support;

use Hyperf\Context\Context as HyperfContext;
use Hyperf\Engine\Coroutine;

class Context extends HyperfContext
{
    protected const DEPTH_KEY = 'di.depth';

    public static function copyFromNonCoroutine(array $keys = [], ?int $coroutineId = null): void
    {
        if (is_null($context = Coroutine::getContextFor($coroutineId))) {
            return;
        }

        if ($keys) {
            $map = array_intersect_key(static::$nonCoContext, array_flip($keys));
        } else {
            $map = static::$nonCoContext;
        }

        $context->exchangeArray($map);
    }

    public static function destroyAll(?int $coroutineId = null): void
    {
        // Clear non-coroutine context in non-coroutine environment.
        if (! $coroutineId) {
            static::$nonCoContext = [];
            return;
        }

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