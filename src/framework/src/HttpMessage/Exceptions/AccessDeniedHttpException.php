<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\HttpMessage\Exceptions;

use Throwable;

class AccessDeniedHttpException extends HttpException
{
    public function __construct(string $message = '', ?Throwable $previous = null, int $code = 0, array $headers = [])
    {
        parent::__construct(403, $message, $code, $previous, $headers);
    }
}
