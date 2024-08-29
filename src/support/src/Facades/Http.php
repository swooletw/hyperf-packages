<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Support\Facades;

use FriendsOfHyperf\Http\Client\Factory as Accessor;
use FriendsOfHyperf\Http\Client\PendingRequest;
use FriendsOfHyperf\Http\Client\Response;

/**
 * @method static PendingRequest accept(string $contentType)
 * @method static PendingRequest acceptJson()
 * @method static PendingRequest asForm()
 * @method static PendingRequest asJson()
 * @method static PendingRequest asMultipart()
 * @method static PendingRequest async()
 * @method static PendingRequest attach(array|string $name, resource|string $contents = '', string|null $filename = null, array $headers = [])
 * @method static PendingRequest baseUrl(string $url)
 * @method static PendingRequest beforeSending(callable $callback)
 * @method static PendingRequest bodyFormat(string $format)
 * @method static PendingRequest connectTimeout(int $seconds)
 * @method static PendingRequest contentType(string $contentType)
 * @method static PendingRequest dd()
 * @method static PendingRequest dump()
 * @method static PendingRequest maxRedirects(int $max)
 * @method static PendingRequest retry(int $times, int $sleepMilliseconds = 0, ?callable $when = null, bool $throw = true)
 * @method static PendingRequest sink(resource|string $to)
 * @method static PendingRequest stub(callable $callback)
 * @method static PendingRequest timeout(int $seconds)
 * @method static PendingRequest withBasicAuth(string $username, string $password)
 * @method static PendingRequest withBody(resource|string $content, string $contentType)
 * @method static PendingRequest withCookies(array $cookies, string $domain)
 * @method static PendingRequest withDigestAuth(string $username, string $password)
 * @method static PendingRequest withHeaders(array $headers)
 * @method static PendingRequest withMiddleware(callable $middleware)
 * @method static PendingRequest withOptions(array $options)
 * @method static PendingRequest withToken(string $token, string $type = 'Bearer')
 * @method static PendingRequest withUserAgent(string $userAgent)
 * @method static PendingRequest withoutRedirecting()
 * @method static PendingRequest withoutVerifying()
 * @method static PendingRequest throw(callable $callback = null)
 * @method static PendingRequest throwIf($condition)
 * @method static PendingRequest throwUnless($condition)
 * @method static array pool(callable $callback)
 * @method static Response delete(string $url, array $data = [])
 * @method static Response get(string $url, array|string|null $query = null)
 * @method static Response head(string $url, array|string|null $query = null)
 * @method static Response patch(string $url, array $data = [])
 * @method static Response post(string $url, array $data = [])
 * @method static Response put(string $url, array $data = [])
 * @method static Response send(string $method, string $url, array $options = [])
 *
 * @see Accessor
 */
class Http extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Accessor::class;
    }
}
