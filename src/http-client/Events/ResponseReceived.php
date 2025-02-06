<?php

declare(strict_types=1);

namespace SwooleTW\HttpClient\Events;

use SwooleTW\HttpClient\Request;
use SwooleTW\HttpClient\Response;

class ResponseReceived
{
    /**
     * Create a new event instance.
     */
    public function __construct(public Request $request, public Response $response)
    {
    }
}
