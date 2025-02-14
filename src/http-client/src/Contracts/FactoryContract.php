<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\HttpClient\Contracts;

use Closure;
use GuzzleHttp\Promise\PromiseInterface;
use Hyperf\Collection\Collection;
use Psr\EventDispatcher\EventDispatcherInterface;
use SwooleTW\Hyperf\HttpClient\PendingRequest;
use SwooleTW\Hyperf\HttpClient\Request;
use SwooleTW\Hyperf\HttpClient\Response;
use SwooleTW\Hyperf\HttpClient\ResponseSequence;

interface FactoryContract
{
    /**
     * Add middleware to apply to every request.
     */
    public function globalMiddleware(callable $middleware): static;

    /**
     * Add request middleware to apply to every request.
     */
    public function globalRequestMiddleware(callable $middleware): static;

    /**
     * Add response middleware to apply to every request.
     */
    public function globalResponseMiddleware(callable $middleware): static;

    /**
     * Set the options to apply to every request.
     */
    public function globalOptions(array|Closure $options): static;

    /**
     * Create a new response instance for use during stubbing.
     */
    public static function response(
        null|array|callable|int|PromiseInterface|Response|string $body = null,
        int $status = 200,
        array $headers = []
    ): PromiseInterface;

    /**
     * Create a new connection exception for use during stubbing.
     */
    public static function failedConnection(?string $message = null): Closure;

    /**
     * Get an invokable object that returns a sequence of responses in order for use during stubbing.
     */
    public function sequence(array $responses = []): ResponseSequence;

    /**
     * Register a stub callable that will intercept requests and be able to return stub responses.
     */
    public function fake(null|array|callable $callback = null): static;

    /**
     * Register a response sequence for the given URL pattern.
     */
    public function fakeSequence(string $url = '*'): ResponseSequence;

    /**
     * Stub the given URL using the given callback.
     */
    public function stubUrl(string $url, array|callable|int|PromiseInterface|Response|string $callback): static;

    /**
     * Indicate that an exception should be thrown if any request is not faked.
     */
    public function preventStrayRequests(bool $prevent = true): static;

    /**
     * Determine if stray requests are being prevented.
     */
    public function preventingStrayRequests(): bool;

    /**
     * Indicate that an exception should not be thrown if any request is not faked.
     */
    public function allowStrayRequests(): static;

    /**
     * Record a request response pair.
     */
    public function recordRequestResponsePair(Request $request, ?Response $response): void;

    /**
     * Assert that a request / response pair was recorded matching a given truth test.
     */
    public function assertSent(callable $callback): void;

    /**
     * Assert that the given request was sent in the given order.
     */
    public function assertSentInOrder(array $callbacks): void;

    /**
     * Assert that a request / response pair was not recorded matching a given truth test.
     */
    public function assertNotSent(callable $callback): void;

    /**
     * Assert that no request / response pair was recorded.
     */
    public function assertNothingSent(): void;

    /**
     * Assert how many requests have been recorded.
     */
    public function assertSentCount(int $count): void;

    /**
     * Assert that every created response sequence is empty.
     */
    public function assertSequencesAreEmpty(): void;

    /**
     * Get a collection of the request / response pairs matching the given truth test.
     */
    public function recorded(?callable $callback = null): Collection;

    /**
     * Create a new pending request instance for this factory.
     */
    public function createPendingRequest(): PendingRequest;

    /**
     * Get the current event dispatcher implementation.
     */
    public function getDispatcher(): ?EventDispatcherInterface;

    /**
     * Get the array of global middleware.
     */
    public function getGlobalMiddleware(): array;
}
