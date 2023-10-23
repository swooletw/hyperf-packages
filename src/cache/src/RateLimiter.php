<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Cache;

use Closure;
use Hyperf\Support\Traits\InteractsWithTime;
use SwooleTW\Hyperf\Cache\Contracts\Factory as Cache;

class RateLimiter
{
    use InteractsWithTime;

    /**
     * The cache store implementation.
     */
    protected Cache $cache;

    /**
     * The configured limit object resolvers.
     */
    protected array $limiters = [];

    /**
     * Create a new rate limiter instance.
     */
    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Register a named limiter configuration.
     */
    public function for(string $name, Closure $callback): static
    {
        $this->limiters[$name] = $callback;

        return $this;
    }

    /**
     * Get the given named rate limiter.
     */
    public function limiter(string $name): ?Closure
    {
        return $this->limiters[$name] ?? null;
    }

    /**
     * Attempts to execute a callback if it's not limited.
     */
    public function attempt(string $key, int $maxAttempts, Closure $callback, int $decaySeconds = 60): mixed
    {
        if ($this->tooManyAttempts($key, $maxAttempts)) {
            return false;
        }

        if (is_null($result = $callback())) {
            $result = true;
        }

        return tap($result, function () use ($key, $decaySeconds) {
            $this->hit($key, $decaySeconds);
        });
    }

    /**
     * Determine if the given key has been "accessed" too many times.
     */
    public function tooManyAttempts(string $key, int $maxAttempts): bool
    {
        if ($this->attempts($key) >= $maxAttempts) {
            if ($this->cache->has($this->cleanRateLimiterKey($key) . ':timer')) {
                return true;
            }

            $this->resetAttempts($key);
        }

        return false;
    }

    /**
     * Increment the counter for a given key for a given decay time.
     */
    public function hit(string $key, int $decaySeconds = 60): int
    {
        $key = $this->cleanRateLimiterKey($key);

        $this->cache->add(
            $key . ':timer',
            $this->availableAt($decaySeconds),
            $decaySeconds
        );

        $added = $this->cache->add($key, 0, $decaySeconds);

        $hits = (int) $this->cache->increment($key);

        if (! $added && $hits == 1) {
            $this->cache->put($key, 1, $decaySeconds);
        }

        return $hits;
    }

    /**
     * Get the number of attempts for the given key.
     */
    public function attempts(string $key): mixed
    {
        $key = $this->cleanRateLimiterKey($key);

        return $this->cache->get($key, 0);
    }

    /**
     * Reset the number of attempts for the given key.
     */
    public function resetAttempts(string $key): mixed
    {
        $key = $this->cleanRateLimiterKey($key);

        return $this->cache->forget($key);
    }

    /**
     * Get the number of retries left for the given key.
     */
    public function remaining(string $key, int $maxAttempts): int
    {
        $key = $this->cleanRateLimiterKey($key);

        $attempts = $this->attempts($key);

        return $maxAttempts - $attempts;
    }

    /**
     * Get the number of retries left for the given key.
     */
    public function retriesLeft(string $key, int $maxAttempts): int
    {
        return $this->remaining($key, $maxAttempts);
    }

    /**
     * Clear the hits and lockout timer for the given key.
     */
    public function clear(string $key): void
    {
        $key = $this->cleanRateLimiterKey($key);

        $this->resetAttempts($key);

        $this->cache->forget($key . ':timer');
    }

    /**
     * Get the number of seconds until the "key" is accessible again.
     */
    public function availableIn(string $key): int
    {
        $key = $this->cleanRateLimiterKey($key);

        return max(0, $this->cache->get($key . ':timer') - $this->currentTime());
    }

    /**
     * Clean the rate limiter key from unicode characters.
     */
    public function cleanRateLimiterKey(string $key): string
    {
        return preg_replace('/&([a-z])[a-z]+;/i', '$1', htmlentities($key));
    }
}
