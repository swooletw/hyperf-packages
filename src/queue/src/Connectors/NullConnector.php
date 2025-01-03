<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Queue\Connectors;

use SwooleTW\Hyperf\Queue\Contracts\Queue;
use SwooleTW\Hyperf\Queue\NullQueue;

class NullConnector implements ConnectorInterface
{
    /**
     * Establish a queue connection.
     */
    public function connect(array $config): Queue
    {
        return new NullQueue();
    }
}
