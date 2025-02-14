<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\HttpClient;

use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Utils;

/**
 * @mixin Factory
 */
class Pool
{
    /**
     * The handler function for the Guzzle client.
     *
     * @var callable
     */
    protected $handler;

    /**
     * The pool of requests.
     */
    protected array $pool = [];

    /**
     * Create a new requests pool.
     */
    public function __construct(protected ?Factory $factory = null)
    {
        $this->factory = $factory ?: new Factory();
        $this->handler = Utils::chooseHandler();
    }

    /**
     * Add a request to the pool with a key.
     */
    public function as(string $key): PendingRequest
    {
        return $this->pool[$key] = $this->asyncRequest();
    }

    /**
     * Retrieve a new async pending request.
     */
    protected function asyncRequest(): PendingRequest
    {
        return $this->factory->setHandler($this->handler)->async();
    }

    /**
     * Retrieve the requests in the pool.
     */
    public function getRequests(): array
    {
        return $this->pool;
    }

    /**
     * Add a request to the pool with a numeric index.
     */
    public function __call(string $method, array $parameters): PendingRequest|Promise
    {
        return $this->pool[] = $this->asyncRequest()->{$method}(...$parameters);
    }
}
