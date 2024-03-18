<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Event;

use Closure;

if (! function_exists('SwooleTW\Hyperf\Event\queueable')) {
    function queueable(Closure $closure): QueuedClosure
    {
        return new QueuedClosure($closure);
    }
}
