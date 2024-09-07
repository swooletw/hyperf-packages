<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Support\Contracts;

use Psr\Http\Message\ResponseInterface;
use SwooleTW\Hyperf\Http\Request;

interface Responsable
{
    /**
     * Create an HTTP response that represents the object.
     */
    public function toResponse(Request $request): ResponseInterface;
}
