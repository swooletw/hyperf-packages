<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Foundation\Exceptions\Handlers;

use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Psr\Http\Message\ResponseInterface;
use SwooleTW\Hyperf\HttpMessage\Exceptions\HttpException;
use SwooleTW\Hyperf\HttpMessage\Exceptions\HttpResponseException;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Throwable;

class AppExceptionHandler extends ExceptionHandler
{
    protected array $statusCodeMapping = [
        \Hyperf\Validation\UnauthorizedException::class => 403,
        \Hyperf\Validation\ValidationException::class => 422,
    ];

    public function handle(Throwable $throwable, ResponseInterface $response): ResponseInterface
    {
        if ($throwable instanceof HttpResponseException) {
            return $throwable->getResponse();
        }

        return $this->prepareResponse($throwable, $response);
    }

    public function isValid(Throwable $throwable): bool
    {
        return true;
    }

    protected function prepareResponse(Throwable $throwable, ResponseInterface $response): ResponseInterface
    {
        $data = [
            'code' => $code = $this->getStatusCode($throwable),
            'exception' => get_class($throwable),
            'message' => $this->getMessage($throwable),
        ];

        if (environment()->isDebug() && ! environment()->isTesting()) {
            $data['trace'] = FlattenException::createFromThrowable($throwable)->getTrace();
        }

        $response = $response
            ->withStatus($code)
            ->withHeader('Content-Type', 'application/json')
            ->withBody(new SwooleStream(json_encode($data)));

        foreach ($this->getHeaders($throwable) as $key => $value) {
            $response = $response->withHeader($key, $value);
        }

        return $response;
    }

    protected function getStatusCode(Throwable $e): int
    {
        if ($statusCode = $this->statusCodeMapping[get_class($e)] ?? null) {
            return $statusCode;
        }

        return $e instanceof HttpException ? $e->getStatusCode() : 400;
    }

    protected function getHeaders(Throwable $e): array
    {
        return $e instanceof HttpException ? $e->getHeaders() : [];
    }

    protected function getMessage(Throwable $e): string
    {
        $message = $e->getMessage() ?? null;

        if (! $message && $previous = $e->getPrevious()) {
            $message = $previous->getMessage() ?? null;
        }

        return $message ?: 'No Message';
    }
}
