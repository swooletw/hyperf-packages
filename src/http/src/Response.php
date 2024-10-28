<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Http;

use Hyperf\Codec\Json;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\Arrayable;
use Hyperf\Contract\Jsonable;
use Hyperf\HttpMessage\Server\Chunk\Chunkable;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\Response as HyperfResponse;
use Hyperf\View\RenderInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use SwooleTW\Hyperf\Http\Contracts\ResponseContract;

class Response extends HyperfResponse implements ResponseContract
{
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

        /* @phpstan-ignore-next-line */
        if ($responseConnection = $this->getConnection()) {
            $swooleResponse = $responseConnection->getSocket();
            foreach ($response->getHeaders() as $key => $value) {
                $swooleResponse->header($key, $value);
            }
        }

        $output = new StreamOutput($response);
        if ($result = $callback($output)) {
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
}
