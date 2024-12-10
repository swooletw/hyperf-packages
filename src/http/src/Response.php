<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Http;

use Hyperf\Codec\Json;
use Hyperf\Context\ApplicationContext;
use Hyperf\Context\Context;
use Hyperf\Context\RequestContext;
use Hyperf\Contract\Arrayable;
use Hyperf\Contract\Jsonable;
use Hyperf\HttpMessage\Server\Chunk\Chunkable;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\Response as HyperfResponse;
use Hyperf\View\RenderInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use SwooleTW\Hyperf\Http\Contracts\ResponseContract;
use SwooleTW\Hyperf\HttpMessage\Exceptions\RangeNotSatisfiableHttpException;

class Response extends HyperfResponse implements ResponseContract
{
    /**
     * The context key for range headers.
     */
    public const RANGE_HEADERS_CONTEXT = '_response.withRangeHeaders';

    /**
     * Create a new response instance.
     */
    public function make(mixed $content = '', int $status = 200, array $headers = []): ResponseInterface
    {
        $response = $this->getResponse()->withStatus($status);
        foreach ($headers as $name => $value) {
            $response->addHeader($name, $value);
        }
        if (is_array($content) || $content instanceof Arrayable) {
            return $response->addHeader('Content-Type', 'application/json')
                ->setBody(new SwooleStream(Json::encode($content)));
        }

        if ($content instanceof Jsonable) {
            return $response->addHeader('Content-Type', 'application/json')
                ->setBody(new SwooleStream((string) $content));
        }

        if ($response->hasHeader('Content-Type')) {
            return $response->setBody(new SwooleStream((string) $content));
        }

        return $response->addHeader('Content-Type', 'text/plain')
            ->setBody(new SwooleStream((string) $content));
    }

    /**
     * Create a new "no content" response.
     */
    public function noContent(int $status = 204, array $headers = []): ResponseInterface
    {
        $response = $this->getResponse()->withStatus($status);
        foreach ($headers as $name => $value) {
            $response->addHeader($name, $value);
        }

        return $response;
    }

    /**
     * Create a new response for a given view.
     */
    public function view(string $view, array $data = [], int $status = 200, array $headers = []): ResponseInterface
    {
        $response = ApplicationContext::getContainer()
            ->get(RenderInterface::class)
            ->render($view, $data);

        foreach ($headers as $name => $value) {
            $response = $response->withAddedHeader($name, $value);
        }

        return $response->withStatus($status);
    }

    /**
     * Format data to JSON and return data with Content-Type:application/json header.
     *
     * @param array|Arrayable|Jsonable $data
     */
    public function json($data, int $status = 200, array $headers = []): ResponseInterface
    {
        $response = parent::json($data);
        foreach ($headers as $name => $value) {
            $response = $response->withHeader($name, $value);
        }

        return $response->withStatus($status);
    }

    /**
     * Get original psr7 response instance.
     */
    public function getPsr7Response(): ResponseInterface
    {
        return $this->getResponse();
    }

    /**
     * Create a streamed response.
     *
     * @param callable $callback Callback that will be handled for streaming
     * @param array $headers Additional headers for the response
     */
    public function stream(callable $callback, array $headers = []): ResponseInterface
    {
        $response = $this->getResponse();
        if (! $response instanceof Chunkable) {
            throw new RuntimeException('The response is not a chunkable response.');
        }

        foreach ($headers as $key => $value) {
            $response->addHeader($key, $value);
        }

        if (! $response->hasHeader('Content-Type')) {
            $response->setHeader('Content-Type', 'text/event-stream');
        }

        if ($this->shouldAppendRangeHeaders()) {
            $this->appendRangeHeaders();
        }

        if ($response->getStatusCode() === 416) {
            return $response;
        }

        // Because response emitter sent response in the end of request lifecycle,
        // we need to send headers and status manually before writing content.
        /* @phpstan-ignore-next-line */
        if ($responseConnection = $this->getConnection()) {
            $swooleResponse = $responseConnection->getSocket();
            foreach ($response->getHeaders() as $key => $value) {
                $swooleResponse->header($key, $value);
            }
            $swooleResponse->status($response->getStatusCode(), $response->getReasonPhrase());
        }

        $output = new StreamOutput($response);
        if (! is_null($result = $callback($output))) {
            $output->write($result);
        }

        return $response;
    }

