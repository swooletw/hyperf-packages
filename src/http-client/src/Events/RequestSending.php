<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\HttpClient\Events;

use SwooleTW\Hyperf\HttpClient\Request;

class RequestSending
{
    /**
     * Create a new event instance.
     */
    public function __construct(public Request $request)
    {
    }
}
