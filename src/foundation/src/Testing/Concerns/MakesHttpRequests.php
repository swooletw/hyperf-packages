<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Foundation\Testing\Concerns;

use SwooleTW\Hyperf\Foundation\Testing\Http\TestResponse;
use SwooleTW\Hyperf\Foundation\Testing\Stubs\FakeMiddleware;
use SwooleTW\Hyperf\Tests\Foundation\Testing\HttpClient;

trait MakesHttpRequests
{
    /**
     * Additional headers for the request.
     */
    protected array $defaultHeaders = [];

    /**
     * Additional cookies for the request.
     */
    protected array $defaultCookies = [];

    /**
     * Indicates whether redirects should be followed.
     */
    protected bool $followRedirects = false;

    /**
     * Indicated whether JSON requests should be performed "with credentials" (cookies).
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/API/XMLHttpRequest/withCredentials
     */
    protected bool $withCredentials = false;

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

    protected function doRequest(string $method, $uri, array $data = [], array $headers = []): TestResponse
    {
        $cookies = $method !== 'json' || ($method === 'json' && $this->withCredentials)
            ? $this->defaultCookies
            : [];

        $response = $this->createTestResponse(
            $this->app->make(HttpClient::class)->{$method}(
                $uri,
                $data,
                array_merge($this->defaultHeaders, $headers),
                $cookies
            )
        );

        if ($this->followRedirects) {
            $response = $this->followRedirects($response);
        }

        $this->flushRequestStates();

        return $response;
    }

    /**
     * Follow a redirect chain until a non-redirect is received.
     *
     * @param TestResponse $response
     */
    protected function followRedirects($response): TestResponse
    {
        $this->followRedirects = false;

        while ($response->isRedirect()) {
            $response = $this->get($response->getHeader('Location')[0]);
        }

        return $response;
    }

    protected function createTestResponse($response): TestResponse
    {
        return new TestResponse($response);
    }

    /**
     * Define additional headers to be sent with the request.
     *
     * @return $this
     */
    public function withHeaders(array $headers): static
    {
        $this->defaultHeaders = array_merge($this->defaultHeaders, $headers);

        return $this;
    }

    /**
     * Add a header to be sent with the request.
     *
     * @return $this
     */
    public function withHeader(string $name, string $value): static
    {
        $this->defaultHeaders[$name] = $value;

        return $this;
    }

    /**
     * Remove a header from the request.
     *
     * @return $this
     */
    public function withoutHeader(string $name): static
    {
        unset($this->defaultHeaders[$name]);

        return $this;
    }

    /**
     * Remove headers from the request.
     *
     * @return $this
     */
    public function withoutHeaders(array $headers): static
    {
        foreach ($headers as $name) {
            $this->withoutHeader($name);
        }

        return $this;
    }

    /**
     * Add an authorization token for the request.
     *
     * @return $this
     */
    public function withToken(string $token, string $type = 'Bearer'): static
    {
        return $this->withHeader('Authorization', $type . ' ' . $token);
    }

    /**
     * Add a basic authentication header to the request with the given credentials.
     *
     * @return $this
     */
    public function withBasicAuth(string $username, string $password): static
    {
        return $this->withToken(base64_encode("{$username}:{$password}"), 'Basic');
    }

    /**
     * Remove the authorization token from the request.
     *
     * @return $this
     */
    public function withoutToken(): static
    {
        return $this->withoutHeader('Authorization');
    }

    /**
     * Flush all the configured states.
     *
     * @return $this
     */
    public function flushRequestStates(): static
    {
        $this->defaultHeaders = [];
        $this->defaultCookies = [];
        $this->followRedirects = false;
        $this->withCredentials = false;

        return $this;
    }

    /**
     * Define a set of server variables to be sent with the requests.
     *
     * @return $this
     */
    public function withServerVariables(array $server): static
    {
        $this->serverVariables = $server;

        return $this;
    }

    /**
     * Disable middleware for the test.
     *
     * @param null|array|string $middleware
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
     * @param null|array|string $middleware
     * @return $this
     */
    public function withMiddleware($middleware = null): static
    {
        if (is_null($middleware)) {
            $this->app->unbind('middleware.disable');

            return $this;
        }

        // restore bindings since bound middleware can't be removed from container's definition map
        foreach ((array) $middleware as $abstract) {
            $this->app->unbind($abstract, $abstract);
        }

        return $this;
    }

    /**
     * Define additional cookies to be sent with the request.
     *
     * @return $this
     */
    public function withCookies(array $cookies): static
    {
        $this->defaultCookies = array_merge($this->defaultCookies, $cookies);

        return $this;
    }

    /**
     * Add a cookie to be sent with the request.
     *
     * @return $this
     */
    public function withCookie(string $name, string $value): static
    {
        $this->defaultCookies[$name] = $value;

        return $this;
    }

    /**
     * Automatically follow any redirects returned from the response.
     *
     * @return $this
     */
    public function followingRedirects(): static
    {
        $this->followRedirects = true;

        return $this;
    }

    /**
     * Include cookies and authorization headers for JSON requests.
     *
     * @return $this
     */
    public function withCredentials()
    {
        $this->withCredentials = true;

        return $this;
    }
}
