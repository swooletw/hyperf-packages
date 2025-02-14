<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\HttpClient\Events;

use SwooleTW\Hyperf\HttpClient\Request;
use SwooleTW\Hyperf\HttpClient\Response;

class ResponseReceived
{
    /**
     * Create a new event instance.
     */
    public function __construct(
        public Request $request,
        public Response $response
    ) {
    }
}
