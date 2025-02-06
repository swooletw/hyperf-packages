<?php

declare(strict_types=1);

namespace SwooleTW\HttpClient\Events;

use SwooleTW\HttpClient\Request;

class RequestSending
{
    /**
     * Create a new event instance.
     *
     * @param Request $request
     */
    public function __construct(public Request $request)
    {
    }
}
