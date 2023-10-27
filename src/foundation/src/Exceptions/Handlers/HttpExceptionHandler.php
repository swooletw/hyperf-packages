<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Foundation\Exceptions\Handlers;

use Hyperf\ExceptionHandler\ExceptionHandler;
use Psr\Http\Message\ResponseInterface;
use SwooleTW\Hyperf\HttpMessage\Exceptions\HttpException;
use SwooleTW\Hyperf\HttpMessage\Exceptions\HttpResponseException;
use Throwable;

class HttpExceptionHandler extends ExceptionHandler
{
    public function handle(Throwable $throwable, ResponseInterface $response): ResponseInterface
    {
        if ($throwable instanceof HttpResponseException) {
            return $throwable->getResponse();
        }

        foreach ($this->getHeaders($throwable) as $key => $value) {
            $response = $response->withHeader($key, $value);
        }

        return $response;
    }

    public function isValid(Throwable $throwable): bool
    {
        return true;
    }

    protected function getHeaders(Throwable $e): array
    {
        return $e instanceof HttpException ? $e->getHeaders() : [];
    }
}
