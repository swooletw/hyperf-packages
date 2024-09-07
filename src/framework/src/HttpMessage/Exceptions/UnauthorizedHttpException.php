<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\HttpMessage\Exceptions;

use Throwable;

class UnauthorizedHttpException extends HttpException
{
    public function __construct(string $message = '', int $code = 0, ?Throwable $previous = null, array $headers = [])
    {
        parent::__construct(401, $message, $code, $previous, $headers);
    }
}
