<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Cache;

use Closure;
use Hyperf\Support\Traits\InteractsWithTime;
use SwooleTW\Hyperf\Cache\Contracts\Repository as Cache;

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
    public function limiter(string $name): Closure
    {
        return $this->limiters[$name] ?? null;
    }

    /**
     * Determine if the given key has been "accessed" too many times.
     */
    public function tooManyAttempts(string $key, int $maxAttempts): bool
    {
        if ($this->attempts($key) >= $maxAttempts) {
            if ($this->cache->has($key . ':timer')) {
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

    // TODO: return type should be int?
    /**
     * Get the number of attempts for the given key.
     */
    public function attempts(string $key): mixed
    {
        return $this->cache->get($key, 0);
    }

    // TODO: return type should be int?
    /**
     * Reset the number of attempts for the given key.
     */
    public function resetAttempts(string $key): mixed
    {
        return $this->cache->forget($key);
    }

    /**
     * Get the number of retries left for the given key.
     */
    public function retriesLeft(string $key, int $maxAttempts): int
    {
        $attempts = $this->attempts($key);

        return $maxAttempts - $attempts;
    }

    /**
     * Clear the hits and lockout timer for the given key.
     */
    public function clear(string $key): void
    {
        $this->resetAttempts($key);

        $this->cache->forget($key . ':timer');
    }

    /**
     * Get the number of seconds until the "key" is accessible again.
     */
    public function availableIn(string $key): int
    {
        return $this->cache->get($key . ':timer') - $this->currentTime();
    }
}
