<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Cache;

use Carbon\Carbon;
use Closure;
use Laravel\SerializableClosure\SerializableClosure;
use Swoole\Table;
use SwooleTW\Hyperf\Cache\Contracts\Store;

use function Hyperf\Coroutine\defer;

class SwooleStore implements Store
{
    protected const ONE_YEAR = 31536000;

    protected const CAPACITY_BUFFER = 10;

    /**
     * All of the registered interval caches.
     */
    protected array $intervals = [];

    /**
     * Create a new Octane store.
     */
    public function __construct(protected Table $table) {}

    /**
     * Retrieve an item from the cache by key.
     */
    public function get(array|string $key): mixed
    {
        $record = $this->table->get($key);

        if (! $this->recordIsFalseOrExpired($record)) {
            return unserialize($record['value']);
        }

        if (in_array($key, $this->intervals)
            && ! is_null($interval = $this->getInterval($key))) {
            return $interval['resolver']();
        }

        $this->forget($key);

        return null;
    }

    /**
     * Retrieve an interval item from the cache.
     */
    protected function getInterval(string $key): ?array
    {
        $interval = $this->get('interval-' . $key);

        return $interval ? unserialize($interval) : null;
    }

    /**
     * Retrieve multiple items from the cache by key.
     * Items not found in the cache will have a null value.
     */
    public function many(array $keys): array
    {
        return collect($keys)->mapWithKeys(fn ($key) => [$key => $this->get($key)])->all();
    }

    /**
     * Store an item in the cache for a given number of seconds.
     */
    public function put(string $key, mixed $value, int $seconds): bool
    {
        $now = $this->getCurrentTimestamp();

        $result = $this->table->set($key, [
            'value' => serialize($value),
            'expiration' => $now + $seconds,
        ]);

        defer(function () {
            while ($this->tableIsFull()) {
                $this->removeAlmostExpireRecords();
            }
        });

        return $result;
    }

    /**
     * Store multiple items in the cache for a given number of seconds.
     */
    public function putMany(array $values, int $seconds): bool
    {
        foreach ($values as $key => $value) {
            $this->put($key, $value, $seconds);
        }

        return true;
    }

    /**
     * Increment the value of an item in the cache.
     */
    public function increment(string $key, int $value = 1): int
    {
        $record = $this->table->get($key);

        if ($this->recordIsFalseOrExpired($record)) {
            return tap($value, fn ($value) => $this->put($key, $value, static::ONE_YEAR));
        }

        return tap((int) (unserialize($record['value']) + $value), function ($value) use ($key, $record) {
            $this->table->set($key, [
                'value' => serialize($value),
                'expiration' => $record['expiration'],
            ]);
        });
    }

    /**
     * Decrement the value of an item in the cache.
     */
    public function decrement(string $key, int $value = 1): int
    {
        return $this->increment($key, $value * -1);
    }

    /**
     * Store an item in the cache indefinitely.
     */
    public function forever(string $key, mixed $value): bool
    {
        return $this->put($key, $value, static::ONE_YEAR);
    }

    /**
     * Register a cache key that should be refreshed at a given interval (in minutes).
     */
    public function interval(string $key, Closure $resolver, int $seconds): void
    {
        if (! is_null($this->getInterval($key))) {
            $this->intervals[] = $key;

            return;
        }

        $this->forever('interval-' . $key, serialize([
            'resolver' => new SerializableClosure($resolver),
            'lastRefreshedAt' => null,
            'refreshInterval' => $seconds,
        ]));

        $this->intervals[] = $key;
    }

    /**
     * Refresh all of the applicable interval caches.
     */
    public function refreshIntervalCaches(): void
    {
        foreach ($this->intervals as $key) {
            if (! $this->intervalShouldBeRefreshed($interval = $this->getInterval($key))) {
                continue;
            }

            $this->forever('interval-' . $key, serialize(array_merge(
                $interval,
                ['lastRefreshedAt' => Carbon::now()->getTimestamp()],
            )));

            $this->forever($key, $interval['resolver']());
        }
    }

    /**
     * Determine if the given interval record should be refreshed.
     */
    protected function intervalShouldBeRefreshed(array $interval): bool
    {
        return is_null($interval['lastRefreshedAt'])
               || (Carbon::now()->getTimestamp() - $interval['lastRefreshedAt']) >= $interval['refreshInterval'];
    }

    /**
     * Remove an item from the cache.
     */
    public function forget(string $key): bool
    {
        return $this->table->del($key);
    }

    /**
     * Remove all items from the cache.
     */
    public function flush(): bool
    {
        foreach ($this->table as $key => $record) {
            if (str_starts_with($key, 'interval-')) {
                continue;
            }

            $this->forget($key);
        }

        return true;
    }

    /**
     * Determine if the record is missing or expired.
     */
    protected function recordIsFalseOrExpired(array|false $record): bool
    {
        return $record === false || $record['expiration'] <= $this->getCurrentTimestamp();
    }

    /**
     * Get the cache key prefix.
     */
    public function getPrefix(): string
    {
        return '';
    }

    /**
     * Get the current UNIX timestamp, with microsecond.
     */
    protected function getCurrentTimestamp(): float
    {
        return Carbon::now()->getPreciseTimestamp(6) / 1000000;
    }

    /**
     * Determine if the table is full.
     */
    protected function tableIsFull(): bool
    {
        return $this->table->stats()['available_slice_num'] < static::CAPACITY_BUFFER
                    || $this->table->getSize() - static::CAPACITY_BUFFER < $this->table->stats()['num'];
    }

    /**
     * Remove the almost expire records.
     */
    protected function removeAlmostExpireRecords(): void
    {
        collect($this->table)
            ->map(fn ($record) => $record['expiration'])
            ->sort()
            ->take(static::CAPACITY_BUFFER)
            ->each(fn ($_, $key) => $this->forget($key));
    }
}
