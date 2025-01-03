<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Bus\Events;

use SwooleTW\Hyperf\Bus\Batch;

class BatchDispatched
{
    public function __construct(
        public Batch $batch
    ) {
    }
}
