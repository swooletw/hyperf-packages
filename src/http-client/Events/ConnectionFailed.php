<?php

declare(strict_types=1);

namespace SwooleTW\HttpClient\Events;

use SwooleTW\HttpClient\ConnectionException;
use SwooleTW\HttpClient\Request;

class ConnectionFailed
{
    /**
     * Create a new event instance.
     */
    public function __construct(public Request $request, public ConnectionException $exception)
    {
    }
}
