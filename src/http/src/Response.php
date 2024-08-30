<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Http;

use Hyperf\Codec\Json;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\Arrayable;
use Hyperf\Contract\Jsonable;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\Response as HyperfResponse;
use Hyperf\View\RenderInterface;
use Psr\Http\Message\ResponseInterface;
use SwooleTW\Hyperf\Http\Contracts\ResponseContract;

class Response extends HyperfResponse implements ResponseContract
{
    /**
     * Create a new response instance.
     */
    public function make(mixed $content = '', int $status = 200, array $headers = []): ResponseInterface
    {
        /* @phpstan-ignore-next-line */
        $this->setStatus($status);
        foreach ($headers as $name => $value) {
            /* @phpstan-ignore-next-line */
            $this->setHeader($name, $value);
        }
        if (is_array($content) || $content instanceof Arrayable) {
            /* @phpstan-ignore-next-line */
            return $this->setHeader('Content-Type', 'application/json')
                ->setBody(new SwooleStream(Json::encode($content)));
        }

        if ($content instanceof Jsonable) {
            /* @phpstan-ignore-next-line */
            return $this->setHeader('Content-Type', 'application/json')
                ->setBody(new SwooleStream((string) $content));
        }

        if ($this->hasHeader('Content-Type')) {
            /* @phpstan-ignore-next-line */
            return $this->setBody(new SwooleStream((string) $content));
        }
        /* @phpstan-ignore-next-line */
        return $this->setHeader('Content-Type', 'text/plain')
            ->setBody(new SwooleStream((string) $content));
    }

    /**
     * Create a new "no content" response.
     */
    public function noContent(int $status = 204, array $headers = []): ResponseInterface
    {
        $response = $this->withStatus($status);
        foreach ($headers as $name => $value) {
            $response = $response->withAddedHeader($name, $value);
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
     * Get original psr7 response instance.
     */
    public function getPsr7Response(): ResponseInterface
    {
        return $this->response;
    }

    /**
     * Create a streamed response.
     *
     * @param callable $callback Callback that will be handled for streaming
     * @param array $headers Additional headers for the response
     */
    public function stream(callable $callback, array $headers = []): ResponseInterface
    {
        $response = $this->getResponse()
            ->setHeader('Content-Type', 'text/event-stream');

        foreach ($headers as $key => $value) {
            /* @phpstan-ignore-next-line */
            $response->setHeader($key, $value);
        }

        /* @phpstan-ignore-next-line */
        $swooleResponse = $this->getConnection()->getSocket();
        foreach ($response->getHeaders() as $key => $value) {
            $swooleResponse->header($key, $value);
        }

        if ($result = $callback($this)) {
            /* @phpstan-ignore-next-line */
            return $response->setBody(
                new SwooleStream($result)
            );
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
        if (! empty($filename)) {
            $downloadHeaders['Content-Disposition'] = "{$disposition}; filename={$filename}; filename*=UTF-8''" . rawurlencode($filename);
        }

        foreach ($headers as $key => $value) {
            $downloadHeaders[$key] = $value;
        }

        return $this->stream($callback, $downloadHeaders);
    }
}