    /**
     * Create a streamed download response.
     *
     * @param callable $callback Callback that will be handled for streaming download
     * @param array $headers Additional headers for the response
     * @param string $disposition Content-Disposition type (attachment or inline)
     */
    public function streamDownload(callable $callback, ?string $filename = null, array $headers = [], string $disposition = 'attachment'): ResponseInterface
    {
        $downloadHeaders = [
            'Content-Type' => 'application/octet-stream',
            'Content-Description' => 'File Transfer',
            'Content-Transfer-Encoding' => 'binary',
            'Pragma' => 'no-cache',
        ];
        if ($filename) {
            $downloadHeaders['Content-Disposition'] = HeaderUtils::makeDisposition($disposition, $filename);
        }

        foreach ($headers as $key => $value) {
            $downloadHeaders[$key] = $value;
        }

        return $this->stream($callback, $downloadHeaders);
    }

    /**
     * Enable range headers for the response.
     */
    public function withRangeHeaders(?int $fileSize = null): static
    {
        Context::set(static::RANGE_HEADERS_CONTEXT, [
            'fileSize' => $fileSize,
        ]);

        return $this;
    }

    /**
     * Disable range headers for the response.
     */
    public function withoutRangeHeaders(): static
    {
        Context::destroy(static::RANGE_HEADERS_CONTEXT);

        return $this;
    }

    /**
     * Determine if the response should append range headers.
     */
    public function shouldAppendRangeHeaders(): bool
    {
        return Context::has(static::RANGE_HEADERS_CONTEXT);
    }

    /**
     * Pull range headers from the context.
     */
    protected function pullRangeHeaders(): ?array
    {
        $context = Context::get(static::RANGE_HEADERS_CONTEXT, null);

        $this->withoutRangeHeaders();

        return $context;
    }

    /**
     * Append range headers to the response.
     */
    protected function appendRangeHeaders(): ResponseInterface
    {
        $rangeHeaders = $this->pullRangeHeaders();
        $request = RequestContext::get();
        $response = $this->getResponse();

        if (! $response->hasHeader('Accept-Ranges')) {
            // Only accept ranges on safe HTTP methods
            $isMethodSafe = in_array($request->getMethod(), ['GET', 'HEAD', 'OPTIONS', 'TRACE']);
            $response->addHeader('Accept-Ranges', $isMethodSafe ? 'bytes' : 'none');
        }

        if (! $request->hasHeader('Range') || $request->getMethod() !== 'GET') {
            return $response;
        }

        $range = $request->getHeader('Range')[0] ?? null;
        if (! str_starts_with($range, 'bytes=')) {
            return $response;
        }

        // Process the range headers.
        $fileSize = $rangeHeaders['fileSize'] ?? null;
        [$start, $end] = explode('-', substr($range, 6), 2) + [1 => ''];

        // Convert start position
        if ($start === '') {
            if ($fileSize === null) {
                throw new RangeNotSatisfiableHttpException(
                    'The requested range is not satisfiable.',
                    0,
                    null,
                    ['Content-Range' => 'bytes */*']
                );
            }
            $start = $fileSize - (int) $end;
            $end = $fileSize - 1;
        } else {
            $start = (int) $start;
        }

        // Convert end position
        if ($end === '') {
            $end = $fileSize !== null ? $fileSize - 1 : null;
        } else {
            $end = (int) $end;
        }

        // Validate the requested range
        if ($start < 0 || ($end !== null && $start > $end) || ($fileSize !== null && $start >= $fileSize)) {
            throw new RangeNotSatisfiableHttpException(
                'The requested range is not satisfiable.',
                0,
                null,
                ['Content-Range' => sprintf('bytes */%s', $fileSize !== null ? $fileSize : '*')]
            );
        }

        // Ensure the end position does not exceed the file size
        if ($fileSize !== null) {
            $end = min($end, $fileSize - 1);
        }

        $response->setStatus(206);

        // Calculate and set Content-Length (if there is an explicit end position)
        if ($end !== null) {
            $length = $end - $start + 1;
            $response->setHeader('Content-Length', $length);
        }

        // Set Content-Range
        $rangeEnd = $end !== null ? $end : '*';
        $totalSize = $fileSize !== null ? $fileSize : '*';
        $response->setHeader('Content-Range', sprintf('bytes %d-%s/%s', $start, $rangeEnd, $totalSize));

        return $response;
    }
}
