<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Support\Facades;

use Closure;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\StreamInterface;
use SwooleTW\Hyperf\HttpClient\Contracts\FactoryContract;
use SwooleTW\Hyperf\HttpClient\PendingRequest;
use SwooleTW\Hyperf\HttpClient\Response;

/**
 * @method static PendingRequest accept(string $contentType)
 * @method static PendingRequest acceptJson()
 * @method static PendingRequest asForm()
 * @method static PendingRequest asJson()
 * @method static PendingRequest asMultipart()
 * @method static PendingRequest async()
 * @method static PendingRequest attach(array|string $name, resource|string $contents = '', ?string $filename = null, array $headers = [])
 * @method static PendingRequest baseUrl(string $url)
 * @method static PendingRequest beforeSending(callable $callback)
 * @method static PendingRequest bodyFormat(string $format)
 * @method static PendingRequest connectTimeout(float|int $seconds)
 * @method static PendingRequest contentType(string $contentType)
 * @method static PendingRequest dd()
 * @method static PendingRequest dump()
 * @method static PendingRequest maxRedirects(int $max)
 * @method static PendingRequest retry(int $times, Closure|int $sleepMilliseconds = 0, ?callable $when = null, bool $throw = true)
 * @method static PendingRequest sink(string $to)
 * @method static PendingRequest stub(callable $callback)
 * @method static PendingRequest timeout(float|int $seconds)
 * @method static PendingRequest withBasicAuth(string $username, string $password)
 * @method static PendingRequest withBody(StreamInterface|string $content, string $contentType)
 * @method static PendingRequest withCookies(array $cookies, string $domain)
 * @method static PendingRequest withDigestAuth(string $username, string $password)
 * @method static PendingRequest withHeaders(array $headers)
 * @method static PendingRequest withMiddleware(callable $middleware)
 * @method static PendingRequest withOptions(array $options)
 * @method static PendingRequest withToken(string $token, string $type = 'Bearer')
 * @method static PendingRequest withUserAgent(bool|string $userAgent)
 * @method static PendingRequest withoutRedirecting()
 * @method static PendingRequest withoutVerifying()
 * @method static PendingRequest throw(?callable $callback = null)
 * @method static PendingRequest throwIf(bool|callable $condition)
 * @method static PendingRequest throwUnless(bool|callable $condition)
 * @method static array pool(callable $callback)
 * @method static Response delete(string $url, array $data = [])
 * @method static Response get(string $url, null|array|string $query = null)
 * @method static Response head(string $url, null|array|string $query = null)
 * @method static Response patch(string $url, array $data = [])
 * @method static Response post(string $url, array $data = [])
 * @method static Response put(string $url, array $data = [])
 * @method static PromiseInterface|Response send(string $method, string $url, array $options = [])
 *
 * @see Factory
 */
class Http extends Facade
{
    protected static function getFacadeAccessor()
    {
        return FactoryContract::class;
    }
}
