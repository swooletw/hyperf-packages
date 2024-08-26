<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Foundation\Macros;

use Carbon\Carbon;
use Hyperf\Collection\Arr;
use Hyperf\Context\Context;
use Hyperf\Context\RequestContext;
use Hyperf\HttpServer\Request;
use Hyperf\Stringable\Str;
use stdClass;
use SwooleTW\Hyperf\Foundation\Http\AcceptHeader;
use SwooleTW\Hyperf\Foundation\Support\HeaderUtils;

use function Hyperf\Collection\collect;
use function Hyperf\Collection\data_get;
/**
 * @property array $contextkeys
 * @mixin Request
 */
class RequestMacro
{
    public function allFiles()
    {
        return fn () => $this->getUploadedFiles();
    }

    public function anyFilled()
    {
        return function ($keys) {
            $keys = is_array($keys) ? $keys : func_get_args();

            foreach ($keys as $key) {
                if ($this->filled($key)) {
                    return true;
                }
            }

            return false;
        };
    }

    public function boolean()
    {
        return fn (string $key = '', $default = false) => filter_var($this->input($key, $default), FILTER_VALIDATE_BOOLEAN);
    }

    public function collect()
    {
        return function ($key = null) {
            if (is_null($key)) {
                return $this->all();
            }

            return collect(
                is_array($key) ? $this->only($key) : $this->input($key)
            );
        };
    }

    public function date()
    {
        return function (string $key, $format = null, $tz = null) {
            if ($this->isNotFilled($key)) {
                return null;
            }

            if (is_null($format)) {
                return Carbon::parse($this->input($key), $tz);
            }

            return Carbon::createFromFormat($format, $this->input($key), $tz);
        };
    }

    public function enum()
    {
        return function ($key, $enumClass) {
            if (
                $this->isNotFilled($key)
                || ! function_exists('enum_exists')
                || ! enum_exists($enumClass)
                || ! method_exists($enumClass, 'tryFrom')
            ) {
                return null;
            }

            return $enumClass::tryFrom($this->input($key));
        };
    }

    public function except()
    {
        return function ($keys) {
            $keys = is_array($keys) ? $keys : func_get_args();
            $results = $this->all();

            Arr::forget($results, $keys);

            return $results;
        };
    }

    public function exists()
    {
        return fn ($key) => $this->has($key);
    }

    public function filled()
    {
        return function ($key) {
            $keys = is_array($key) ? $key : func_get_args();

            foreach ($keys as $value) {
                if ($this->isEmptyString($value)) {
                    return false;
                }
            }

            return true;
        };
    }

    public function float()
    {
        return fn ($key, $default = null) => (float) $this->input($key, $default);
    }

    public function hasAny()
    {
        return fn ($keys) => Arr::hasAny($this->all(), is_array($keys) ? $keys : func_get_args());
    }

    public function host()
    {
        return fn () => $this->getHost();
    }

    public function httpHost()
    {
        return fn () => $this->getHttpHost();
    }

    public function getHost()
    {
        return function () {
            $host = $this->getHeader('HOST')[0] ?? $this->getServerParams('SERVER_NAME')[0] ?? $this->getServerParams('SERVER_ADDR')[0] ?? '';
            return strtolower(preg_replace('/:\d+$/', '', trim($host)));
        };
    }

    public function getHttpHost()
    {
        return fn () => $this->getHost() . ':' . $this->getPort();
    }

    public function getPort()
    {
        return function () {
            if (! $host = $this->getHeader('HOST')[0] ?? '') {
                return $this->getServerParams('SERVER_PORT')[0];
            }

            if ($host[0] === '[') {
                $pos = strpos($host, ':', strrpos($host, ']'));
            } else {
                $pos = strrpos($host, ':');
            }

            if ($pos !== false && $port = substr($host, $pos + 1)) {
                return (int) $port;
            }

            return $this->getScheme() === 'https' ? 443 : 80;
        };
    }

    public function getScheme()
    {
        return fn () => $this->isSecure() ? 'https' : 'http';
    }

    public function isSecure()
    {
        return function () {
            $https = $this->getServerParams('HTTPS')[0] ?? '';

            return ! empty($https) && strtolower($https) !== 'off';
        };
    }

    public function integer()
    {
        return fn ($key, $default = null) => (int) $this->input($key, $default);
    }

    public function isEmptyString()
    {
        return function ($key) {
            $value = $this->input($key);

            return ! is_bool($value) && ! is_array($value) && trim((string) $value) === '';
        };
    }

    public function isJson()
    {
        return fn () => Str::contains($this->header('CONTENT_TYPE') ?? '', ['/json', '+json']);
    }

    public function isNotFilled()
    {
        return function ($key) {
            $keys = is_array($key) ? $key : func_get_args();

            foreach ($keys as $value) {
                if (! $this->isEmptyString($value)) {
                    return false;
                }
            }

            return true;
        };
    }

    public function keys()
    {
        return fn () => array_merge(array_keys($this->all()), array_keys($this->getUploadedFiles()));
    }

    public function merge()
    {
        return function (array $input) {
            /* @phpstan-ignore-next-line */
            Context::override($this->contextkeys['parsedData'], fn ($inputs) => array_replace((array) $inputs, $input));

            return $this;
        };
    }

    public function mergeIfMissing()
    {
        return fn (array $input) => $this->merge(
            collect($input)
                ->filter(fn ($value, $key) => $this->missing($key))
                ->toArray()
        );
    }

