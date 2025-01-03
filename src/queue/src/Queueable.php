<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Queue;

use SwooleTW\Hyperf\Bus\Dispatchable;
use SwooleTW\Hyperf\Bus\Queueable as QueueableByBus;

trait Queueable
{
    use Dispatchable;
    use InteractsWithQueue;
    use QueueableByBus;
    use SerializesModels;
}
