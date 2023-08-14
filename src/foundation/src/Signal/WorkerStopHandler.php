<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Foundation\Signal;

use Hyperf\Engine\Coroutine;
use Hyperf\Signal\Handler\WorkerStopHandler as HyperfWorkerStopHandler;

class WorkerStopHandler extends HyperfWorkerStopHandler
{
    public function handle(int $signal): void
    {
        Coroutine::set([
            'enable_deadlock_check' => false,
        ]);

        parent::handle($signal);
    }
}