    public function missing()
    {
        return fn ($key) => ! $this->has(is_array($key) ? $key : func_get_args());
    }

    public function only()
    {
        return function ($keys) {
            $results = [];
            $input = $this->all();
            $placeholder = new stdClass();

            foreach (is_array($keys) ? $keys : func_get_args() as $key) {
                $value = data_get($input, $key, $placeholder);

                if ($value !== $placeholder) {
                    Arr::set($results, $key, $value);
                }
            }

            return $results;
        };
    }

    public function getSchemeAndHttpHost()
    {
        return fn () => $this->getScheme() . '://' . $this->httpHost();
    }

    public function schemeAndHttpHost()
    {
        return fn () => $this->getSchemeAndHttpHost();
    }

    public function str()
    {
        return fn ($key, $default = null) => Str::of($this->input($key, $default));
    }

    public function string()
    {
        return fn ($key, $default = null) => Str::of($this->input($key, $default));
    }

    public function wantsJson()
    {
        return function () {
            $acceptable = explode(',', $this->header('ACCEPT') ?? '');

            return Str::contains(strtolower($acceptable[0]) ?? '', ['/json', '+json']);
        };
    }

    public function whenFilled()
    {
        return function ($key, callable $callback, ?callable $default = null) {
            if ($this->filled($key)) {
                return $callback(data_get($this->all(), $key)) ?: $this;
            }

            if ($default) {
                return $default();
            }

            return $this;
        };
    }

    public function whenHas()
    {
        return function ($key, callable $callback, ?callable $default = null) {
            if ($this->has($key)) {
                return $callback(data_get($this->all(), $key)) ?: $this;
            }

            if ($default) {
                return $default();
            }

            return $this;
        };
    }

    public function getClientIp()
    {
        return function () {
            if (! RequestContext::has()) {
                return '127.0.0.1';
            }
            return $this->getHeaderLine('x-real-ip')
                ?: $this->server('remote_addr');
        };
    }

    public function ip()
    {
        return $this->getClientIp();
    }

    public function fullUrlWithQuery()
    {
        return function (array $query) {
            $question = $this->url() . $this->getPathInfo() === '/' ? '/?' : '?';

            return count($this->query()) > 0
                ? $this->url() . $question . Arr::query(array_merge($this->query(), $query))
                : $this->fullUrl() . Arr::query($query);
        };
    }

    public function fullUrlWithoutQuery()
    {
        return function (array $keys) {
            $query = Arr::except($this->query(), $keys);

            $question = $this->url() . $this->getPathInfo() === '/' ? '/?' : '?';

            return count($query) > 0
                ? $this->url() . $question . Arr::query($query)
                : $this->url();
        };
    }

    public function method()
    {
        return fn () => $this->getMethod();
    }

    public function bearerToken()
    {
        return function () {
            $header = $this->header('Authorization', '');

            $position = strrpos($header, 'Bearer ');

            if ($position !== false) {
                $header = substr($header, $position + 7);

                return str_contains($header, ',') ? strstr($header, ',', true) : $header;
            }
        };
    }

    public function getAcceptableContentTypes()
    {
        return fn () => array_map('strval', array_keys(AcceptHeader::fromString($this->header('Accept'))->all()));
    }

    public function getMimeType()
    {
        return fn (string $format) => isset(HeaderUtils::$formats[$format]) ? HeaderUtils::$formats[$format][0] : null;
    }

    public function getMimeTypes()
    {
        return fn (string $format) => HeaderUtils::$formats[$format] ?? [];
    }

    public function accepts()
    {
        return function (array $contentTypes) {
            $accepts = $this->getAcceptableContentTypes();
            if (count($accepts) === 0) {
                return true;
            }

            foreach ($accepts as $accept) {
                if ($accept === '*/*' || $accept === '*') {
                    return true;
                }

                foreach ($contentTypes as $type) {
                    $accept = strtolower($accept);

                    $type = strtolower($type);

                    if (AcceptHeader::matchesType($accept, $type) || $accept === strtok($type, '/') . '/*') {
                        return true;
                    }
                }
            }

            return false;
        };
    }

    public function prefers()
    {
        return function (array $contentTypes) {
            $accepts = $this->getAcceptableContentTypes();
            foreach ($accepts as $accept) {
                if (in_array($accept, ['*/*', '*'])) {
                    return $contentTypes[0];
                }

                foreach ($contentTypes as $contentType) {
                    $type = $contentType;

                    if (! is_null($mimeType = $this->getMimeType($contentType))) {
                        $type = $mimeType;
                    }

                    $accept = strtolower($accept);

                    $type = strtolower($type);

                    if (AcceptHeader::matchesType($type, $accept) || $accept === strtok($type, '/') . '/*') {
                        return $contentType;
                    }
                }
            }
        };
    }

    public function isXmlHttpRequest()
    {
        return fn () => $this->header('X-Requested-With') === 'XMLHttpRequest';
    }

    public function ajax()
    {
        return $this->isXmlHttpRequest();
    }

    public function pjax()
    {
        return fn () => $this->header('X-PJAX') === true;
    }

    public function acceptsAnyContentType()
    {
        return function () {
            $acceptable = $this->getAcceptableContentTypes();

            return count($acceptable) === 0 || (
            isset($acceptable[0]) && ($acceptable[0] === '*/*' || $acceptable[0] === '*')
            );
        };
    }

    public function expectsJson()
    {
        return fn () =>
        ($this->ajax() && ! $this->pjax() && $this->acceptsAnyContentType()) || $this->wantsJson();
    }
}
