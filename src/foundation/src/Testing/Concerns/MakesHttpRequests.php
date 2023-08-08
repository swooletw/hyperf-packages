<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Foundation\Testing\Concerns;

use Hyperf\Testing\Http\Client;
use SwooleTW\Hyperf\Foundation\Testing\Http\TestResponse;
use SwooleTW\Hyperf\Foundation\Testing\Stubs\FakeMiddleware;

trait MakesHttpRequests
{
    protected function get($uri, array $data = [], array $headers = []): TestResponse
    {
        return $this->doRequest(__FUNCTION__, $uri, $data, $headers);
    }

    protected function post($uri, array $data = [], array $headers = []): TestResponse
    {
        return $this->doRequest(__FUNCTION__, $uri, $data, $headers);
    }

    protected function put($uri, array $data = [], array $headers = []): TestResponse
    {
        return $this->doRequest(__FUNCTION__, $uri, $data, $headers);
    }

    protected function delete($uri, array $data = [], array $headers = []): TestResponse
    {
        return $this->doRequest(__FUNCTION__, $uri, $data, $headers);
    }

    protected function json($uri, array $data = [], array $headers = []): TestResponse
    {
        return $this->doRequest(__FUNCTION__, $uri, $data, $headers);
    }

    protected function file($uri, array $data = [], array $headers = []): TestResponse
    {
        return $this->doRequest(__FUNCTION__, $uri, $data, $headers);
    }

    protected function doRequest(string $method, ...$args): TestResponse
    {
        return $this->createTestResponse(
            make(Client::class)->{$method}(...$args)
        );
    }

    protected function createTestResponse($response): TestResponse
    {
        return new TestResponse($response);
    }

    /**
     * Disable middleware for the test.
     *
     * @param  string|array|null  $middleware
     * @return $this
     */
    protected function withoutMiddleware($middleware = null): static
    {
        if (is_null($middleware)) {
            $this->app->set('middleware.disable', true);

            return $this;
        }

        foreach ((array) $middleware as $abstract) {
            $this->app->define($abstract, FakeMiddleware::class);
        }

        return $this;
    }

    /**
     * Enable the given middleware for the test.
     *
     * @param  string|array|null  $middleware
     * @return $this
     */
    public function withMiddleware($middleware = null): static
    {
        if (is_null($middleware)) {
            $this->app->unbind('middleware.disable');

            return $this;
        }

        foreach ((array) $middleware as $abstract) {
            $this->app->unbind([$abstract]);
        }

        return $this;
    }
}
