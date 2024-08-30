<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Support\Facades;

use Hyperf\HttpMessage\Cookie\Cookie;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use SwooleTW\Hyperf\Http\Contracts\ResponseContract;

/**
 * @method static ResponseInterface json($data, int $status = 200, array $headers = [])
 * @method static ResponseInterface xml($data, int $status = 200, array $headers = [])
 * @method static ResponseInterface raw($data, int $status = 200, array $headers = [])
 * @method static ResponseInterface redirect(string $toUrl, int $status = 302, string $schema = 'http')
 * @method static ResponseInterface download(string $file, string $name = '')
 * @method static ResponseInterface withCookie(Cookie $cookie)
 * @method static ResponseInterface withHeader($name, $value)
 * @method static ResponseInterface withHeaders(array $headers)
 * @method static ResponseInterface withStatus($code, $reasonPhrase = '')
 * @method static ResponseInterface withContent($content)
 * @method static ResponseInterface withAddedHeader($name, $value)
 * @method static ResponseInterface withoutHeader($name)
 * @method static ResponseInterface withProtocolVersion($version)
 * @method static ResponseInterface withBody(\Psr\Http\Message\StreamInterface $body)
 * @method static string getHeaderLine($name)
 * @method static array getHeaders()
 * @method static bool hasHeader($name)
 * @method static array getHeader($name)
 * @method static StreamInterface getBody()
 * @method static int getStatusCode()
 * @method static string getReasonPhrase()
 * @method static string getProtocolVersion()
 * @method static ResponseInterface write(string $data)
 * @method static ResponseInterface withFile(string $file)
 * @method static ResponseInterface withTrailer($name, $value)
 * @method static ResponseInterface withTrailers(array $trailers)
 * @method static ResponseInterface make(mixed $content = '', int $status = 200, array $headers = [])
 * @method static ResponseInterface noContent(int $status = 204, array $headers = [])
 * @method static ResponseInterface view(string $view, array $data = [], int $status = 200, array $headers = [])
 * @method static ResponseInterface getPsr7Response()
 * @method static ResponseInterface stream(callable $callback, array $headers = [])
 * @method static ResponseInterface streamDownload(callable $callback, ?string $filename = null, array $headers = [], string $disposition = 'attachment')
 *
 * @see \SwooleTW\Hyperf\Http\Response
 */
class Response extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ResponseContract::class;
    }
}
