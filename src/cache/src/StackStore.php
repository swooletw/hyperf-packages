<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Cache;

use Carbon\Carbon;
use Closure;
use SwooleTW\Hyperf\Cache\Contracts\Store;

class StackStore implements Store
{
    /**
     * @param array<Store> $stores
     */
    public function __construct(protected array $stores) {}

    public function get(string $key): mixed
    {
        $record = $this->getOrRestoreRecord($key);

        return $record['value'] ?? null;
    }

    public function many(array $keys): array
    {
        return array_map(fn ($key) => $this->get($key), array_combine($keys, $keys));
    }

    public function put(string $key, mixed $value, int $seconds): bool
    {
        $record = [
            'value' => $value,
            'ttl' => $seconds,
        ];

        return $this->putRecord($key, $record);
    }

    public function putMany(array $values, int $seconds): bool
    {
        foreach ($values as $key => $value) {
            if (! $this->put($key, $value, $seconds)) {
                return false;
            }
        }

        return true;
    }

    public function increment(string $key, int $value = 1): int|bool
    {
        $record = $this->getOrRestoreRecord($key);

        if (is_null($record)) {
            return tap($value, fn ($value) => $this->forever($key, $value));
        }

        $newValue = $record['value'] + $value;
        $newRecord = ['value' => $newValue] + $record;

        if ($this->putRecord($key, $newRecord)) {
            return $newValue;
        }

        return false;
    }

    public function decrement(string $key, int $value = 1): int|bool
    {
        return $this->increment($key, $value * -1);
    }

    public function forever(string $key, mixed $value): bool
    {
        $record = compact('value');

        return $this->buildStoresCallStack(
            static function (Store $store, Closure $next, string $key, array $record): bool {
                if (! $store->forever($key, $record)) {
                    return false;
                }

                return $next($key, $record);
            },
            static fn () => true
        )($key, $record);
    }

    public function forget(string $key): bool
    {
        return $this->buildStoresCallStack(
            static function (Store $store, Closure $next, string $key): bool {
                if (! $store->forget($key)) {
                    return false;
                }

                return $next($key);
            },
            static fn () => true
        )($key);
    }

    public function flush(): bool
    {
        return $this->buildStoresCallStack(
            static function (Store $store, Closure $next): bool {
                if (! $store->flush()) {
                    return false;
                }

                return $next();
            },
            static fn () => true
        )();
    }

    public function getPrefix(): string
    {
        return '';
    }

    protected function getOrRestoreRecord(string $key): mixed
    {
        return $this->buildStoresCallStack(
            static function (Store $store, Closure $next, string $key, Closure $putToStore): ?array {
                if (! is_null($record = $store->get($key))) {
                    return (array) $record;
                }

                if (is_null($record = $next($key, $putToStore))) {
                    return null;
                }

                if ($putToStore($store, $key, $record)) {
                    return $record;
                }

                return null;
            },
            static fn () => null
        )($key, $this->putToStore());
    }

    protected function putRecord(string $key, array $record): bool
    {
        return $this->buildStoresCallStack(
            static function (Store $store, Closure $next, string $key, array $record, Closure $putToStore): bool {
                if (! $putToStore($store, $key, $record)) {
                    return false;
                }

                return $next($key, $record, $putToStore);
            },
            static fn () => true
        )($key, $record, $this->putToStore());
    }

    protected function putToStore(): Closure
    {
        return static function (Store $store, string $key, array $record): bool {
            if (! array_key_exists('value', $record)) {
                return false;
            }

            if (! array_key_exists('expiration', $record) && ! array_key_exists('ttl', $record)) {
                return $store->forever($key, $record);
            }

            $currentTimestamp = Carbon::now()->getTimestamp();
            $value = $record['value'];
            $expiration = $record['expiration'] ?? $currentTimestamp + $record['ttl'];
            $ttl = $record['ttl'] ?? $record['expiration'] - $currentTimestamp;
            $normalizedRecord = compact('value', 'expiration');

            return $store->put($key, $normalizedRecord, $ttl);
        };
    }

    protected function buildStoresCallStack(Closure $handler, Closure $bottomLayer): mixed
    {
        return array_reduce(array_reverse($this->stores), function ($stack, $store) use ($handler) {
            return function (...$args) use ($stack, $store, $handler) {
                return $handler($store, $stack, ...$args);
            };
        }, $bottomLayer);
    }
}
