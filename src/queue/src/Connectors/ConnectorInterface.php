<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Queue\Connectors;

use SwooleTW\Hyperf\Queue\Contracts\Queue;

interface ConnectorInterface
{
    /**
     * Establish a queue connection.
     */
    public function connect(array $config): Queue;
}
