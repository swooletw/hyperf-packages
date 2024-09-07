<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Http\Contracts;

use Hyperf\Contract\Arrayable;
use Hyperf\Contract\Jsonable;
use Hyperf\HttpServer\Contract\ResponseInterface as HyperfResponseInterface;
use Psr\Http\Message\ResponseInterface;

interface ResponseContract extends HyperfResponseInterface
{
    /**
     * Create a new response instance.
     */
    public function make(mixed $content = '', int $status = 200, array $headers = []): ResponseInterface;

    /**
     * Create a new "no content" response.
     */
    public function noContent(int $status = 204, array $headers = []): ResponseInterface;

    /**
     * Create a new response for a given view.
     */
    public function view(string $view, array $data = [], int $status = 200, array $headers = []): ResponseInterface;

    /**
     * Format data to JSON and return data with Content-Type:application/json header.
     *
     * @param array|Arrayable|Jsonable $data
     */
    public function json($data, int $status = 200, array $headers = []): ResponseInterface;

    /**
     * Create a streamed response.
     *
     * @param callable $callback Callback that will be handled for streaming
     * @param array $headers Additional headers for the response
     */
    public function stream(callable $callback, array $headers = []): ResponseInterface;

    /**
     * Create a streamed download response.
     *
     * @param callable $callback Callback that will be handled for streaming download
     * @param array $headers Additional headers for the response
     * @param string $disposition Content-Disposition type (attachment or inline)
     */
    public function streamDownload(callable $callback, ?string $filename = null, array $headers = [], string $disposition = 'attachment'): ResponseInterface;

    /**
     * Get original psr7 response instance.
     */
    public function getPsr7Response(): ResponseInterface;
}
