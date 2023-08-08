<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\JWT\Exceptions;

use Exception;

class JWTException extends Exception
{
    /**
     * {@inheritdoc}
     */
    protected $message = 'An error occurred';
}
