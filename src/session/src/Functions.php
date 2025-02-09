<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Session;

use RuntimeException;
use SwooleTW\Hyperf\Session\Contracts\Session as SessionContract;
use SwooleTW\Hyperf\Support\HtmlString;

/**
 * Get / set the specified session value.
 *
 * If an array is passed as the key, we will assume you want to set an array of values.
 *
 * @return mixed|SessionContract
 */
function session(null|array|string $key = null, mixed $default = null): mixed
{
    $session = app(SessionContract::class);

    if (is_null($key)) {
        return $session;
    }

    if (is_array($key)) {
        return $session->put($key);
    }

    return $session->get($key, $default);
}

/**
 * Get the CSRF token value.
 *
 * @throws RuntimeException
 */
function csrf_token(): ?string
{
    if (! app()->has(SessionContract::class)) {
        throw new RuntimeException('Application session store not set.');
    }

    return app()->get(SessionContract::class)
        ->token();
}

if (! function_exists('csrf_field')) {
    /**
     * Generate a CSRF token form field.
     */
    function csrf_field(): HtmlString
    {
        return new HtmlString('<input type="hidden" name="_token" value="' . csrf_token() . '" autocomplete="off">');
    }
}
